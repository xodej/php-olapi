<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class DatabaseStore.
 */
class DatabaseStore extends Store
{
    /**
     * DatabaseStore constructor.
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
     * @return Database
     */
    public function offsetGet($index): Database
    {
        return parent::offsetGet($index);
    }

    /**
     * @param Database $value
     *
     * @throws \Exception
     */
    public function append($value): void
    {
        if (!($value instanceof Database)) {
            throw new \InvalidArgumentException('parameter type Database expected');
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
        if (!($newval instanceof Database)) {
            throw new \InvalidArgumentException('parameter type Database expected');
        }
        parent::offsetSet($index, $newval);
    }

    /**
     * @param DatabaseStore $input
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function exchangeArray($input): array
    {
        if (!($input instanceof static)) {
            throw new \InvalidArgumentException('parameter type DatabaseStore expected');
        }

        return parent::exchangeArray($input->getArrayCopy());
    }

    /**
     * @return Database[]
     */
    public function getArrayCopy(): array
    {
        return parent::getArrayCopy();
    }
}
