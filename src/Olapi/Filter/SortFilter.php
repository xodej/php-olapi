<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

use http\Exception\InvalidArgumentException;

/**
 * Class SortFilter.
 */
class SortFilter extends Filter
{
    /**
     *  Sorting filter.
     *
     *  Flags
     *  0x000001 sort filtered elements according to name (default, not necessary to pass)
     *  0x000002 sort filtered elements according to value computed by data-filter !! MIGHT BE STRING DATA !!
     *  0x000004 sort according to an attribute (to be set separately)
     *  0x000008 sort according to aliases as determined by alias filter
     *  0x000010 show parents below their children
     *  0x000020 do not sort consolidated elements
     *  0x000040 show duplicates that have different parents
     *  0x000080 show whole hierarchy
     *  0x000100 position --default
     *  0x000200 reverse the sorting
     *  0x000400 sort on the level of level-element
     *  0x000800 do NOT build a tree -- default
     *  0x001000 build a tree, but do not follow the children-list of an element that has been filtered out before
     *  0x002000 only sort consolidations
     *  0x004000 sort all levels except one
     *  0x008000 show duplicates, default value is 0 (flag inactive - duplicates are hidden)
     *  0x010000 this will completely reverse the ordering
     *  0x020000 limit number of elements returned
     *  0x040000 sort by consolidation order
     *  0x080000 return also id path
     *  0x100000 completely skip sorting (when this option is used subset can't be used as source for other subset)
     *  0x200000 don't return path (default for subset mode)
     *  0x400000 return also name path (default for view mode)
     *  0x800000 Inherit sorting filter from parent subset and apply limit if specified
     *
     * 5;<flags>;<indent>;<attribute name>;<level>;<limit_count>;<limit start>
     */
    public const FILTER_ID = 5;

    public const FLAG_TEXT = 1;
    public const FLAG_NUMERIC = 2;
    public const FLAG_USE_ATTRIBUTE = 4;
    public const FLAG_USE_ALIAS = 8;
    public const FLAG_REVERSE_ORDER = 16;
    public const FLAG_LEAVES_ONLY = 32;
    public const FLAG_SHOW_DUPLICATE_PARENT = 64;
    public const FLAG_WHOLE = 128;
    public const FLAG_POSITION = 256;
    public const FLAG_REVERSE_TOTAL = 512;
    public const FLAG_SORT_ONE_LEVEL = 1024;
    public const FLAG_FLAT_HIERARCHY = 2048;
    public const FLAG_NO_CHILDREN = 4096;
    public const FLAG_CONSOLIDATED_ONLY = 8192;
    public const FLAG_SORT_NOT_ONE_LEVEL = 16384;
    public const FLAG_SHOW_DUPLICATES = 32768;
    public const FLAG_REVERSE_TOTAL_EX = 65536;
    public const FLAG_LIMIT = 131072;
    public const FLAG_CONSOLIDATED_ORDER = 262144;
    public const FLAG_ELEMENT_PATH = 524288;
    public const FLAG_NO_SORT = 1048576;
    public const FLAG_NO_PATH = 2097152;
    public const FLAG_NAME_PATH = 4194304;
    public const FLAG_INHERIT = 8388608;

    /**
     * @var string
     */
    protected $attribute;

    /**
     * @var int
     */
    protected $level;

    /**
     * @var int
     */
    protected $levelType;

    /**
     * @var int
     */
    protected $limitCount;

    /**
     * @var int
     */
    protected $limitStart;

    /**
     * The allowed values are 1 (equivalently: empty), 2, 3 and 4 (4 as of Version 6.0 SR1). The argument alters the numbering of the hierarchy level, which in turn affects the subset, where the hierarchy level is specified in the filter.
     * Indent 1: The elements of the highest hierarchy level get the number 1, the second highest level gets number 2, and so on.
     * Indent 2: The elements in the lowest hierarchy level (base elements) get the number 0. The number is incremented by 1 for every step up in the hierarchy.
     * Indent 3: The elements of the highest hierarchy level get the number 0, the second highest level gets the number 1, and so on.
     * Indent 4: This number will dynamically adjust the indent of elements in the subset based on the overall subset results. All elements for which no parent element is found will get indent  number 1,
     * regardless of their position level in the dimension. Elements for which at least one parent element is found in the subset will get an indent number calculated from their parent’s indent in the result (incremented by 1).
     *
     * @param int $levelType
     */
    public function setLevelType(int $levelType): void
    {
        if ($levelType < 1 || $levelType > 4) {
            throw new \InvalidArgumentException('SortFilter::setLevelType() only allows values between 1 and 4, got ' . $levelType);
        }
        $this->levelType = $levelType;
    }

    /**
     * @return int
     */
    public function getLevelType(): int
    {
        if (null === $this->levelType) {
            return 1;
        }

        return $this->levelType;
    }

    /**
     * @param string $attributeName
     */
    public function useAttribute(string $attributeName): void
    {
        $this->attribute = $attributeName;
        $this->addFlag(self::FLAG_USE_ATTRIBUTE);
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limitCount = $limit;
    }

    /**
     * @param int $limitStart
     */
    public function setLimitStart(int $limitStart): void
    {
        $this->limitStart = $limitStart;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $level_min = 0;
        $level_max = 0;

        if (GeneralFilter::FLAG_LEVELTYPE_INDENT === $this->levelType) {
            $level_max = $this->dimension->getMaxIndent();
            $level_min = 1;
        }

        if (GeneralFilter::FLAG_LEVELTYPE_LEVEL === $this->levelType) {
            $level_max = $this->dimension->getMaxLevel();
        }

        if (GeneralFilter::FLAG_LEVELTYPE_DEPTH === $this->levelType) {
            $level_max = $this->dimension->getMaxDepth();
        }

        if ($level > $level_max) {
            $level = $level_max;
        }

        if ($level < $level_min) {
            $level = $level_min;
        }

        $this->level = $level;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        if (null === $this->level) {
            $this->setLevel(0);
        }

        return $this->level;
    }

    /**
     * @return array
     */
    public function parse(): array
    {
        // no flags set - use default flag
        if (null === $this->getFlag()) {
            $this->setFlag(self::FLAG_POSITION);
        }

        // build return string
        return [
            self::FILTER_ID, // filter ID
            $this->getFlag(), // flags
            $this->getLevelType(), // indent
            $this->attribute ?? '', // attribute name
            $this->getLevel() ?? '', // level
            $this->limitCount ?? 1000, // limit_count
            $this->limitStart ?? 0, // limit_start
        ];
    }
}
