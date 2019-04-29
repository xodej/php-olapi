<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class CubeStore.
 */
class CubeStore extends Store
{
    /**
     * CubeStore constructor.
     *
     * @param array  $input
     * @param int    $flags
     * @param string $iterator_class
     *
     * @throws \Exception
     */
    public function __construct($input = [], int $flags = 0, string $iterator_class = 'ArrayIterator')
    {
        if (!empty($input)) {
            throw new \InvalidArgumentException('non-empty array not allowed');
        }
        parent::__construct($input, $flags, $iterator_class);
    }

    /**
     * @param mixed $index
     *
     * @return Cube
     */
    public function offsetGet($index): Cube
    {
        return parent::offsetGet($index);
    }

    /**
     * @param Cube $value
     *
     * @throws \Exception
     */
    public function append($value): void
    {
        if (!($value instanceof Cube)) {
            throw new \InvalidArgumentException('parameter type Cube expected');
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
        if (!($newval instanceof Cube)) {
            throw new \InvalidArgumentException('parameter type Cube expected');
        }
        parent::offsetSet($index, $newval);
    }

    /**
     * @param CubeStore $input
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function exchangeArray($input): array
    {
        if (!($input instanceof static)) {
            throw new \InvalidArgumentException('parameter type CubeStore expected');
        }

        return parent::exchangeArray($input->getArrayCopy());
    }

    /**
     * @return Cube[]
     */
    public function getArrayCopy(): array
    {
        return parent::getArrayCopy();
    }
}
