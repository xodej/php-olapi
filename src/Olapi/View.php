<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class View.
 */
class View implements IBase
{
    public const API_VIEW_CALCULATE = '/view/calculate';

    public const FLAG_SKIP_HEADER = 1;
    public const FLAG_SKIP_ROWS = 2;
    public const FLAG_SKIP_COLUMNS = 4;
    public const FLAG_SKIP_AREA = 8;
    public const FLAG_SUBSET_NAMES = 16;
    public const FLAG_COMPRESS = 32;
    public const FLAG_AXIS_SIZE = 64;
    public const FLAG_VIEW_PARENT_PATHS = 128;
    public const FLAG_AXIS_FULL_SIZE = 256;

    /**
     * @var SubsetStore
     */
    protected $subsets;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Cube
     */
    protected $cube;

    /**
     * @var string
     */
    protected $name;

    /**
     * View constructor.
     *
     * @param Database $db
     *
     * @throws \Exception
     */
    public function __construct(Database $db)
    {
        $this->subsets = new SubsetStore();
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
        return \random_int(1000000, 9999999);
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
     * @return Store|resource
     */
    public function fire(bool $debug = null)
    {
        $debug = $debug ?? false;

        if ($debug) {
            \file_put_contents('php://stderr', \implode(';', \array_map(static function (Subset $v) {
                return (string) $v;
            }, $this->subsets->getArrayCopy())).PHP_EOL);
        }

        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'view_subsets' => \implode(';', \array_map(static function (Subset $v) {
                    return (string) $v;
                }, $this->subsets->getArrayCopy())),
                'view_axes' => \implode(';', \array_map(static function (Subset $v) {
                    return '$'.\implode(';', [$v->getName(), '', '', 0]).'$';
                }, $this->subsets->getArrayCopy())),
                // @todo View::fire()
                // 'view_area' => '',
                // 'view_expanders' => '',
                'mode' => self::FLAG_COMPRESS,
            ],
        ];

        if ($debug) {
            return $this->getConnection()->requestRaw(self::API_VIEW_CALCULATE, $params);
        }

        $response = $this->getConnection()->request(self::API_VIEW_CALCULATE, $params);

        $return = new Store();
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
     *
     * @return void
     */
    public function reload(): void
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
