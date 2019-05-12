<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Cube.
 */
class Cube implements IBase
{
    public const API_CUBE_CLEAR = '/cube/clear';
    public const API_CUBE_CLEAR_CACHE = '/cube/clear_cache';
    public const API_CUBE_COMMIT = '/cube/commit';
    public const API_CUBE_CONVERT = '/cube/convert';
    public const API_CUBE_CREATE = '/cube/create';
    public const API_CUBE_DESTROY = '/cube/destroy';
    public const API_CUBE_GENERATE_SCRIPT = '/cube/generate_script';
    public const API_CUBE_HOLDS = '/cube/holds';
    public const API_CUBE_INFO = '/cube/info';
    public const API_CUBE_LOAD = '/cube/load';
    public const API_CUBE_LOCK = '/cube/lock';
    public const API_CUBE_LOCKS = '/cube/locks';
    public const API_CUBE_RENAME = '/cube/rename';
    public const API_CUBE_ROLLBACK = '/cube/rollback';
    public const API_CUBE_RULES = '/cube/rules';
    public const API_CUBE_SAVE = '/cube/save';
    public const API_CUBE_UNLOAD = '/cube/unload';

    public const API_CELL_AREA = '/cell/area';
    public const API_CELL_COPY = '/cell/copy';
    public const API_CELL_DRILLTHROUGH = '/cell/drillthrough';
    public const API_CELL_EXPORT = '/cell/export';
    public const API_CELL_GOALSEEK = '/cell/goalseek';
    public const API_CELL_REPLACE = '/cell/replace';
    public const API_CELL_REPLACE_BULK = '/cell/replace_bulk';
    public const API_CELL_VALUE = '/cell/value';
    public const API_CELL_VALUES = '/cell/values';

    public const API_RULE_CREATE = '/rule/create';
    public const API_RULE_DESTROY = '/rule/destroy';
    public const API_RULE_FUNCTIONS = '/rule/functions';
    public const API_RULE_INFO = '/rule/info';
    public const API_RULE_MODIFY = '/rule/modify';
    public const API_RULE_PARSE = '/rule/parse';

    public const API_HOLD_CREATE = '/hold/create';
    public const API_HOLD_DESTROY = '/hold/destroy';

    public const DRILL_MODE_SVS = 1;
    public const DRILL_MODE_SVS_SECONDARY = 2;
    public const DRILL_MODE_AUDIT = 3;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var bool
     */
    protected $persistCachedValues = false;

    /**
     * @var DimensionStore
     */
    private $dimensions;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var array
     */
    private $cachedValues = [];

    /**
     * @var array
     */
    private $metaInfo;

    /**
     * @var ?array
     */
    private $cubeDimensionList;

    /**
     * Holds the state if caching mode is on.
     *
     * @see Cube::getValueC()
     *
     * @var bool
     */
    private $inCacheMode = false;

    /**
     * Cube constructor.
     *
     * @param Database $database
     * @param array    $metaInfo
     *
     * @throws \Exception
     */
    public function __construct(Database $database, array $metaInfo)
    {
        $this->database = $database;
        $this->metaInfo = $metaInfo;

        $this->dimensions = new DimensionStore();
    }

    /**
     * @param null|array $requestParameters
     * @param null|bool  $show_headers
     * @param null|bool  $replace_special_chars
     * @param null|int   $max_rows
     *
     * @throws \ErrorException
     *
     * @return array
     */
    public function arrayExport(
        ?array $requestParameters = null,
        ?bool $show_headers = null,
        ?bool $replace_special_chars = null,
        ?int $max_rows = null
    ): array {
        $max_rows = $max_rows ?? 10000;
        $show_headers = $show_headers ?? true;

        $stream = $this->export($requestParameters, $show_headers, $replace_special_chars);

        if (!\is_resource($stream)) {
            return [];
        }

        \rewind($stream);

        $return = [];
        $row_counter = 0;
        while (false !== ($data_line = \fgetcsv($stream))) {
            ++$row_counter;
            if (null === $data_line) {
                continue;
            }

            $return[] = (array) $data_line;

            if ($row_counter >= $max_rows) {
                break;
            }
        }
        \fclose($stream);

        return $return;
    }

    /**
     * @return bool
     */
    public function cacheCollectionEnabled(): bool
    {
        return $this->inCacheMode;
    }

    /**
     * Deletes all data, or all data in specified coordinates.
     *
     * @param null|array $element_list multi-dimensional array of elements to be cleared or all
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function clear(?array $element_list = null): bool
    {
        $complete = 0;
        if (null === $element_list) {
            $complete = 1;
        }

        $response = $this->getConnection()->request(self::API_CUBE_CLEAR, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'area' => $this->createArea((array) $element_list),
                'complete' => $complete,
            ],
        ]);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param string $lock_id
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function commit(string $lock_id): bool
    {
        $response = $this->getConnection()->request(self::API_CUBE_COMMIT, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'lock' => $lock_id,
            ],
        ]);

        return (bool) $response[0];
    }

    /**
     * Convert normal or gpu type cube to cube type (0=normal or 4=gpu type).
     *
     * @param int $cube_type 0=normal or 4=gpu type
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function convert(int $cube_type): bool
    {
        $response = $this->getConnection()->request(self::API_CUBE_CONVERT, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'type' => $cube_type,
            ],
        ]);

        return (bool) $response[0];
    }

    /**
     * Copies a cell path or a calculated predictive value to an other cell path.
     *
     * @param array      $path_from
     * @param array      $path_to
     * @param null|bool  $use_rules
     * @param null|mixed $value
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function copyValue(
        array $path_from,
        array $path_to,
        ?bool $use_rules = null,
        $value = null,
        ?array $options = null
    ): bool {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'path' => $this->buildPathFromElements($path_from),
                'path_to' => $this->buildPathFromElements($path_to),
            ],
        ];

        if (null !== $use_rules) {
            $params['query']['use_rules'] = (int) $use_rules;
        }
        if (null !== $value) {
            $params['query']['value'] = (string) $value;
        }
        if (isset($options['locked_paths'])) {
            $params['query']['locked_paths'] = $options['locked_paths'];
        }
        if (isset($options['wait'])) {
            $params['query']['wait'] = (int) $options['wait'];
        }
        if (isset($options['function'])) {
            $params['query']['function'] = (int) $options['function'];
        }
        if (isset($options['area'])) {
            $params['query']['area'] = $options['area'];
        }

        $response = $this->getConnection()->request(self::API_CELL_COPY, $params);

        return (bool) $response[0];
    }

    /**
     * @param array $elementsBucketParam
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return string
     */
    public function createArea(array $elementsBucketParam): string
    {
        return \implode(',', $this->createSubcube($elementsBucketParam));
    }

    /**
     * @param Area $area
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function createHold(Area $area): Store
    {
        return $this->getDatabase()->getConnection()->request(self::API_HOLD_CREATE, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'area' => $area->getArea(),
            ],
        ]);
    }

    /**
     * Creates a new enterprise rule for a cube.
     *
     * @param string     $definition
     * @param null|bool  $activate
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createRule(string $definition, ?bool $activate = null, ?array $options = null): array
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'definition' => $definition,
                'activate' => (int) ($activate ?? true),
            ],
        ];

        if (isset($options['external_identifier'])) {
            $params['query']['external_identifier'] = $options['external_identifier'];
        }

        if (isset($options['comment'])) {
            $params['query']['comment'] = $options['comment'];
        }

        if (isset($options['use_identifier'])) {
            $params['query']['use_identifier'] = $options['use_identifier'];
        }

        if (isset($options['position'])) {
            $params['query']['position'] = $options['position'];
        }

        if (isset($options['source'])) {
            $params['query']['source'] = $options['source'];
        }

        return $this->getConnection()
            ->request(self::API_RULE_CREATE, $params)
            ->getArrayCopy()
            ;
    }

    /**
     * @param array $elementsBucketParam
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createSubcube(array $elementsBucketParam): array
    {
        foreach ($elementsBucketParam as $dimension_name => $temp_trash) {
            if (!$this->hasDimension($dimension_name)) {
                throw new \InvalidArgumentException('unknown dimension given in area specifications');
            }
        }

        $dimensions = $this->listDimensions(true);

        $elements_bucket = [];
        foreach ($dimensions as $dimension) {
            $elements_bucket[] = $elementsBucketParam[$dimension] ?? '*';
        }

        $ret_area = [];
        foreach ($elements_bucket as $dim_index => $dim_elements) {
            $dim_elements = (array) $dim_elements;

            $elements = [];
            foreach ($dim_elements as $dim_element) {
                if (null === $dim_element || '*' === $dim_element || '' === $dim_element) {
                    $elements[] = '*';

                    continue;
                }
                $elements[] = $this->getDatabase()
                    ->getDimensionByName($dimensions[$dim_index])
                    ->getElementIdFromName((string) $dim_element)
                ;
            }

            $element_collection = \implode(':', $elements);
            $ret_area[] = $element_collection;
        }

        return $ret_area;
    }

    /**
     * Removes an enterprise rule from a cube.
     *
     * @param null|int[] $rule_ids Null deletes all rules in cube
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteRules(?array $rule_ids = null): bool
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
            ],
        ];

        if (null !== $rule_ids && isset($rule_ids[0])) {
            $params['query']['rule'] = \implode(',', $rule_ids);
        }

        $response = $this->getDatabase()->getConnection()->request(self::API_RULE_DESTROY, $params);

        return (bool) $response[0];
    }

    /**
     * @param string   $hold_identifier
     * @param null|int $complete
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function destroyHold(string $hold_identifier, ?int $complete = null): bool
    {
        $complete = $complete ?? 0;

        $response = $this->getDatabase()->getConnection()->request(self::API_HOLD_DESTROY, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'complete' => $complete,
                'hold' => $hold_identifier,
            ],
        ]);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param int[]|string[] $path
     * @param null|int       $drill_mode
     * @param null|array     $request_parameters
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function drillthrough(array $path, ?int $drill_mode = null, ?array $request_parameters = null): Store
    {
        $request_parameters = $request_parameters ?? [];
        $drill_mode = $drill_mode ?? self::DRILL_MODE_SVS;

        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'mode' => $drill_mode,
            ],
        ];

        if (\is_int($path[0]) || \is_numeric($path[0])) {
            $params['query']['path'] = \implode(',', $path);
        } else {
            $params['query']['name_path'] = \implode(',', $path);
        }

        // Audit cell history mode = 3
        if (self::DRILL_MODE_AUDIT === $drill_mode) {
            /* @noinspection PhpParamsInspection */ // @todo inspection
            $params['query']['area'] = new Area($this);
            $params['query']['definition'] = $request_parameters['definition'] ?? 'USER-D';
            $params['query']['blocksize'] = $request_parameters['blocksize'] ?? 1000;
            $params['query']['value'] = $request_parameters['value'] ?? 0;
            $params['query']['source'] = $request_parameters['source'] ?? '';
            $params['query']['condition'] = $request_parameters['condition'] ?? '';
        }

        return $this->getDatabase()->getConnection()->request(self::API_CELL_DRILLTHROUGH, $params);
    }

    /**
     * @throws \Exception
     */
    public function endCache(): void
    {
        if (!$this->cacheCollectionEnabled()) {
            throw new \ErrorException('Cube::endCache() not permitted on uncached calls');
        }

        $this->inCacheMode = false;

        if (0 === $this->getCacheSize()) {
            return;
        }

        $str_query = \implode(':', $this->cache);

        $response = $this->getConnection()->request(self::API_CELL_VALUES, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'paths' => $str_query,
                // 'show_lock_info' => 1,
                // 'show_rule'      => 1
            ],
        ]);

        $index = -1;
        foreach ($this->cache as $path_hash => $value) {
            ++$index;
            // if ('1' !== $response[$index][1]) {
            //    $this->cachedValues[$path_hash] = '#NA';
            //
            //    continue;
            // }
            $this->cachedValues[$path_hash] = ('2' === $response[$index][0] ?
                (string) $response[$index][2] : (float) $response[$index][2]);
        }

        $this->cache = [];
    }

    /**
     * @param null|array $requestParameters
     * @param null|bool  $show_headers
     * @param null|bool  $replace_special_chars
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return bool|resource
     */
    public function export(
        ?array $requestParameters = null,
        ?bool $show_headers = null,
        ?bool $replace_special_chars = null
    ) {
        $show_headers = $show_headers ?? true;
        $replace_special_chars = $replace_special_chars ?? false;

        $database = $this->getDatabase();
        $dimensions = $this->listDimensions();

        $data_stream = $this->streamExport($requestParameters);

        if (!\is_resource($data_stream)) {
            throw new \ErrorException('failed to open data stream');
        }

        if (false === ($ret_stream = \fopen('php://temp/maxmemory:10485760', 'wb+'))) {
            throw new \ErrorException('failed to open temporary file');
        }

        if ($show_headers) {
            $header = [];
            foreach ($dimensions as $dim_index => $dim_id) {
                $header[] = $database->getDimensionNameFromId($dim_id);
            }
            $header[] = '#VALUE';
            \fputcsv($ret_stream, $header);
        }

        /** @var array $data_row */
        while (false !== ($data_row = \fgetcsv($data_stream, 0, ';', '"', '"'))) {
            if (null === $data_row) {
                continue;
            }

            // split element IDs
            $elements_path = \explode(',', $data_row[3]);

            // map each element path ID to an element name
            $coordinates = \array_map(
                static function (int $dimension_order, string $element_id) use ($dimensions, $database): string {
                    return $database->getDimensionById((int) $dimensions[$dimension_order])
                        ->getElementNameFromId((int) $element_id)
                        ;
                },
                \array_keys($elements_path),
                $elements_path
            );

            // adding #VALUE column
            if ($replace_special_chars) {
                $data_row[2] = \str_replace(["\t", "\r", "\n"], [' ', '', ' '], $data_row[2]);
            }

            $coordinates[] = $data_row[2];
            \fputcsv($ret_stream, $coordinates);
        }

        \rewind($ret_stream);

        return $ret_stream;
    }

    /**
     * @param array|null $request_parameters
     * @param bool|null $show_headers
     * @param bool|null $replace_special_chars
     * @return \Generator
     * @throws \ErrorException
     * @throws \Exception
     */
    public function exportRowProcessor(
        ?array $request_parameters = null,
        ?bool $show_headers = null,
        ?bool $replace_special_chars = null
    ): \Generator {

        $show_headers = $show_headers ?? true;
        $replace_special_chars = $replace_special_chars ?? false;

        $database = $this->getDatabase();
        $dimensions = $this->listDimensions();

        // path parameter must be omitted for the first run
        $coord_path = null;

        // incrementally fetch data from server
        do {
            // fetch data
            $result = $this->doRawRequest($request_parameters, $coord_path);
            $data_stream = $result->__stream__;
            $coord_path = $result->__lastpath__;

            if (!\is_resource($data_stream)) {
                throw new \ErrorException('failed to open data stream');
            }

            // write header if required
            if ($show_headers) {
                $header = [];
                foreach ($dimensions as $dim_index => $dim_id) {
                    $header[] = $database->getDimensionNameFromId($dim_id);
                }
                $header[] = '#VALUE';
                $show_headers = false; // show headers just once

                yield $header;
            }

            /** @var array $data_row */
            while (false !== ($data_row = \fgetcsv($data_stream, 0, ';', '"', '"'))) {
                if (null === $data_row) {
                    continue;
                }

                // split element IDs
                $elements_path = \explode(',', $data_row[3]);

                // map each element path ID to an element name
                $coordinates = \array_map(
                    static function (int $dimension_order, string $element_id) use ($dimensions, $database): string {
                        return $database->getDimensionById((int) $dimensions[$dimension_order])
                            ->getElementNameFromId((int) $element_id)
                            ;
                    },
                    \array_keys($elements_path),
                    $elements_path
                );

                // adding #VALUE column
                if ($replace_special_chars) {
                    $data_row[2] = \str_replace(["\t", "\r", "\n"], [' ', '', ' '], $data_row[2]);
                }

                $coordinates[] = $data_row[2];
                yield $coordinates;
            }
        } while (!$result->__complete__); // start next cycle
    }

    /**
     * @return int
     */
    public function getCacheSize(): int
    {
        return \count($this->cache ?? []);
    }

    /**
     * @throws \Exception
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->getDatabase()->getConnection();
    }

    /**
     * @return string
     */
    public function getCubeToken(): string
    {
        return (string) $this->metaInfo[8];
    }

    /**
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * @throws \Exception
     *
     * @return DimensionStore
     */
    public function getDimensions(): DimensionStore
    {
        $dimension_list = $this->listDimensions();

        $this->dimensions = new DimensionStore();
        foreach ($dimension_list as $dimension_id) {
            $this->dimensions[] = $this->getDatabase()->getDimensionById((int) $dimension_id);
        }

        return $this->dimensions;
    }

    /**
     * @param Database $database
     * @param string   $cube_name
     *
     * @throws \Exception
     *
     * @return Cube
     */
    public static function getInstance(Database $database, string $cube_name): Cube
    {
        return $database->getCubeByName($cube_name);
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
     * @param null|string $pattern
     * @param null|bool   $use_identifier
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function getRules(?string $pattern = null, ?bool $use_identifier = null): Store
    {
        $use_identifier = $use_identifier ?? false;

        $response = $this->getConnection()->request(self::API_CUBE_RULES, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'use_identifier' => (int) $use_identifier,
            ],
        ]);

        if (null === $pattern) {
            return $response;
        }

        // filter for pattern and/or active status
        $response = \array_filter($response->getArrayCopy(), static function ($v) use ($pattern): bool {
            return 1 === \preg_match($pattern, $v[1]);
        });

        return new Store($response);
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return Subset
     */
    public function getSubset(string $dimension_name): Subset
    {
        if (!$this->hasDimensionByName($dimension_name)) {
            throw new \InvalidArgumentException('cube does not contain dimension '.$dimension_name);
        }

        $dimension = $this->getDatabase()->getDimension($dimension_name);

        return new Subset($dimension);
    }

    /**
     * @param array     $dims
     * @param null|bool $use_keys
     *
     * @throws \Exception
     *
     * @return null|float|int|string
     */
    public function getValue(array $dims, ?bool $use_keys = null)
    {
        $response = $this->getValueAsStore($dims, $use_keys)[0] ?? [];

        // if ('1' !== $response[1]) {
        //     return null;
        // }

        // in case of type numeric type cast
        return '2' === $response[0] ? (string) $response[2] : (float) $response[2];
    }

    /**
     * @param array     $dims
     * @param null|bool $use_keys
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function getValueAsStore(array $dims, ?bool $use_keys = null): Store
    {
        $use_keys = $use_keys ?? false;

        return $this->getConnection()->request(self::API_CELL_VALUE, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'path' => $this->buildPathFromElements($dims, $use_keys),
                'show_lock_info' => 1,
                'show_rule' => 1,
            ],
        ]);
    }

    /**
     * @param array     $dims
     * @param null|bool $use_keys
     *
     * @throws \Exception
     *
     * @return null|float|int|string
     */
    public function getValueC(array $dims, ?bool $use_keys = null)
    {
        $use_keys = $use_keys ?? false;

        // md5() not necessary when first step build path,
        // then store path 1,5,34,54 directly as key
        // $cube_path = $this->buildPathFromElements($dims);
        // $path_hash = \md5($cube_path);
        // or another solution \md5(\serialize($dims))

        $path_hash = \md5(\implode(',', $dims));

        // if not in caching mode the value
        // should be retrievable from cache directly
        if (!$this->cacheCollectionEnabled()) {
            // check if value is available in cache
            if (!isset($this->cachedValues[$path_hash])) {
                \trigger_error('Exception caught! Exception: ', \E_USER_WARNING);

                return '#VALUE';
            }

            // value found
            return $this->cachedValues[$path_hash];
        }

        // still in caching mode
        $this->cache[$path_hash] = $this->buildPathFromElements($dims, $use_keys);

        return '#NA';
    }

    /**
     * @param array     $dims
     * @param null|bool $use_keys
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getValueRaw(array $dims, ?bool $use_keys = null): string
    {
        $use_keys = $use_keys ?? false;

        return (string) \fgets($this->getConnection()->requestRaw(self::API_CELL_VALUE, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'path' => $this->buildPathFromElements($dims, $use_keys),
                'show_lock_info' => 1,
                'show_rule' => 1,
            ],
        ]));
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasDimension(string $dimension_name): bool
    {
        $dimensions = $this->listDimensions(true);

        return \in_array($dimension_name, $dimensions, true);
    }

    /**
     * @param string $dimension_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasDimensionByName(string $dimension_name): bool
    {
        return \in_array($dimension_name, $this->listDimensions(true), true);
    }

    /**
     * Shows cube data.
     *
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function info(?array $options = null): Store
    {
        return $this->getDatabase()->getConnection()->request(self::API_CUBE_INFO, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'show_permission' => $options['show_permission'] ?? 0,
                'show_counters' => $options['show_counters'] ?? 0,
                'show_gpuflag' => $options['show_gpuflag'] ?? 0,
                'show_audit' => $options['show_audit'] ?? 0,
                'show_zero' => $options['show_zero'] ?? 0,
                'mode' => $options['mode'] ?? 0,
                'timeout' => $options['timeout'] ?? 0,
                'wait' => $options['wait'] ?? 0,
            ],
        ]);
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
     * @param null|bool $show_names
     *
     * @throws \Exception
     *
     * @return array
     */
    public function listDimensions(?bool $show_names = null): array
    {
        if (null === $this->cubeDimensionList) {
            $this->cubeDimensionList = \array_map(static function ($v) {
                return (int) $v;
            }, \explode(',', $this->metaInfo[3]));
        }

        if (true === $show_names) {
            return \array_map([$this->getDatabase(), 'getDimensionNameFromId'], $this->cubeDimensionList);
        }

        return $this->cubeDimensionList;
    }

    /**
     * Lists the locked cube areas.
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function listLocks(): Store
    {
        return $this->getDatabase()->getConnection()->request(self::API_CUBE_LOCKS, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
            ],
        ]);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function load(): bool
    {
        $response = $this->getDatabase()->getConnection()->request(self::API_CUBE_LOAD, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
            ],
        ]);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Locks a cube area.
     *
     * @param null|array $area
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function lock(?array $area = null): Store
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
            ],
        ];

        $complete = 1;
        if (null !== $area) {
            $params['query']['area'] = $this->createArea($area);
            $complete = 0;
        }
        $params['query']['complete'] = $complete;

        return $this->getConnection()->request(self::API_CUBE_LOCK, $params);
    }

    /**
     * Modifies an enterprise rule for a cube. Use the parameter "definition" for changing the rule or
     * use the parameter "activate" for activating and deactivating.
     *
     * @param int[] $rule_identifiers
     * @param int[] $rule_positions
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function moveRules(array $rule_identifiers, array $rule_positions): Store
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
            ],
        ];

        $params['query']['rule'] = \implode(',', $rule_identifiers);
        $params['query']['position'] = \implode(',', $rule_positions);

        return $this->getDatabase()->getConnection()->request(self::API_RULE_MODIFY, $params);
    }

    /**
     * @param string $definition
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function parseRule(string $definition): Store
    {
        // @todo Cube::parseRule()
        return $this->getConnection()->request(self::API_RULE_PARSE, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'definition' => $definition,
            ],
        ]);
    }

    /**
     * @throws \Exception
     *
     * @return Cube
     */
    public function reload(): self
    {
        return $this->getConnection()
            ->getDatabaseById($this->getDatabase()->getOlapObjectId())
            ->getCubeById($this->getOlapObjectId())
            ;
    }

    /**
     * @param string   $lock_id
     * @param null|int $steps
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function rollback(string $lock_id, ?int $steps = null): bool
    {
        $response = $this->getConnection()->request(self::API_CUBE_ROLLBACK, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'lock' => $lock_id,
                'steps' => $steps ?? '',
            ],
        ]);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     *
     * @deprecated use Database:save() instead
     */
    public function save(): bool
    {
        $response = $this->getDatabase()->getConnection()->request(self::API_CUBE_SAVE, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
            ],
        ]);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param array      $values
     * @param array      $dims_multi
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setBulk(array $values, array $dims_multi, ?array $options = null): bool
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'paths' => \implode(':', \array_map([$this, 'buildPathFromElements'], $dims_multi)),
                'values' => \implode(':', \array_map(static function ($v) {
                    return Util::strputcsv([$v]);
                }, $values)),
            ],
        ];

        if (isset($options['add'])) {
            $params['query']['add'] = (int) $options['add'];
        }
        if (isset($options['splash'])) {
            $params['query']['splash'] = (int) $options['splash'];
        }
        if (isset($options['locked_paths'])) {
            $params['query']['locked_paths'] = $options['locked_paths'];
        }
        if (isset($options['event_processor'])) {
            $params['query']['event_processor'] = (int) $options['event_processor'];
        }
        if (isset($options['wait'])) {
            $params['query']['wait'] = (int) $options['wait'];
        }

        $response = $this->getConnection()->request(self::API_CELL_REPLACE_BULK, $params);

        return (bool) $response[0];
    }

    /**
     * @param mixed      $value
     * @param array      $dims
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setValue($value, array $dims, ?array $options = null): bool
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'path' => $this->buildPathFromElements($dims),
                'value' => $value,
            ],
        ];

        if (isset($options['add'])) {
            $params['query']['add'] = (int) $options['add'];
        }
        if (isset($options['splash'])) {
            $params['query']['splash'] = (int) $options['splash'];
        }
        if (isset($options['locked_paths'])) {
            $params['query']['locked_paths'] = $options['locked_paths'];
        }
        if (isset($options['event_processor'])) {
            $params['query']['event_processor'] = (int) $options['event_processor'];
        }
        if (isset($options['wait'])) {
            $params['query']['wait'] = (int) $options['wait'];
        }

        $response = $this->getConnection()->request(self::API_CELL_REPLACE, $params);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param bool|null $ignore_state
     */
    public function startCache(?bool $ignore_state = null): void
    {
        // do not clear cache etc. if state is ignored
        // just switch to cache mode
        $ignore_state  = $ignore_state ?? false;
        if ($ignore_state) {
            $this->inCacheMode = true;
            return;
        }

        $this->cache = [];
        // if persist value cache is active
        // prevent cache cleanup
        if (false === $this->persistCachedValues) {
            $this->cachedValues = [];
        }
        $this->inCacheMode = true;
    }

    /**
     * @example file_put_contents('x.csv', $cube->export());
     *
     * @param null|array $request_parameters
     *
     * @throws \Exception
     *
     * @return bool|resource
     */
    public function streamExport(?array $request_parameters = null)
    {
        // init the return stream with 10MB in memory size
        // if exceeds 10MB it's swapped into file on disk
        $ret_stream = \fopen('php://temp/maxmemory:10485760', 'wb+');

        if (false === $ret_stream) {
            throw new \ErrorException('failed to open temp stream');
        }

        // path parameter must be omitted for the first run
        $coord_path = null;

        // incrementally fetch data from server
        do {
            $result = $this->doRawRequest($request_parameters, $coord_path);
            \stream_copy_to_stream($result->__stream__, $ret_stream);
            // \fseek($ret_stream, 0, SEEK_END);
            $coord_path = $result->__lastpath__;
        } while (!$result->__complete__); // start next cycle

        // output complete data set as stream resource
        \rewind($ret_stream);

        return $ret_stream;
    }

    /**
     * @param null|array $request_parameters
     * @param null|string $coord_path
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return \stdClass
     */
    protected function doRawRequest(?array $request_parameters = null, string $coord_path = null): object
    {
        // init the return stream with 10MB in memory size
        // if exceeds 10MB it's swapped into file on disk
        $ret_stream = \fopen('php://temp/maxmemory:10485760', 'wb+');

        if (false === $ret_stream) {
            throw new \ErrorException('failed to open temp stream');
        }

        // if given area is an area object, fetch as array
        if (isset($request_parameters['area']) && $request_parameters['area'] instanceof Area) {
            $request_parameters['area'] = $request_parameters['area']->getArea();
        }

        // set up the request parameter for the API call
        $req_params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'cube' => $this->getOlapObjectId(),
                'blocksize' => (int) ($request_parameters['blocksize'] ?? 10000),
                // @todo Cube::export() - path
                'area' => $request_parameters['area'] ??
                    \implode(',', \array_fill(0, \count($this->listDimensions()), '*')),
                // @todo Cube::export() - condition
                'use_rules' => (int) ($request_parameters['use_rules'] ?? 0),
                'base_only' => (int) ($request_parameters['base_only'] ?? 1),
                'skip_empty' => (int) ($request_parameters['skip_empty'] ?? 1),
                'type' => (int) ($request_parameters['type'] ?? 0),
                // @todo Cube::export() - properties
                'show_rule' => (int) ($request_parameters['show_rule'] ?? 0),
            ],
        ];

        if (isset($request_parameters['condition'])) {
            $req_params['query']['condition'] = $request_parameters['condition'];
        }

        if (isset($request_parameters['properties'])) {
            $req_params['query']['properties'] = $request_parameters['properties'];
        }

        // use the path as offset only for
        // subsequent runs not the first run
        if (null !== $coord_path) {
            $req_params['query']['path'] = $coord_path;
        }

        // make the API call and fetch the response as stream resource
        $stream = $this->getConnection()->requestRaw(self::API_CELL_EXPORT, $req_params);

        if (null === $stream) {
            throw new \ErrorException('HTTP request to OLAP resulted in error');
        }

        \rewind($stream);

        /**
         * little hack to omit writing last line of Jedox output
         * since this is represents the progress but not actual data
         * write process lags one cycle.
         */
        $write_flag = false;
        $fore_last_row = null;
        $last_row = null;
        while (false !== ($data_row = \fgets($stream))) {
            // lag one cycle
            if ($write_flag && null !== $last_row) {
                \fwrite($ret_stream, $last_row);
            }
            $write_flag = true; // next cycle write the data from former cycle
            $fore_last_row = $last_row; // fore last row holds the path parameter = offset for the next cycle
            $last_row = $data_row;
        }
        \fclose($stream); // close response stream

        // split last line (progress) into parts to check if already read % == total % --> 100% export
        $progress = \str_getcsv($last_row ?? '', ';', '"', '"');

        if (null !== $fore_last_row) {
            // take path as offset for next cycle from the forelast row
            $coord_path = \str_getcsv($fore_last_row ?? '', ';', '"', '"')[3];
        }

        // debug / log progress
        if ($this->isDebugMode()) {
            \file_put_contents('php://stderr', 'progress: '.(($progress[0] / $progress[1]) * 100).'%'.\PHP_EOL);
        }

        // output complete data set as stream resource
        \rewind($ret_stream);

        $ret_obj = new class{
            public $__stream__;
            public $__lastpath__;
            public $__progress__;
            public $__complete__;

            public function __destruct() {
                if (null !== $this->__stream__) {
                    \fclose($this->__stream__);
                }
            }
        };

        $ret_obj->__stream__ = $ret_stream;
        $ret_obj->__lastpath__ = $coord_path;
        $ret_obj->__progress__ = ($progress[0] / $progress[1]) * 100;
        $ret_obj->__complete__ = ($progress[0] === $progress[1]);

        return $ret_obj;
    }

    /**
     * @param array     $dim_element_coordinates
     * @param null|bool $use_keys
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function buildPathFromElements(array $dim_element_coordinates, ?bool $use_keys = null): string
    {
        $use_keys = $use_keys ?? false;

        $dimensions = $this->listDimensions();

        // use given keys to build path instead of relying on correct element order
        if ($use_keys) {
            $return = [];
            foreach ($dimensions as $dim_index => $dim_id) {
                $dimension = $this->getDatabase()->getDimensionById($dim_id);
                $element = $dim_element_coordinates[$dimension->getName()] ?? null;
                if (null === $element) {
                    throw new \InvalidArgumentException('element for dimension '.
                        $dimension->getName().' missing in parameters');
                }
                $return[] = $dimension->getElementIdFromName((string) $element);
            }

            return \implode(',', $return);
        }

        // relying on elements in correct cube dimension order
        $return = [];
        foreach ($dim_element_coordinates as $dim_index => $elem_name) {
            $dimension = $this->getDatabase()->getDimensionById($dimensions[$dim_index]);
            $return[] = $dimension->getElementIdFromName($elem_name);
        }

        return \implode(',', $return);
    }
}
