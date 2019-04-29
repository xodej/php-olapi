<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

use Xodej\Olapi\Dimension;

/**
 * Class Filter.
 */
abstract class Filter
{
    protected $dimension;
    protected $flags;

    /**
     * Filter constructor.
     *
     * @param Dimension $dimension
     */
    public function __construct(Dimension $dimension)
    {
        $this->dimension = $dimension;
        $this->flags = 0;
    }

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function addFlag(int $flag): bool
    {
        $this->flags |= $flag;

        return true;
    }

    /**
     * @param int $flag
     */
    public function setFlag(int $flag): void
    {
        $this->flags = $flag;
    }

    /**
     * @return null|int
     */
    public function getFlag(): ?int
    {
        return $this->flags;
    }

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function hasFlag(int $flag): bool
    {
        return 0 !== ($this->flags & $flag);
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return \spl_object_hash($this);
    }

    /**
     * @return Dimension
     */
    public function getDimension(): Dimension
    {
        return $this->dimension;
    }

    // @return array
    // abstract public function parse(): array;
}
