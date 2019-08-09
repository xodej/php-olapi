<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

use Xodej\Olapi\Area;
use Xodej\Olapi\Cube;
use Xodej\Olapi\Dimension;

// @todo class DataFilter - features missing

/**
 * Class DataFilter.
 */
class DataFilter extends Filter
{
    /*public
     * Data filter
     *
     * Flags
     * 0x0001 use min operator on cell values
     * 0x0002 use max operator on cell values
     * 0x0004 use sum operator on cell values
     * 0x0008 use average operator on cell values
     * 0x0010 conditions must be true for at least one value
     * 0x0020 conditions must be true for all values
     * 0x0040 interpret data as strings
     * 0x0080 compute data only for consolidations (don't filter leaves)
     * 0x0100 compute data only for leaves (don't filter consolidations)
     * 0x0200 sort elements from highest to lowest and choose those that contribute the first p1% (set percentage method)
     * 0x0400 sort elements from lowest to highest and choose those that contribute the first p1% (set percentage method)
     * 0x0800 sort elements from highest to lowest and choose those that contribute p2% after removing the first elements that make up p1%
     * 0x1000 pick only the top-x elements. x set by set_top
     * 0x2000 do not use rules when extracting cell values
     * 0x4000 use AND to previous dfilter instead of OR
     *
     * 4;<flags>;<cube name>;<0|1 use strings>;<compare1 operator>;<compare1 value>;<compare2 operator>;<compare2 value>;<coordinates count>;<; separated list of : separated lists of element names>;<upper percentage>;<lower percentage>;<top>
     * Example:
     * 4;4;"Sales";0;">";10;;0;6;;"Germany:France";"Jan";"2007";"Actual";"Units";;;0
     */
    public const FILTER_ID = 4;

    public const FLAG_MIN = 1;
    public const FLAG_MAX = 2;
    public const FLAG_SUM = 4;
    public const FLAG_AVERAGE = 8;
    public const FLAG_ANY = 16;
    public const FLAG_ALL = 32;
    public const FLAG_STRING = 64;
    public const FLAG_ONLY_CONSOLIDATED = 128;
    public const FLAG_ONLY_LEAVES = 256;
    public const FLAG_UPPER_PERCENTAGE = 512;
    public const FLAG_LOWER_PERCENTAGE = 1024;
    public const FLAG_MID_PERCENTAGE = 2048;
    public const FLAG_TOP = 4096;
    public const FLAG_NO_RULES = 8192;
    public const FLAG_USE_AND = 16384;

    /**
     * @var null|Cube
     */
    protected $cube;

    /**
     * @var DataComparison[]
     */
    protected $cmps;

    /**
     * @var bool
     */
    protected $coordsSet;

    /**
     * @var string[]
     */
    protected $coords;

    /**
     * @var bool
     */
    protected $upperPercentageSet;

    /**
     * @var bool
     */
    protected $lowerPercentageSet;

    /**
     * @var float
     */
    protected $upperPercentage;

    /**
     * @var float
     */
    protected $lowerPercentage;

    /**
     * @var int
     */
    protected $topmost;

    /**
     * @var int
     */
    protected $useStrings;

    /**
     * @var null|Area
     */
    protected $subcube;

    /**
     * DataFilter constructor.
     *
     * @param Dimension $dimension
     */
    public function __construct(Dimension $dimension)
    {
        parent::__construct($dimension);
        $this->cmps = [];
    }

    /**
     * @param Cube $cube
     */
    public function setCube(Cube $cube): void
    {
        $this->cube = $cube;
    }

    /**
     * @param null|bool $use_strings
     */
    public function useStrings(?bool $use_strings = null): void
    {
        $use_strings = $use_strings ?? false;
        $this->useStrings = (int) $use_strings;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function parse(): array
    {
        if (null === $this->cube) {
            throw new \DomainException('cube required for DataFilter');
        }

        // no flags set - use default flag
        if (null === $this->getFlag()) {
            $this->addFlag(self::FLAG_SUM);
        }

        $return = [
            self::FILTER_ID, // filter ID
            $this->getFlag(), // flags
            $this->cube->getName(), // cube name
            (int) ($this->useStrings ?? false), // 0|1 use strings
            isset($this->cmps[0]) ? $this->cmps[0]->getOperator() : '', // compare1 operator
            isset($this->cmps[0]) ? $this->cmps[0]->getParameter() : 0, // compare1 value
            isset($this->cmps[1]) ? $this->cmps[1]->getOperator() : '', // compare2 operator
            isset($this->cmps[1]) ? $this->cmps[1]->getParameter() : 0, // compare2 value
        ];

        // add the amount of the following dimension columns
        $return[] = $this->cube->getDimensionCount();  // coordinates count

        if (null === $this->subcube) {
            // add one statement per Dimension
            foreach ($this->cube->getDimensions() as $dimension) {
                // @todo filter for elements in dimensions --> like Area
                $return[] = ''; // separated list of : separated lists of element names
            }
        }

        if (null !== $this->subcube) {
            foreach ($this->subcube->getAreaAsArray() as $dim_coord) {
                $return[] = $dim_coord;
            }
        }

        $return[] = $this->lowerPercentage ?? ''; // upper percentage
        $return[] = $this->upperPercentage ?? ''; // lower percentage
        $return[] = $this->topmost ?? -1; // top

        return $return;
    }

    /**
     * @param int          $operator $operator DataComparison::OPERATOR_XX constants
     * @param float|string $value
     *
     * @throws \Exception
     */
    public function addComparison(int $operator, $value): void
    {
        if (2 === \count($this->cmps)) {
            throw new \InvalidArgumentException('only two comparisons are supported by Jedox');
        }

        if (!\is_numeric($value)) {
            $this->useStrings(true);
        }

        $this->cmps[] = new DataComparison($operator, $value);
    }

    public function reset(): void
    {
        $this->cmps = [];
        $this->subcube = null;
    }

    /**
     * @param Area $area
     */
    public function setArea(Area $area): void
    {
        $this->subcube = $area;
    }
}
