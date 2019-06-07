<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

/**
 * Class PicklistFilter.
 */
class PicklistFilter extends Filter
{
    /**
     * Picklist filter.
     *
     * Flags
     * 0x01 put manually picked elements after the others
     * 0x02 merge manually picked elements with the others
     * 0x04 use the picklist as a filter, not as a union
     * 0x08 default value, put manually picked elements before the others
     * 0x10 similar to SUB, but expected to be only filter in subset
     * 0x20 ignore nonexistent elements
     *
     * 0;<flags>;<: separated list of element names>
     */
    public const FILTER_ID = 0;

    //  0/empty = Insert before the list, 1 = Insert after the list, 2 = insert into the list, 3 = As pre-selection for the subset.

    public const FLAG_INSERT_BACK = 1;
    public const FLAG_MERGE_ELEMENTS = 2;
    public const FLAG_SUB = 4;
    public const FLAG_INSERT_FRONT = 8;
    public const FLAG_DFILTER = 16;
    public const FLAG_IGNORE_MISSING = 32;
    public const FLAG_FRONT_EXCLUSIVE = 64;

    /**
     * @var bool
     */
    protected $manualSubsetSet;

    /**
     * @var string[]
     */
    protected $manualSubset;

    /**
     * @var array
     */
    private $elements;

    /**
     * @param string $element_name
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return bool
     *
     * @see PicklistFilter::addElementByName() alias
     */
    public function addElement(string $element_name): bool
    {
        return $this->addElementByName($element_name);
    }

    /**
     * @param string $element_name
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return bool
     */
    public function addElementByName(string $element_name): bool
    {
        if (!$this->getDimension()->hasElementByName($element_name)) {
            throw new \ErrorException('unknown element name '.$element_name.' in dimension '.$this->getDimension()->getName());
        }

        $this->elements[] = $this->getDimension()->getElementByName($element_name);

        return true;
    }

    /**
     * @param int $element_id
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return bool
     */
    public function addElementById(int $element_id): bool
    {
        if (!$this->getDimension()->hasElementById($element_id)) {
            throw new \ErrorException('unknown element ID '.$element_id.' in dimension '.$this->getDimension()->getName());
        }

        $this->elements[] = $this->getDimension()->getElementById($element_id);

        return true;
    }

    /**
     * @return array
     */
    public function parse(): array
    {
        // no flags set - use default flag
        if (null === $this->getFlag()) {
            $this->setFlag(self::FLAG_INSERT_FRONT);
        }

        $element_list = [];
        foreach ($this->elements as $element) {
            $element_list[] = $element->getName();
        }
        $element_list = \implode(':', $element_list);

        return [
            self::FILTER_ID,
            $this->getFlag(),
            $element_list,
        ];
    }
}
