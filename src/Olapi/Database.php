<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Database.
 */
class Database implements IBase
{
    public const API_CUBE_CREATE = '/cube/create';
    public const API_CUBE_DESTROY = '/cube/destroy';
    public const API_CUBE_RENAME = '/cube/rename';

    public const API_DATABASE_CUBES = '/database/cubes';
    public const API_DATABASE_DIMENSIONS = '/database/dimensions';
    public const API_DATABASE_INFO = '/database/info';
    public const API_DATABASE_LOAD = '/database/load';
    public const API_DATABASE_SAVE = '/database/save';
    public const API_DATABASE_UNLOAD = '/database/unload';
    public const API_DATABASE_REBUILD_MARKERS = '/database/rebuild_markers';
    public const API_DATABASE_GENERATE_SCRIPT = '/database/generate_script';

    public const API_DIMENSION_CREATE = '/dimension/create';
    public const API_DIMENSION_DESTROY = '/dimension/destroy';
    public const API_DIMENSION_RENAME = '/dimension/rename';

    public const API_SCRIPT_EXECUTE = '/script/execute';
    public const API_SCRIPT_VARIABLES = '/script/variables';

    private Connection $connection;
    private DimensionCollection $dimensions;
    private CubeCollection $cubes;

    /**
     * @var string[]
     */
    private array $metaInfo;

    private ?array $dimensionList = null;
    private ?array $cubeList = null;

    /**
     * Database constructor.
     *
     * @param Connection $connection
     * @param array      $metaInfo
     *
     * @throws \Exception
     */
    public function __construct(Connection $connection, array $metaInfo)
    {
        $this->connection = $connection;
        $this->metaInfo = $metaInfo;

        $this->dimensions = new DimensionCollection();
        $this->cubes = new CubeCollection();

        $this->initDimensions();
        $this->initCubes();
    }

    /**
     * @param string $name
     * @param array  $dimensionNames
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createCube(string $name, array $dimensionNames): bool
    {
        $response = $this->getConnection()->request(self::API_CUBE_CREATE, [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'new_name' => $name,
                'name_dimensions' => \implode(',', $dimensionNames),
                'type' => 0, // default
            ],
        ]);

        // @todo Database::createCube() - reload cubes

        return '1' === ($response[0] ?? '0');
    }

    /**
     * @param string     $dimension_name
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createDimension(string $dimension_name, ?array $options = null): bool
    {
        $options = (array) $options;

        $response = $this->getConnection()->request(self::API_DIMENSION_CREATE, [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'new_name' => $dimension_name,
                'type' => (int) ($options['type'] ?? 0), // default 0
                'mode' => (int) ($options['mode'] ?? 0), // default 0 (since Jedox 2019.3)
            ],
        ]);

        // @todo Database::createDimension() - reload dimensions
        return \is_numeric($response[0][0] ?? 'X');
    }

    /**
     * @param string $cube_name
     *
     * @throws \ErrorException
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function deleteCube(string $cube_name): bool
    {
        return $this->deleteCubeByName($cube_name);
    }

    /**
     * @param int $cube_id
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteCubeById(int $cube_id): bool
    {
        if (!$this->hasCubeById($cube_id)) {
            throw new \InvalidArgumentException('Unknown cube ID '.$cube_id.' given.');
        }

        $response = $this->getConnection()->request(self::API_CUBE_DESTROY, [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'cube' => $cube_id,
            ],
        ]);

        $flag_successful = ('1' === ($response[0][0] ?? '0'));

        if ($flag_successful) {
            if (isset($this->cubes[$cube_id])) {
                unset($this->cubes[$cube_id]);
            }
            // reload cubes w/o caching
            $this->listCubes(false);
        }

        return $flag_successful;
    }

    /**
     * @param string $cube_name
     *
     * @throws \ErrorException
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function deleteCubeByName(string $cube_name): bool
    {
        if (!$this->hasCubeByName($cube_name)) {
            throw new \InvalidArgumentException('Unknown cube name '.$cube_name.' given.');
        }

        return $this->deleteCubeById($this->getCubeIdFromName($cube_name));
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return bool
     *
     * @see Database::deleteDimensionByName() alias
     */
    public function deleteDimension(string $dimension_name): bool
    {
        return $this->deleteDimensionByName($dimension_name);
    }

    /**
     * @param int $dimension_id
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteDimensionById(int $dimension_id): bool
    {
        if (!$this->hasDimensionById($dimension_id)) {
            throw new \InvalidArgumentException('Unknown dimension ID '.$dimension_id.' given.');
        }

        $response = $this->getConnection()->request(self::API_DIMENSION_DESTROY, [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'dimension' => $dimension_id,
            ],
        ]);

        // delete references of deleted dimension in data model
        $tmp_object = $this->dimensions[$dimension_id];
        unset($tmp_object, $this->dimensions[$dimension_id]);

        // @todo Database::deleteDimension() - reload dimensions

        return '1' === ($response[0] ?? '0');
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteDimensionByName(string $dimension_name): bool
    {
        if (!$this->hasDimensionByName($dimension_name)) {
            throw new \InvalidArgumentException('Unknown dimension '.$dimension_name.' given.');
        }

        return $this->deleteDimensionById($this->getDimensionIdFromName($dimension_name));
    }

    /**
     * Generates new script for a database.
     *
     * @param null|array $dimension_names
     * @param null|array $cube_names
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateScript(
        ?array $dimension_names = null,
        ?array $cube_names = null,
        ?array $options = null
    ): string {
        $params = ['query' => [
            'database' => $this->getOlapObjectId(),
        ],
        ];

        if (null !== $dimension_names) {
            $params['query']['name_dimensions'] = \implode(',', $dimension_names);
        }

        if (null !== $cube_names) {
            $params['query']['name_cubes'] = \implode(',', $cube_names);
        }

        if (isset($options['include_elements'])) {
            $params['query']['include_elements'] = $options['include_elements'];
        }

        if (isset($options['complete'])) {
            $params['query']['complete'] = $options['complete'];
        }

        if (isset($options['show_attribute'])) {
            $params['query']['show_attribute'] = $options['show_attribute'];
        }

        if (isset($options['include_local_subsets'])) {
            $params['query']['include_local_subsets'] = $options['include_local_subsets'];
        }

        if (isset($options['include_global_subsets'])) {
            $params['query']['include_global_subsets'] = $options['include_global_subsets'];
        }

        if (isset($options['include_local_views'])) {
            $params['query']['include_local_views'] = $options['include_local_views'];
        }

        if (isset($options['include_global_views'])) {
            $params['query']['include_global_views'] = $options['include_global_views'];
        }

        if (isset($options['include_dimension_rights'])) {
            $params['query']['include_dimension_rights'] = $options['include_dimension_rights'];
        }

        if (isset($options['include_cube_rights'])) {
            $params['query']['include_cube_rights'] = $options['include_cube_rights'];
        }

        if (isset($options['clear'])) {
            $params['query']['clear'] = $options['clear'];
        }

        if (isset($options['languages'])) {
            $params['query']['languages'] = $options['languages'];
        }

        if (isset($options['show_rule'])) {
            $params['query']['show_rule'] = $options['show_rule'];
        }

        if (isset($options['script_create_clause'])) {
            $params['query']['script_create_clause'] = $options['script_create_clause'];
        }

        if (isset($options['script_modify_clause'])) {
            $params['query']['script_modify_clause'] = $options['script_modify_clause'];
        }

        $response = $this->getConnection()->requestRaw(self::API_DATABASE_GENERATE_SCRIPT, $params);

        if (null === $response) {
            return '';
        }

        return \stream_get_contents($response);
    }

    /**
     * @throws \Exception
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param string $cube_name
     *
     * @throws \Exception
     *
     * @return Cube
     *
     * @see Database::getCubeByName() alias
     */
    public function getCube(string $cube_name): Cube
    {
        return $this->getCubeByName($cube_name);
    }

    /**
     * @param int $cubeId
     *
     * @throws \Exception
     *
     * @return Cube
     */
    public function getCubeById(int $cubeId): Cube
    {
        // cube ID is unknown
        if (!$this->hasCubeById($cubeId)) {
            throw new \InvalidArgumentException('Unknown cube ID '.$cubeId.' given');
        }

        // cube not yet initialized
        if (!isset($this->cubes[$cubeId])) {
            $this->cubes[$cubeId] = new Cube($this, $this->getCubeListRecordById($cubeId));
        }

        return $this->cubes[$cubeId];
    }

    /**
     * @param string $cube_name
     *
     * @throws \Exception
     *
     * @return Cube
     */
    public function getCubeByName(string $cube_name): Cube
    {
        if (!$this->hasCubeByName($cube_name)) {
            throw new \InvalidArgumentException('Unknown cube name '.$cube_name.' given');
        }

        return $this->getCubeById($this->getCubeIdFromName($cube_name));
    }

    /**
     * @param string $cube_name
     *
     * @throws \ErrorException
     *
     * @return int
     */
    public function getCubeIdFromName(string $cube_name): int
    {
        if (!$this->hasCubeByName($cube_name)) {
            throw new \ErrorException('unknown cube '.$cube_name.' requested from database '.
                $this->getDatabase()->getName());
        }

        return $this->cubeList['olap_name'][\strtolower($cube_name)];
    }

    /**
     * @param string $cube_name
     *
     * @throws \ErrorException
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return array
     *
     * @see Database::getCubeListRecordByName() alias
     */
    public function getCubeListRecord(string $cube_name): array
    {
        return $this->getCubeListRecordByName($cube_name);
    }

    /**
     * @param int $cube_id
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getCubeListRecordById(int $cube_id): array
    {
        $this->listCubes();

        if (!$this->hasCubeById($cube_id)) {
            throw new \InvalidArgumentException('Unknown cube ID '.$cube_id.' given.');
        }

        return $this->cubeList['olap_id'][$cube_id];
    }

    /**
     * @param string $cube_name
     *
     * @throws \ErrorException
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getCubeListRecordByName(string $cube_name): array
    {
        $cube_id = $this->getCubeIdFromName($cube_name);

        return $this->getCubeListRecordById($cube_id);
    }

    /**
     * @param int $cube_id
     *
     * @throws \ErrorException
     *
     * @return string
     */
    public function getCubeNameFromId(int $cube_id): string
    {
        if (!$this->hasCubeById($cube_id)) {
            throw new \ErrorException('unknown cube ID '.$cube_id.' requested from database '.
                $this->getDatabase()->getName());
        }

        return $this->cubeList['olap_id'][$cube_id][1];
    }

    /**
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this;
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return Dimension
     *
     * @see Database::getDimensionByName() alias
     */
    public function getDimension(string $dimension_name): Dimension
    {
        return $this->getDimensionByName($dimension_name);
    }

    /**
     * @param int       $dimension_id
     * @param null|bool $use_cache
     *
     * @throws \Exception
     *
     * @return Dimension
     */
    public function getDimensionById(int $dimension_id, ?bool $use_cache = null): Dimension
    {
        $use_cache = $use_cache ?? true;

        // dimension ID is unknown
        if (!$this->hasDimensionById($dimension_id)) {
            throw new \InvalidArgumentException('Unknown dimension ID '.$dimension_id.' given');
        }

        // dimension not yet initialized
        if (!$use_cache || !isset($this->dimensions[$dimension_id])) {
            $this->dimensions[$dimension_id] = new Dimension($this, $this->getDimensionListRecordById($dimension_id));
        }

        return $this->dimensions[$dimension_id];
    }

    /**
     * @param string    $dimension_name
     * @param null|bool $use_cache
     *
     * @throws \Exception
     *
     * @return Dimension
     */
    public function getDimensionByName(string $dimension_name, ?bool $use_cache = null): Dimension
    {
        $use_cache = $use_cache ?? true;

        if (!$this->hasDimensionByName($dimension_name)) {
            throw new \InvalidArgumentException('Unknown dimension name '.$dimension_name.' given');
        }

        return $this->getDimensionById($this->getDimensionIdFromName($dimension_name), $use_cache);
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return int
     */
    public function getDimensionIdFromName(string $dimension_name): int
    {
        if ($this->hasDimensionByName($dimension_name)) {
            return $this->dimensionList['olap_name'][\strtolower($dimension_name)];
        }

        throw new \ErrorException('dimension name '.$dimension_name.' not found in database '.
            $this->getDatabase()->getName());
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return array
     *
     * @see Database::getDimensionListRecordByName() alias
     */
    public function getDimensionListRecord(string $dimension_name): array
    {
        return $this->getDimensionListRecordByName($dimension_name);
    }

    /**
     * @param int $dimension_id
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getDimensionListRecordById(int $dimension_id): array
    {
        $this->listDimensions();

        if (!$this->hasDimensionById($dimension_id)) {
            throw new \InvalidArgumentException('Unknown dimension ID '.$dimension_id.' given.');
        }

        return $this->dimensionList['olap_id'][$dimension_id];
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getDimensionListRecordByName(string $dimension_name): array
    {
        $this->listDimensions();
        $dimension_id = $this->getDimensionIdFromName($dimension_name);

        return $this->getDimensionListRecordById($dimension_id);
    }

    /**
     * @param int $dimension_id
     *
     * @throws \ErrorException
     *
     * @return string
     */
    public function getDimensionNameFromId(int $dimension_id): string
    {
        if ($this->hasDimensionById($dimension_id)) {
            return $this->dimensionList['olap_id'][$dimension_id][1];
        }

        throw new \ErrorException('dimension id '.$dimension_id.' not found in database '.
            $this->getDatabase()->getName());
    }

    /**
     * @param Connection $connection
     * @param string     $database_name
     *
     * @throws \Exception
     *
     * @return Database
     */
    public static function getInstance(Connection $connection, string $database_name): Database
    {
        return $connection->getDatabaseByName($database_name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->metaInfo[1];
    }

    /**
     * @return int
     */
    public function getOlapObjectId(): int
    {
        return (int) $this->metaInfo[0];
    }

    /**
     * @param string $cube_name
     *
     * @return bool
     *
     * @see Database::hasCubeByName() alias
     */
    public function hasCube(string $cube_name): bool
    {
        return $this->hasCubeByName($cube_name);
    }

    /**
     * @param int $cubeId
     *
     * @return bool
     */
    public function hasCubeById(int $cubeId): bool
    {
        return isset($this->cubeList['olap_id'][$cubeId]);
    }

    /**
     * @param string $cubeName
     *
     * @return bool
     */
    public function hasCubeByName(string $cubeName): bool
    {
        return isset($this->cubeList['olap_name'][\strtolower($cubeName)]);
    }

    /**
     * @param string $dimension_name
     *
     * @return bool
     *
     * @see Database::hasDimensionByName() alias
     */
    public function hasDimension(string $dimension_name): bool
    {
        return $this->hasDimensionByName($dimension_name);
    }

    /**
     * @param int $dimension_id
     *
     * @return bool
     */
    public function hasDimensionById(int $dimension_id): bool
    {
        return isset($this->dimensionList['olap_id'][$dimension_id]);
    }

    /**
     * @param string $dimensionName
     *
     * @return bool
     */
    public function hasDimensionByName(string $dimensionName): bool
    {
        return isset($this->dimensionList['olap_name'][\strtolower($dimensionName)]);
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function info(): array
    {
        $database_info = $this->getConnection()->request(self::API_DATABASE_INFO, [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'show_permission' => 1,
                'show_counters' => 1,
                'mode' => 0,
            ],
        ]);

        if (!isset($database_info[0])) {
            throw new \ErrorException('database information not found');
        }

        $this->metaInfo = $database_info[0];

        return $this->metaInfo;
    }

    /**
     * prefetch dimension and cube information.
     *
     * @throws \Exception
     */
    public function init(): bool
    {
        $this->initDimensions();
        $this->initCubes();

        return true;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function initCubes(): bool
    {
        // @todo make Database::initCubes() to cast all names to lower case (to work case insensitive)
        $cube_list = $this->listCubes(false);

        $tmp_cube_list = [
            'olap_id' => [],
            'olap_name' => [],
        ];

        foreach ($cube_list as $cube_row) {
            $tmp_cube_list['olap_id'][(int) $cube_row[0]] = (array) $cube_row;
            $tmp_cube_list['olap_name'][\strtolower($cube_row[1])] = (int) $cube_row[0];
        }

        $this->cubeList = $tmp_cube_list;
        $this->cubeList['simple'] = \array_flip($this->cubeList['olap_name']);

        return true;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function initDimensions(): bool
    {
        // @todo make Database::initDimensions() to cast all names to lower case (to work case insensitive)
        $dimension_list = $this->listDimensions(false);

        $tmp_dim_list = [
            'olap_id' => [],
            'olap_name' => [],
        ];

        foreach ($dimension_list as $dimension_row) {
            $tmp_dim_list['olap_id'][(int) $dimension_row[0]] = (array) $dimension_row;
            $tmp_dim_list['olap_name'][\strtolower($dimension_row[1])] = (int) $dimension_row[0];
        }

        $this->dimensionList = $tmp_dim_list;
        $this->dimensionList['simple'] = \array_flip($this->dimensionList['olap_name']);

        return true;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->getConnection()->isDebugMode();
    }

    /**
     * @param null|bool  $cached
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return array
     */
    public function listCubes(?bool $cached = null, ?array $options = null): array
    {
        $cached = $cached ?? true;

        $options = (array) $options;

        if (true === $cached && null !== $this->cubeList) {
            return $this->cubeList['simple'];
        }

        $cube_list = $this->getConnection()->request(self::API_DATABASE_CUBES, [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'show_normal' => $options['show_normal'] ?? 1,
                'show_system' => $options['show_system'] ?? 1,
                'show_attribute' => $options['show_attribute'] ?? 1,
                'show_info' => $options['show_info'] ?? 1,
                'show_gputype' => $options['show_gputype'] ?? 1,
                'show_gpuflag' => $options['show_gpuflag'] ?? 1,
                'show_audit' => $options['show_audit'] ?? 1,
                'show_permission' => $options['show_permission'] ?? 1,
                'show_zero' => 1,
            ],
        ]);

        if ((bool) ($options['palo_compat'] ?? false)) {
            return \array_column($cube_list->getArrayCopy(), 1);
        }

        return $cube_list->getArrayCopy();
    }

    /**
     * @param null|bool  $cached
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return array
     */
    public function listDimensions(?bool $cached = null, ?array $options = null): array
    {
        $cached = $cached ?? true;

        $options = (array) $options;

        if (true === $cached && null !== $this->dimensionList) {
            return $this->dimensionList['simple'];
        }

        $params = [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'show_normal' => $options['show_normal'] ?? 1,
                'show_system' => $options['show_system'] ?? 1,
                'show_attribute' => $options['show_attribute'] ?? 1,
                'show_info' => $options['show_info'] ?? 1,
                'show_permission' => $options['show_permission'] ?? 1,
                'show_default_elements' => $options['show_default_elements'] ?? 1,
                'show_count_by_type' => $options['show_count_by_type'] ?? 1,
            ],
        ];

        if (isset($options['name_element'])) {
            $params['query']['name_element'] = $options['name_element'];
        }

        $dimension_list = $this->getConnection()->request(self::API_DATABASE_DIMENSIONS, $params);

        if ((bool) ($options['palo_compat'] ?? false)) {
            return \array_column($dimension_list->getArrayCopy(), 1);
        }

        return $dimension_list->getArrayCopy();
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function load(): bool
    {
        $response = $this->getConnection()->request(self::API_DATABASE_LOAD, [
            'query' => [
                'database' => $this->getOlapObjectId(),
            ],
        ]);

        return '1' === ($response[0] ?? '0');
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function rebuildMarkers(): bool
    {
        $response = $this->getConnection()->request(self::API_DATABASE_REBUILD_MARKERS, [
            'query' => [
                'database' => $this->getOlapObjectId(),
            ],
        ]);

        return '1' === ($response[0] ?? '0');
    }

    /**
     * @throws \Exception
     *
     * @return Database
     */
    public function reload(): Database
    {
        return $this->getConnection()->reload()
            ->getDatabaseById($this->getDatabase()->getOlapObjectId())
        ;
    }

    /**
     * @param string $name_old
     * @param string $name_new
     *
     * @throws \Exception
     *
     * @return Cube
     */
    public function renameCube(string $name_old, string $name_new): Cube
    {
        // @todo refactor Database::renameCube() to only change name inside cube objects

        if (!$this->hasCubeByName($name_old)) {
            throw new \InvalidArgumentException('Unknown cube name '.$name_old.' given.');
        }

        $response = $this->getConnection()->request(self::API_CUBE_RENAME, [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'name_cube' => $name_old,
                'new_name' => $name_new,
            ],
        ]);

        if ('0' === ($response[0] ?? '0')) {
            throw new \ErrorException('Cube rename from '.$name_old.' to '.$name_new.' failed');
        }

        // @todo throw exception if rename was not successful

        // delete/replace references of old cube in data model
        $cube_id = $this->getCubeIdFromName($name_old);
        $cube_old = $this->getCubeById($cube_id);
        unset($cube_old, $this->cubes[$cube_id]);

        // @todo Database::renameCube() - reload database/cubes??

        return $this->getCubeByName($name_new);
    }

    /**
     * @param string $name_old
     * @param string $name_new
     *
     * @throws \Exception
     *
     * @return Dimension
     */
    public function renameDimension(string $name_old, string $name_new): Dimension
    {
        // @todo refactor Database::renameDimension() to only change name inside dimension objects

        if (!$this->hasDimensionByName($name_old)) {
            throw new \InvalidArgumentException('Unknown dimension name '.$name_old.' given.');
        }

        $response = $this->getConnection()->request(self::API_DIMENSION_RENAME, [
            'query' => [
                'database' => $this->getOlapObjectId(),
                'name_dimension' => $name_old,
                'new_name' => $name_new,
            ],
        ]);

        if ('0' === ($response[0] ?? '0')) {
            throw new \ErrorException('Dimension rename from '.$name_old.' to '.$name_new.' failed');
        }

        // delete/replace references of old dimension in data model
        $dimension_id = $this->getDimensionIdFromName($name_old);
        $dimension_old = $this->getDimensionById($dimension_id);
        unset($dimension_old, $this->dimensions[$dimension_id]);

        // @TODO Database::renameDimension() - reload databases/dimensions??

        return $this->getDimensionByName($name_new);
    }

    /**
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function save(?array $options = null): bool
    {
        $params = [
            'query' => [
                'database' => $this->getOlapObjectId(),
            ],
        ];

        if (isset($options['external_identifier'])) {
            $params['query']['external_identifier'] = $options['external_identifier'];
        }

        if (isset($options['mode'])) {
            $params['query']['mode'] = $options['mode'];
        }

        if (isset($options['show_system'])) {
            $params['query']['show_system'] = $options['show_system'];
        }

        if (isset($options['include_archive'])) {
            $params['query']['include_archive'] = $options['include_archive'];
        }

        if (isset($options['show_audit'])) {
            $params['query']['show_audit'] = $options['show_audit'];
        }

        if (isset($options['include_csv'])) {
            $params['query']['include_csv'] = $options['include_csv'];
        }

        if (isset($options['password'])) {
            $params['query']['password'] = $options['password'];
        }

        if (isset($options['type'])) {
            $params['query']['type'] = $options['type'];
        }

        $response = $this->getConnection()->request(self::API_DATABASE_SAVE, $params);

        return '1' === ($response[0] ?? '0');
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function unload(): bool
    {
        $response = $this->getConnection()->request(self::API_DATABASE_UNLOAD, [
            'query' => [
                'database' => $this->getOlapObjectId(),
            ],
        ]);

        return '1' === ($response[0] ?? '0');
    }
}
