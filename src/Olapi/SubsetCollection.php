<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class SubsetCollection.
 */
class SubsetCollection extends GenericCollection
{
    /**
     * SubsetCollection constructor.
     *
     * @param array       $input
     * @param null|int    $flags
     * @param null|string $iterator_class
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
     * @return Subset
     */
    public function offsetGet($index): Subset
    {
        return parent::offsetGet($index);
    }

    /**
     * @param Subset $value
     *
     * @throws \Exception
     */
    public function append($value): void
    {
        if (!($value instanceof Subset)) {
            throw new \InvalidArgumentException('parameter type Subet expected');
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
        if (!($newval instanceof Subset)) {
            throw new \InvalidArgumentException('parameter type Subset expected');
        }
        parent::offsetSet($index, $newval);
    }

    /**
     * @param SubsetCollection $input
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function exchangeArray($input): array
    {
        if (!($input instanceof static)) {
            throw new \InvalidArgumentException('parameter type SubsetCollection expected');
        }

        return parent::exchangeArray($input->getArrayCopy());
    }

    /**
     * @return Subset[]
     */
    public function getArrayCopy(): array
    {
        return parent::getArrayCopy();
    }
}
