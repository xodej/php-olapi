<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class CubeCollection.
 */
class CubeCollection extends GenericCollection
{
    /**
     * CubeCollection constructor.
     *
     * @param array       $input
     * @param null|int    $flags
     * @param null|string $iterator_class
     *
     * @throws \Exception
     */
    public function __construct(array $input = [], ?int $flags = null, ?string $iterator_class = null)
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
     * @param CubeCollection $input
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function exchangeArray($input): array
    {
        if (!($input instanceof static)) {
            throw new \InvalidArgumentException('parameter type CubeCollection expected');
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
