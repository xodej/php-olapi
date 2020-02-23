<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use Xodej\Olapi\ApiRequestParams\ApiViewCalculate;

/**
 * Class View.
 */
class View implements IBase
{
    public const FLAG_SKIP_HEADER = 1;
    public const FLAG_SKIP_ROWS = 2;
    public const FLAG_SKIP_COLUMNS = 4;
    public const FLAG_SKIP_AREA = 8;
    public const FLAG_SUBSET_NAMES = 16;
    public const FLAG_COMPRESS = 32;
    public const FLAG_AXIS_SIZE = 64;
    public const FLAG_VIEW_PARENT_PATHS = 128;
    public const FLAG_AXIS_FULL_SIZE = 256;

    protected SubsetCollection $subsets;
    protected Database $database;
    protected ?Cube $cube = null;
    protected string $name;

    /**
     * View constructor.
     *
     * @param Database $db
     *
     * @throws \Exception
     */
    public function __construct(Database $db)
    {
        $this->subsets = new SubsetCollection();
        $this->database = $db;
        $this->name = "\t".\sha1(\random_bytes(160));
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function getOlapObjectId(): int
    {
        return \random_int(1_000_000, 9_999_999);
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
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->getDatabase()->getConnection();
    }

    /**
     * @param Subset $subset
     */
    public function addSubset(Subset $subset): void
    {
        $this->subsets[$subset->getHash()] = $subset;
    }

    /**
     * @param Cube $cube
     */
    public function setCube(Cube $cube): void
    {
        $this->cube = $cube;
    }

    /**
     * @param null|bool $debug
     *
     * @throws \Exception
     *
     * @return GenericCollection
     */
    public function fire(bool $debug = null): GenericCollection
    {
        $debug = $debug ?? false;

        if ($debug) {
            \file_put_contents('php://stderr', \implode(';', \array_map(static function (Subset $v) {
                return (string) $v;
            }, $this->subsets->getArrayCopy())).PHP_EOL);
        }

        $request = new ApiViewCalculate();
        $request->database = $this->getDatabase()->getOlapObjectId();

        $request->view_subsets = \implode(';', \array_map(static function (Subset $v) {
            return (string) $v;
        }, $this->subsets->getArrayCopy()));

        $request->view_axes = \implode(';', \array_map(static function (Subset $v) {
            return '$'.\implode(';', [$v->getName(), '', '', 0]).'$';
        }, $this->subsets->getArrayCopy()));

        $request->mode = self::FLAG_COMPRESS;

        $response = $this->getConnection()->request($request);

        if ($debug) {
            return $response;
        }

        $return = new GenericCollection();
        $flag_switch = false;
        foreach ($response as $entry) {
            /* @noinspection PhpAssignmentInConditionInspection */ // @todo inspection
            if ($flag_switch = !$flag_switch) {
                // ignore second row of response: same as 0 === $index % 2
                continue;
            }
            $return[] = $entry;
        }

        return $return;
    }

    /**
     * @throws \DomainException
     */
    public function reload(): self
    {
        throw new \DomainException('View::reload() not implemented');
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
}
