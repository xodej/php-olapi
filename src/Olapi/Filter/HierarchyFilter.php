<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

/**
 * Class HierarchyFilter.
 */
class HierarchyFilter extends Filter
{
    /**
     * Hierarchical filter.
     *
     * Flags
     * 0x0001 choose elements below including element passed to set_bound
     * 0x0002 choose elements below excluding element passed to set_bound
     * 0x0004 remove all consolidated elements from set, show only leaves
     * 0x0008 remove all non-consolidated elements, show aggregations only
     * 0x0040 revolve (repeat) the list, choose all elements on the same level
     * 0x0080 add all elements with the same or a higher level than elemname and repeat the list
     * 0x0100 add all elements with the same or a lower level than elemname and repeat the list
     * 0x0200 choose elements above excluding element passed to set_bound
     * 0x0400 choose elements above including element passed to set_bound
     * 0x0800 simply repeat the list without further filtering
     * 0x1000 use levels relative to bound element, if there is no bound element, return elements without parents
     * 0x2000 choose siblings excluding element passed to set_bound
     * 0x4000 choose siblings including element passed to set_bound
     *
     * 1;<flags>;<indent>;<bound element name>;<level start>;<level end>;<revolve count>;<revolve name>
     */
    public const FILTER_ID = 1;

    public const FLAG_BELOW_INCLUSIVE = 1;
    public const FLAG_BELOW_EXCLUSIVE = 2;
    public const FLAG_HIDE_CONSOLIDATED = 4;
    public const FLAG_HIDE_LEAVES = 8;
    public const FLAG_REVOLVING = 64;
    public const FLAG_REVOLVE_ADD_ABOVE = 128;
    public const FLAG_REVOLVE_ADD_BELOW = 256;
    public const FLAG_ABOVE_EXCLUSIVE = 512;
    public const FLAG_ABOVE_INCLUSIVE = 1024;
    public const FLAG_CYCLIC = 2048;
    public const FLAG_RELATIVE_LEVELS = 4096;
    public const FLAG_SIBLINGS_EXCLUSIVE = 8192;
    public const FLAG_SIBLINGS_INCLUSIVE = 16384;

    protected ?string $bound = null;
    protected ?bool $level = null;
    protected ?int $levelStart = null;
    protected ?int $levelEnd = null;
    protected ?bool $revolve = null;
    protected ?string $revolveElement = null;
    protected ?int $revolveCount = null;
    protected ?int $indent = null;

    /**
     * @param string $element_name
     * @return $this
     */
    public function setBoundElement(string $element_name): self
    {
        if (!$this->getDimension()->hasElementByName($element_name)) {
            throw new \InvalidArgumentException('unknown element given as argument');
        }

        $this->bound = $element_name;
        return $this;
    }

    /**
     * @throws \DomainException
     *
     * @return string
     */
    public function getBoundElement(): string
    {
        if (null === $this->bound) {
            throw new \DomainException('no bound element found');
        }

        return $this->bound;
    }

    /**
     * @param int $level_start
     * @return $this
     */
    public function setLevelStart(int $level_start): self
    {
        $this->levelStart = $level_start;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevelStart(): int
    {
        return $this->levelStart ?? 1;
    }

    /**
     * @param int $level_end
     * @return $this
     */
    public function setLevelEnd(int $level_end): self
    {
        $this->levelEnd = $level_end;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevelEnd(): int
    {
        return $this->levelEnd ?? $this->getDimension()->getMaxLevel() + 1;
    }

    /**
     * @param int $revolve_count
     * @return $this
     */
    public function setRevolveCount(int $revolve_count): self
    {
        if ($revolve_count < 1) {
            throw new \InvalidArgumentException('argument must be integer');
        }

        $this->revolveCount = $revolve_count;
        return $this;
    }

    /**
     * @return int
     */
    public function getRevolveCount(): int
    {
        return $this->revolveCount ?? 1;
    }

    /**
     * @param string $revolve_name
     * @return $this
     */
    public function setRevolveName(string $revolve_name): self
    {
        if (!$this->getDimension()->hasElementByName($revolve_name)) {
            throw new \InvalidArgumentException('argument not found in dimension');
        }

        $this->revolveElement = $revolve_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getRevolveName(): string
    {
        return $this->revolveElement ?? '';
    }

    /**
     * @return int
     */
    public function getIndent(): int
    {
        return $this->indent ?? $this->getDimension()->getMaxIndent();
    }

    /**
     * @throws \DomainException
     *
     * @return array
     */
    public function parse(): array
    {
        // no flags set - use default flag
        if (null === $this->getFlag()) {
            $this->addFlag(self::FLAG_BELOW_INCLUSIVE);
        }

        $return = [
            self::FILTER_ID,
            $this->getFlag(),
        ];

        foreach ($this->getCsvArray() as $param) {
            $return[] = $param;
        }

        return $return;
    }

    /**
     * @throws \DomainException
     *
     * @return array
     */
    protected function getCsvArray(): array
    {
        $return = [];

        if ($this->hasFlag(self::FLAG_REVOLVING)) {
            $return[] = '1'; // indent
            $return[] = '';  // bound element name
            $return[] = '';  // level start
            $return[] = '';  // level end
            $return[] = $this->getRevolveCount(); // revolve count
            $return[] = $this->getRevolveName(); // revolve name
            return $return;
        }

        $return[] = $this->getIndent(); // indent
        $return[] = $this->getBoundElement();  // bound element name
        $return[] = $this->getLevelStart();  // level start
        $return[] = $this->getLevelEnd();  // level end
        $return[] = '';  // revolve count
        $return[] = '';  // revolve name

        return $return;
    }
}
