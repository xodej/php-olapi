<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use Xodej\Olapi\ApiRequestParams\ApiCellCopy;
use Xodej\Olapi\ApiRequestParams\ApiCellDrillthrough;
use Xodej\Olapi\ApiRequestParams\ApiCellExport;
use Xodej\Olapi\ApiRequestParams\ApiCellReplace;
use Xodej\Olapi\ApiRequestParams\ApiCellReplaceBulk;
use Xodej\Olapi\ApiRequestParams\ApiCellValue;
use Xodej\Olapi\ApiRequestParams\ApiCellValues;
use Xodej\Olapi\ApiRequestParams\ApiCubeClear;
use Xodej\Olapi\ApiRequestParams\ApiCubeCommit;
use Xodej\Olapi\ApiRequestParams\ApiCubeConvert;
use Xodej\Olapi\ApiRequestParams\ApiCubeInfo;
use Xodej\Olapi\ApiRequestParams\ApiCubeLoad;
use Xodej\Olapi\ApiRequestParams\ApiCubeLock;
use Xodej\Olapi\ApiRequestParams\ApiCubeRollback;
use Xodej\Olapi\ApiRequestParams\ApiCubeRules;
use Xodej\Olapi\ApiRequestParams\ApiCubeSave;
use Xodej\Olapi\ApiRequestParams\ApiHoldCreate;
use Xodej\Olapi\ApiRequestParams\ApiHoldDestroy;
use Xodej\Olapi\ApiRequestParams\ApiRuleCreate;
use Xodej\Olapi\ApiRequestParams\ApiRuleDestroy;
use Xodej\Olapi\ApiRequestParams\ApiRuleModify;
use Xodej\Olapi\ApiRequestParams\ApiRuleParse;

/**
 * Class Cube.
 */
class Cube implements IBase
{
    public const DRILL_MODE_SVS = 1;
    public const DRILL_MODE_SVS_SECONDARY = 2;
    public const DRILL_MODE_AUDIT = 3;

    protected Database $database;
    protected bool $persistCachedValues = false;
    /**
     * @var DimensionCollection<Dimension>
     */
    private DimensionCollection $dimensions;

    /**
     * @var array<string,string>
     */
    private array $cache = [];

    /**
     * @var array<string,mixed>
     */
    private array $cachedValues = [];

    /**
     * @var string[]
     */
    private array $metaInfo;

    /**
     * Holds the state if caching mode is on.
     *
     * @see Cube::getValueC()
     *
     * @var bool
     */
    private bool $inCacheMode = false;

    /**
     * Cube constructor.
     *
     * @param Database $database database object
     * @param string[] $metaInfo array of meta information of cube (/cube/info)
     *
     * @throws \Exception
     */
    public function __construct(Database $database, array $metaInfo)
    {
        $this->database = $database;
        $this->metaInfo = $metaInfo;

        $this->dimensions = new DimensionCollection();
    }

    /**
     * Returns an array of cube data based on given request parameters.
     *
     * @param null|ApiCellExport $params                array of request parameters
     * @param null|bool          $show_headers          if true add headers as first array element
     * @param null|bool          $replace_special_chars if true \t, \r and \n are replaced
     * @param null|int           $max_rows              number of rows to be returned (default: 10,000)
     *
     * @throws \ErrorException
     *
     * @return array<int,array<string>>
     */
    public function arrayExport(
        ?ApiCellExport $params = null,
        ?bool $show_headers = null,
        ?bool $replace_special_chars = null,
        ?int $max_rows = null
    ): array {
        $max_rows = $max_rows ?? 10000;
        $show_headers = $show_headers ?? true;

        $stream = $this->export($params, $show_headers, $replace_special_chars);

        if (!\is_resource($stream)) {
            return [];
        }

        \rewind($stream);

        $return = [];
        $row_counter = 0;
        while (false !== ($data_line = \fgetcsv($stream))) {
            ++$row_counter;

            $return[] = $data_line;

            if ($row_counter >= $max_rows) {
                break;
            }
        }
        \fclose($stream);

        return $return;
    }

    /**
     * Returns true if cube is in caching status.
     *
     * @return bool
     */
    public function cacheCollectionEnabled(): bool
    {
        return $this->inCacheMode;
    }

    /**
     * Deletes all data, or all data in specified coordinates.
     *
     * @param null|array<string,array<string>> $element_list multi-dimensional array of elements to be cleared or all
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function clear(?array $element_list = null): bool
    {
        $complete = false;
        if (null === $element_list) {
            $complete = true;
        }

        $request = new ApiCubeClear();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->area = $this->createArea((array) $element_list);
        $request->complete = $complete;

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Commit data into cube.
     *
     * @param int $lock_id lock ID
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function commit(int $lock_id): bool
    {
        $request = new ApiCubeCommit();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->lock = $lock_id;

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Convert normal or gpu type cube to cube type (0=normal or 4=gpu type).
     *
     * @param ?int $cube_type 0=normal or 4=gpu type
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function convert(?int $cube_type = null): bool
    {
        // default is 0=normal cube type
        $cube_type ??= 0;

        $request = new ApiCubeConvert();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->type = $cube_type;

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Copies a cell path or a calculated predictive value to an other cell path.
     *
     * @param string[]               $path_sender
     * @param string[]               $path_receiver
     * @param null|bool              $use_rules
     * @param null|mixed             $value
     * @param null|ApiCellCopy       $request
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function copyValue(
        array $path_sender,
        array $path_receiver,
        ?bool $use_rules = null,
        $value = null,
        ?ApiCellCopy $request = null
    ): bool {
        $request ??= new ApiCellCopy();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->path = $this->buildPathFromElements($path_sender);
        $request->path_to = $this->buildPathFromElements($path_receiver);
        $request->use_rules ??= $use_rules;
        $request->value ??= $value;

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Returns a cube area object which can be used in exports.
     *
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
     * @return GenericCollection
     */
    public function createHold(Area $area): GenericCollection
    {
        $request = new ApiHoldCreate();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->area = $area->getArea();

        return $this->getDatabase()->getConnection()->request($request);
    }

    /**
     * Creates a new enterprise rule for a cube.
     *
     * @param string                   $definition
     * @param null|bool                $activate
     * @param null|ApiRuleCreate       $request
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createRule(string $definition, ?bool $activate = null, ?ApiRuleCreate $request = null): array
    {
        $request ??= new ApiRuleCreate();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->definition = $definition;
        $request->activate = $activate ?? true;

        return $this->getConnection()
            ->request($request)
            ->getArrayCopy()
            ;
    }

    /**
     * @param array<string,array<string>> $elementsBucketParam
     *
     * @throws \Exception
     *
     * @return array<string>
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
        $request = new ApiRuleDestroy();
        $request->database = $this->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        if (null !== $rule_ids && isset($rule_ids[0])) {
            $request->rule = \implode(',', $rule_ids);
        }

        $response = $this->getDatabase()->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param null|bool   $complete
     * @param null|string $hold_identifier
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function destroyHold(?bool $complete = null, ?string $hold_identifier = null): bool
    {
        $request = new ApiHoldDestroy();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->complete = $complete ?? false;
        $request->hold = $hold_identifier;

        if (false === $request->complete && null === $request->hold) {
            throw new \DomainException('Cube::destroyHold() requires $hold_identifier for non-complete deletes');
        }

        $response = $this->getDatabase()->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param int[]|string[]                 $path
     * @param null|int                       $drill_mode
     * @param null|ApiCellDrillthrough       $request
     *
     * @throws \Exception
     *
     * @return GenericCollection
     */
    public function drillthrough(array $path, ?int $drill_mode = null, ?ApiCellDrillthrough $request = null): GenericCollection
    {
        $request ??= new ApiCellDrillthrough();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->mode = $drill_mode ?? self::DRILL_MODE_SVS;

        if (\is_int($path[0]) || \is_numeric($path[0])) {
            $request->path = \implode(',', $path);
        } else {
            $request->name_path = \implode(',', $path);
        }

        // Audit cell history mode = 3
        if (self::DRILL_MODE_AUDIT === $drill_mode) {
            $request->area = (new Area($this))->getArea();
            $request->definition ??= 'USER-D';
            $request->blocksize ??= 1000;
            $request->value ??= 0;
            $request->source ??= '';
            $request->condition ??= '';
        }

        return $this->getDatabase()->getConnection()->request($request);
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

        $request = new ApiCellValues();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->paths = \implode(':', $this->cache);

        $response = $this->getConnection()->request($request);

        $index = -1;
        foreach ($this->cache as $path_hash => $value) {
            ++$index;
            if ('1' !== $response[$index][1]) {
                $this->cachedValues[$path_hash] = '#NA';

                continue;
            }
            $this->cachedValues[$path_hash] = ('2' === $response[$index][0] ?
                $response[$index][2] : (float) $response[$index][2]);
        }

        $this->cache = [];
    }

    /**
     * @param null|ApiCellExport       $request
     * @param null|bool                $show_headers
     * @param null|bool                $replace_special_chars
     *
     *@throws \Exception
     * @throws \ErrorException
     *
     * @return bool|resource
     */
    public function export(
        ?ApiCellExport $request = null,
        ?bool $show_headers = null,
        ?bool $replace_special_chars = null
    ) {
        $show_headers = $show_headers ?? true;
        $replace_special_chars = $replace_special_chars ?? false;

        $database = $this->getDatabase();
        $dimensions = $this->listDimensions();

        $data_stream = $this->streamExport($request);

        if (!\is_resource($data_stream)) {
            throw new \ErrorException('failed to open data stream');
        }

        if (false === ($ret_stream = \fopen('php://temp/maxmemory:10485760', 'wb+'))) {
            throw new \ErrorException('failed to open temporary file');
        }

        if ($show_headers) {
            $header = [];
            foreach ($dimensions as $dim_index => $dim_id) {
                $header[] = $database->getDimensionNameFromId((int) $dim_id);
            }
            $header[] = '#VALUE';
            \fputcsv($ret_stream, $header);
        }

        /** @var array $data_row */
        while (false !== ($data_row = \fgetcsv($data_stream, 0, ';', '"', '"'))) {
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
     * @param null|ApiCellExport $params
     * @param null|bool $show_headers
     * @param null|bool $replace_special_chars
     * @return \Generator
     * @throws \Exception
     * @throws \ErrorException
     */
    public function exportRowProcessor(
        ?ApiCellExport $params = null,
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
            $result = $this->doRawRequest($params, $coord_path);
            $data_stream = $result->__stream__;
            $coord_path = $result->__lastpath__;

            if (!\is_resource($data_stream)) {
                throw new \ErrorException('failed to open data stream');
            }

            // write header if required
            if ($show_headers) {
                $header = [];
                foreach ($dimensions as $dim_index => $dim_id) {
                    $header[] = $database->getDimensionNameFromId((int) $dim_id);
                }
                $header[] = '#VALUE';
                $show_headers = false; // show headers just once

                yield $header;
            }

            /** @var array $data_row */
            while (false !== ($data_row = \fgetcsv($data_stream, 0, ';', '"', '"'))) {
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
     * @return DimensionCollection
     */
    public function getDimensions(): DimensionCollection
    {
        $dimension_list = $this->listDimensions();

        $this->dimensions = new DimensionCollection();
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
     * @return GenericCollection
     */
    public function getRules(?string $pattern = null, ?bool $use_identifier = null): GenericCollection
    {
        $request = new ApiCubeRules();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->use_identifier = $use_identifier ?? false;

        $response = $this->getConnection()->request($request);

        if (null === $pattern) {
            return $response;
        }

        // filter for pattern and/or active status
        $response = \array_filter($response->getArrayCopy(), static function (array $v) use ($pattern): bool {
            return 1 === \preg_match($pattern, $v[1]);
        });

        return new GenericCollection($response);
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
        $response = $this->getValueAsCollection($dims, $use_keys)[0] ?? [];

        if ('1' !== $response[1]) {
            return null;
        }

        // in case of type numeric type cast
        return '2' === $response[0] ? $response[2] : (float) $response[2];
    }

    /**
     * @param array     $dims
     * @param null|bool $use_keys
     *
     * @throws \Exception
     *
     * @return GenericCollection
     */
    public function getValueAsCollection(array $dims, ?bool $use_keys = null): GenericCollection
    {
        $request = new ApiCellValue();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->path = $this->buildPathFromElements($dims, $use_keys ?? false);
        $request->show_lock_info = true;
        $request->show_rule = true;

        return $this->getConnection()->request($request);
    }

    /**
     * @param array $dims
     * @param bool|null $use_keys
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
        $request = new ApiCellValue();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->path = $this->buildPathFromElements($dims, $use_keys ?? false);

        $request->show_lock_info = true;
        $request->show_rule = true;

        return (string) \fgets($this->getConnection()->requestRaw($request->url(), $request->asArray()));
    }

    /**
     * Returns true if given dimension name is used in cube.
     *
     * @param string $dimension_name dimension name
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
     * Returns true if given dimension name is used in cube.
     *
     * @param string $dimension_name dimension name
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
     * @param null|ApiCubeInfo $request
     *
     * @throws \Exception
     *
     * @return GenericCollection<array<string>>
     */
    public function info(?ApiCubeInfo $request = null): GenericCollection
    {
        $request ??= new ApiCubeInfo();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $request->show_permission ??= false;
        $request->show_counters ??= false;
        $request->show_gpuflag ??= false;
        $request->show_audit ??= false;
        $request->show_zero ??= false;
        $request->wait ??= false;

        return $this->getDatabase()->getConnection()->request($request);
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
     * @return int[]|string[]
     */
    public function listDimensions(?bool $show_names = null): array
    {
        return \array_map(function (string $v) use ($show_names) {
            if (true === $show_names) {
                return $this->getDatabase()->getDimensionNameFromId((int) $v);
            }

            return (int) $v;
        }, \explode(',', $this->metaInfo[3]));
    }

    /**
     * Lists the locked cube areas.
     *
     * @param null|ApiCubeLock $request
     *
     * @throws \Exception
     *
     * @return GenericCollection<array<string>>
     */
    public function listLocks(?ApiCubeLock $request = null): GenericCollection
    {
        $request ??= new ApiCubeLock();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        return $this->getDatabase()->getConnection()->request($request);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function load(): bool
    {
        $request = new ApiCubeLoad();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $response = $this->getDatabase()->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Locks a cube area.
     *
     * @param null|array $area
     *
     * @throws \Exception
     *
     * @return GenericCollection<array<string>>
     */
    public function lock(?array $area = null): GenericCollection
    {
        $request = new ApiCubeLock();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $complete = true;
        if (null !== $area) {
            $request->area = $this->createArea($area);
            $complete = false;
        }
        $request->complete = $complete;

        return $this->getConnection()->request($request);
    }

    /**
     * Moves a rule to a desired position.
     *
     * @param int[] $rule_identifiers array of rule IDs
     * @param float $rule_position    array of rule positions
     *
     * @throws \Exception
     *
     * @return GenericCollection<array<string>>
     */
    public function moveRules(array $rule_identifiers, float $rule_position): GenericCollection
    {
        $request = new ApiRuleModify();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $request->rule = \implode(',', $rule_identifiers);
        $request->position = $rule_position;

        return $this->getDatabase()->getConnection()->request($request);
    }

    /**
     * Parse given rule.
     *
     * @param string $definition rule definition
     *
     * @throws \Exception
     *
     * @return string
     */
    public function parseRule(string $definition): string
    {
        $request = new ApiRuleParse();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $request->definition = $definition;

        return stream_get_contents($this->getConnection()->requestRaw('/rule/parse', $request->asArray()));
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
     * Rollback data commit (if lock was enabled).
     *
     * @param int      $lock_id lock ID
     * @param null|int $steps   number of steps (default "")
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function rollback(int $lock_id, ?int $steps = null): bool
    {
        $request = new ApiCubeRollback();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $request->lock = $lock_id;
        $request->steps = $steps;

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * [DEPRECATED] use Database::save() instead.
     *
     * @throws \Exception
     *
     * @deprecated use Database:save() instead
     */
    public function save(): bool
    {
        $request = new ApiCubeSave();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $response = $this->getDatabase()->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Write data into cube in bulk mode.
     *
     * @param array<mixed>            $values     array of values
     * @param array<array<string>>    $dims_multi array of coordinates (dimension names)
     * @param null|ApiCellReplaceBulk $request    array of options
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setBulk(array $values, array $dims_multi, ?ApiCellReplaceBulk $request = null): bool
    {
        $count_values = \count($values);
        $count_paths = \count($dims_multi);

        if ($count_values !== $count_paths) {
            throw new \InvalidArgumentException(sprintf('Cube::setBulk() requires equal amount of paths and values: received %d paths but %s values', $count_paths, $count_values));
        }

        if (0 === $count_values) {
            throw new \InvalidArgumentException(sprintf('Cube::setBulk() requires non-zero amount of paths and values: received %d paths and %s values', $count_paths, $count_values));
        }

        $request ??= new ApiCellReplaceBulk();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $request->paths = \implode(':', \array_map([$this, 'buildPathFromElements'], $dims_multi));
        $request->values = \implode(':', \array_map(static function (?string $v): string {
            return Util::strputcsv([($v ?? '')]);
        }, $values));

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param mixed                     $value
     * @param string[]                  $dims
     * @param null|ApiCellReplace       $request
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setValue($value, array $dims, ?ApiCellReplace $request = null): bool
    {
        $request ??= new ApiCellReplace();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();

        $request->path = $this->buildPathFromElements($dims);
        $request->value = $value;

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param null|bool $ignore_state
     */
    public function startCache(?bool $ignore_state = null): void
    {
        // do not clear cache etc. if state is ignored
        // just switch to cache mode
        $ignore_state = $ignore_state ?? false;
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
     * @param null|ApiCellExport $request
     *
     * @throws \Exception
     *
     * @return bool|resource
     *
     * @example file_put_contents('x.csv', $cube->export());
     */
    public function streamExport(?ApiCellExport $request = null)
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
            $result = $this->doRawRequest($request, $coord_path);
            \stream_copy_to_stream($result->__stream__, $ret_stream);
            // \fseek($ret_stream, 0, SEEK_END);
            $coord_path = $result->__lastpath__;
        } while (!$result->__complete__); // start next cycle

        // output complete data set as stream resource
        \rewind($ret_stream);

        return $ret_stream;
    }

    /**
     * @return int
     */
    public function getDimensionCount(): int
    {
        return (int) $this->metaInfo[2];
    }

    /**
     * @param null|ApiCellExport       $request
     * @param null|string              $coord_path
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return \stdClass
     */
    protected function doRawRequest(?ApiCellExport $request = null, ?string $coord_path = null): object
    {
        // init the return stream with 10MB in memory size
        // if exceeds 10MB it's swapped into file on disk
        $ret_stream = \fopen('php://temp/maxmemory:10485760', 'wb+');

        if (false === $ret_stream) {
            throw new \ErrorException('failed to open temp stream');
        }

        $request ??= new ApiCellExport();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->cube = $this->getOlapObjectId();
        $request->blocksize ??= 10000;

        $request->area ??= \implode(',', \array_fill(0, \count($this->listDimensions()), '*'));

        $request->use_rules ??= false;
        $request->base_only ??= true;
        $request->skip_empty ??= 1;
        $request->type ??= 0;
        $request->show_rule ??= false;

        // use the path as offset only for
        // subsequent runs not the first run
        $request->path ??= $coord_path;

        // make the API call and fetch the response as stream resource
        $stream = $this->getConnection()->requestRaw($request->url(), $request->asArray());

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

        // handle case where progress processing failed in CSV
        if (!isset($progress[1])) {
            $progress = [1, 1];
        }

        if (null !== $fore_last_row && $progress[0] !== $progress[1]) {
            // take path as offset for next cycle from the forelast row
            $coord_path = \str_getcsv($fore_last_row ?? '', ';', '"', '"')[3];
        }

        // debug / log progress
        if ($this->isDebugMode()) {
            \file_put_contents('php://stderr', \sprintf("progress: %0.1f%%\n", ($progress[0] / $progress[1]) * 100));
        }

        // output complete data set as stream resource
        \rewind($ret_stream);

        $ret_obj = new class() {
            public $__stream__;
            public ?string $__lastpath__ = null;
            public float $__progress__;
            public bool $__complete__;

            public function __destruct()
            {
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
     * @param string[] $dim_element_coordinates
     * @param bool|null $use_keys
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function buildPathFromElements(array $dim_element_coordinates, ?bool $use_keys = null): string
    {
        $use_keys = $use_keys ?? false;

        $dimensions = $this->listDimensions();

        // use given keys to build path instead of relying on correct element order
        if ($use_keys) {
            $return = [];
            foreach ($dimensions as $dim_index => $dim_id) {
                $dimension = $this->getDatabase()->getDimensionById((int) $dim_id);
                $element = $dim_element_coordinates[$dimension->getName()] ?? null;
                if (null === $element) {
                    throw new \InvalidArgumentException('element for dimension '.$dimension->getName().' missing in parameters');
                }
                $return[] = $dimension->getElementIdFromName((string) $element);
            }

            return \implode(',', $return);
        }

        // relying on elements in correct cube dimension order
        $return = [];
        foreach ($dim_element_coordinates as $dim_index => $elem_name) {
            $dimension = $this->getDatabase()->getDimensionById($dimensions[$dim_index]);
            $return[] = $dimension->getElementIdFromName((string) $elem_name);
        }

        return \implode(',', $return);
    }
}
