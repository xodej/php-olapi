<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use Xodej\Olapi\ApiRequestParams\ApiCubeCreate;
use Xodej\Olapi\ApiRequestParams\ApiCubeDestroy;
use Xodej\Olapi\ApiRequestParams\ApiCubeRename;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseCubes;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseDimensions;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseGenerateScript;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseInfo;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseLoad;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseRebuildMarkers;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseSave;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseUnload;
use Xodej\Olapi\ApiRequestParams\ApiDimensionCreate;
use Xodej\Olapi\ApiRequestParams\ApiDimensionDestroy;
use Xodej\Olapi\ApiRequestParams\ApiDimensionRename;

/**
 * Class Database.
 */
class Database implements IBase
{
    private Connection $connection;
    private DimensionCollection $dimensions;
    private CubeCollection $cubes;

    /**
     * @var string[]
     */
    private array $metaInfo;

    /**
     * @var null|array<int,array<string>>
     */
    private ?array $cubeLookupByID = null;

    /**
     * @var null|array<string,int>
     */
    private ?array $cubeLookupByName = null;

    /**
     * @var null|array<int,array<string>>
     */
    private ?array $dimensionLookupByID = null;

    /**
     * @var null|array<string,int>
     */
    private ?array $dimensionLookupByName = null;

    /**
     * Database constructor.
     *
     * @param Connection $connection
     * @param string[]   $metaInfo
     *
     * @throws \Exception
     */
    public function __construct(Connection $connection, array $metaInfo)
    {
        $this->connection = $connection;
        $this->metaInfo = $metaInfo;

        $this->dimensions = new DimensionCollection();
        $this->cubes = new CubeCollection();

        $this->listDimensions(false);
        $this->listCubes(false);
    }

    /**
     * @param string   $name
     * @param string[] $dimensionNames
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createCube(string $name, array $dimensionNames): bool
    {
        $request = new ApiCubeCreate();
        $request->database = $this->getOlapObjectId();
        $request->new_name = $name;
        $request->name_dimensions = \implode(',', $dimensionNames);

        $response = $this->getConnection()->request($request);

        // @todo Database::createCube() - reload cubes
        return '1' === ($response[0][0] ?? '0');
    }

    /**
     * @param string                        $dimension_name
     * @param null|ApiDimensionCreate       $request
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createDimension(string $dimension_name, ?ApiDimensionCreate $request = null): bool
    {
        $request ??= new ApiDimensionCreate();
        $request->database = $this->getOlapObjectId();
        $request->new_name = $dimension_name;

        $response = $this->getConnection()->request($request);

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

        $request = new ApiCubeDestroy();
        $request->database = $this->getOlapObjectId();
        $request->cube = $cube_id;

        $response = $this->getConnection()->request($request);

        $this->listCubes(false);

        return '1' === ($response[0][0] ?? '0');
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

        $request = new ApiDimensionDestroy();
        $request->database = $this->getOlapObjectId();
        $request->dimension = $dimension_id;

        $response = $this->getConnection()->request($request);

        $this->listDimensions(false);

        return '1' === ($response[0][0] ?? '0');
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
     * @param null|string[]                        $dimension_names
     * @param null|string[]                        $cube_names
     * @param null|ApiDatabaseGenerateScript       $request
     *
     * @throws \Exception
     *
     * @return null|string
     */
    public function generateScript(
        ?array $dimension_names = null,
        ?array $cube_names = null,
        ?ApiDatabaseGenerateScript $request = null
    ): ?string {
        $request = $request ?? new ApiDatabaseGenerateScript();
        $request->database = $this->getOlapObjectId();
        if (null !== $dimension_names) {
            $request->name_dimensions = \implode(',', $dimension_names);
        }
        if (null !== $cube_names) {
            $request->name_cubes = \implode(',', $cube_names);
        }
        $response = $this->getConnection()->requestRaw($request->url(), $request->asArray());

        if (null === $response) {
            return '';
        }

        if (false === ($return = \stream_get_contents($response))) {
            return null;
        }

        return $return;
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

        return $this->cubeLookupByName[\strtolower($cube_name)];
    }

    /**
     * @param string $cube_name
     *
     * @throws \ErrorException
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return string[]
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
     * @return string[]
     */
    public function getCubeListRecordById(int $cube_id): array
    {
        if (!$this->hasCubeById($cube_id)) {
            throw new \InvalidArgumentException('Unknown cube ID '.$cube_id.' given.');
        }

        return $this->cubeLookupByID[$cube_id];
    }

    /**
     * @param string $cube_name
     *
     * @throws \ErrorException
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return string[]
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

        return $this->cubeLookupByID[$cube_id][1];
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
        if (!isset($this->dimensions[$dimension_id]) || !$use_cache) {
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
        if (!$this->hasDimensionByName($dimension_name)) {
            throw new \ErrorException('dimension name '.$dimension_name.' not found in database '.
                $this->getDatabase()->getName());
        }

        return $this->dimensionLookupByName[\strtolower($dimension_name)];
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return string[]
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
     * @return string[]
     */
    public function getDimensionListRecordById(int $dimension_id): array
    {
        if (!$this->hasDimensionById($dimension_id)) {
            throw new \InvalidArgumentException('Unknown dimension ID '.$dimension_id.' given.');
        }

        return $this->dimensionLookupByID[$dimension_id];
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return string[]
     */
    public function getDimensionListRecordByName(string $dimension_name): array
    {
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
        if (!$this->hasDimensionById($dimension_id)) {
            throw new \ErrorException('dimension id '.$dimension_id.' not found in database '.
                $this->getDatabase()->getName());
        }

        return $this->dimensionLookupByID[$dimension_id][1];
    }

    /**
     * @param Connection $connection
     * @param string     $database_name
     *
     * @throws \Exception
     *
     * @return Database
     */
    public static function getInstance(Connection $connection, string $database_name): self
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
        return isset($this->cubeLookupByID[$cubeId]);
    }

    /**
     * @param string $cubeName
     *
     * @return bool
     */
    public function hasCubeByName(string $cubeName): bool
    {
        return isset($this->cubeLookupByName[\strtolower($cubeName)]);
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
        return isset($this->dimensionLookupByID[$dimension_id]);
    }

    /**
     * @param string $dimensionName
     *
     * @return bool
     */
    public function hasDimensionByName(string $dimensionName): bool
    {
        return isset($this->dimensionLookupByName[\strtolower($dimensionName)]);
    }

    /**
     * @throws \Exception
     *
     * @return string[]
     */
    public function info(): array
    {
        $request = new ApiDatabaseInfo();
        $request->database = $this->getOlapObjectId();
        $request->show_permission = true;
        $request->show_counters = true;

        $database_info = $this->getConnection()->request($request);

        if (!isset($database_info[0])) {
            throw new \ErrorException('database information not found');
        }

        $this->metaInfo = $database_info[0];

        return $this->metaInfo;
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
     * @param null|bool                   $cached
     * @param null|ApiDatabaseCubes       $request
     *
     * @throws \Exception
     *
     * @return array<int,array<string>>|array<int,string>
     */
    public function listCubes(?bool $cached = null, ?ApiDatabaseCubes $request = null): array
    {
        $cached = $cached ?? true;

        if (true === $cached && null !== $this->cubeLookupByID) {
            return $this->cubeLookupByID;
        }

        $request ??= new ApiDatabaseCubes();
        $request->database = $this->getOlapObjectId();

        $request->show_normal ??= true;
        $request->show_system ??= true;
        $request->show_attribute ??= true;
        $request->show_info ??= true;
        $request->show_gpuflag ??= true;
        $request->show_audit ??= true;
        $request->show_permission ??= true;

        $cube_list = $this->getConnection()->request($request);

        $this->cubes = new CubeCollection();
        $this->cubeLookupByID = [];
        $this->cubeLookupByName = [];

        foreach ($cube_list as $cube_row) {
            $this->cubeLookupByID[(int) $cube_row[0]] = (array) $cube_row;
            $this->cubeLookupByName[\strtolower($cube_row[1])] = (int) $cube_row[0];
        }

        return $this->cubeLookupByID;
    }

    /**
     * @param null|bool                        $cached
     * @param null|ApiDatabaseDimensions       $request
     *
     * @throws \Exception
     *
     * @return array<int,array<string>>|array<int,string>
     */
    public function listDimensions(?bool $cached = null, ?ApiDatabaseDimensions $request = null): array
    {
        $cached = $cached ?? true;

        if (true === $cached && null !== $this->dimensionLookupByID) {
            return $this->dimensionLookupByID;
        }

        $request ??= new ApiDatabaseDimensions();
        $request->database = $this->getOlapObjectId();

        $request->show_normal ??= true;
        $request->show_system ??= true;
        $request->show_attribute ??= true;
        $request->show_info ??= true;
        $request->show_permission ??= true;
        $request->show_default_elements ??= true;
        $request->show_count_by_type ??= true;

        $dimension_list = $this->getConnection()->request($request);

        $this->dimensions = new DimensionCollection();
        $this->dimensionLookupByID = [];
        $this->dimensionLookupByName = [];

        foreach ($dimension_list as $dimension_row) {
            $this->dimensionLookupByID[(int) $dimension_row[0]] = (array) $dimension_row;
            $this->dimensionLookupByName[\strtolower($dimension_row[1])] = (int) $dimension_row[0];
        }

        return $this->dimensionLookupByID;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function load(): bool
    {
        $request = new ApiDatabaseLoad();
        $request->database = $this->getOlapObjectId();

        $response = $this->getConnection()->request($request);

        return '1' === ($response[0][0] ?? '0');
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function rebuildMarkers(): bool
    {
        $request = new ApiDatabaseRebuildMarkers();
        $request->database = $this->getOlapObjectId();

        $response = $this->getConnection()->request($request);

        return '1' === ($response[0][0] ?? '0');
    }

    /**
     * @throws \Exception
     *
     * @return Database
     */
    public function reload(): self
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

        $request = new ApiCubeRename();
        $request->database = $this->getOlapObjectId();
        $request->cube = $this->getCubeIdFromName($name_old);
        $request->new_name = $name_new;

        $response = $this->getConnection()->request($request);

        if ('0' === ($response[0][0] ?? '0')) {
            throw new \ErrorException('Cube rename from '.$name_old.' to '.$name_new.' failed');
        }

        // @todo throw exception if rename was not successful

        // delete/replace references of old cube in data model
        $this->listCubes(false);

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

        $request = new ApiDimensionRename();
        $request->database = $this->getOlapObjectId();
        $request->dimension = $this->getDimensionIdFromName($name_old);
        $request->new_name = $name_new;

        $response = $this->getConnection()->request($request);

        if ('0' === ($response[0][0] ?? '0')) {
            throw new \ErrorException('Dimension rename from '.$name_old.' to '.$name_new.' failed');
        }

        // delete/replace references of old dimension in data model
        $this->listDimensions(false);

        return $this->getDimensionByName($name_new);
    }

    /**
     * @param null|ApiDatabaseSave $request
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function save(?ApiDatabaseSave $request = null): bool
    {
        $request ??= new ApiDatabaseSave();
        $request->database = $this->getOlapObjectId();

        $response = $this->getConnection()->request($request);

        return '1' === ($response[0][0] ?? '0');
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function unload(): bool
    {
        $request = new ApiDatabaseUnload();
        $request->database = $this->getOlapObjectId();

        $response = $this->getConnection()->request($request);

        return '1' === ($response[0][0] ?? '0');
    }
}
