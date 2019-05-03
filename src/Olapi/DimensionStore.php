<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class DimensionStore.
 */
class DimensionStore extends Store
{
    /**
     * DimensionStore constructor.
     *
     * @param array       $input
     * @param int|null    $flags
     * @param string|null $iterator_class
     *
     * @throws \Exception
     */
    public function __construct($input = [], ?int $flags = null, ?string $iterator_class = null)
    {
        $flags = $flags ?? 0;
        $iterator_class = $iterator_class ?? 'ArrayIterator';

        if (!empty($input)) {
            throw new \InvalidArgumentException('non-empty array not allowed');
        }
        parent::__construct($input, $flags, $iterator_class);
    }

    /**
     * @param mixed $index
     *
     * @return Dimension
     */
    public function offsetGet($index): Dimension
    {
        return parent::offsetGet($index);
    }

    /**
     * @param Dimension $value
     *
     * @throws \Exception
     */
    public function append($value): void
    {
        if (!($value instanceof Dimension)) {
            throw new \InvalidArgumentException('parameter type Dimension expected');
        }
        parent::append($value);
    }

    /**
     * @param mixed $index
     * @param mixed $newval
     *
     * @throws \Exception
     */
    public function offsetSet($index, $newval): void
    {
        if (!($newval instanceof Dimension)) {
            throw new \InvalidArgumentException('parameter type Dimension expected');
        }
        parent::offsetSet($index, $newval);
    }

    /**
     * @param DimensionStore $input
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function exchangeArray($input): array
    {
        if (!($input instanceof static)) {
            throw new \InvalidArgumentException('parameter type DimensionStore expected');
        }

        return parent::exchangeArray($input->getArrayCopy());
    }

    /**
     * @return Dimension[]
     */
    public function getArrayCopy(): array
    {
        return parent::getArrayCopy();
    }
}
