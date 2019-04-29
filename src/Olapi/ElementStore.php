<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class ElementStore.
 */
class ElementStore extends Store
{
    /**
     * ElementStore constructor.
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
     * @return Element
     */
    public function offsetGet($index): Element
    {
        return parent::offsetGet($index);
    }

    /**
     * @param Element $value
     *
     * @throws \Exception
     */
    public function append($value): void
    {
        if (!($value instanceof Element)) {
            throw new \InvalidArgumentException('parameter type Element expected');
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
        if (!($newval instanceof Element)) {
            throw new \InvalidArgumentException('parameter type Element expected');
        }
        parent::offsetSet($index, $newval);
    }

    /**
     * @param ElementStore $input
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function exchangeArray($input): array
    {
        if (!($input instanceof static)) {
            throw new \InvalidArgumentException('parameter type ElementStore expected');
        }

        return parent::exchangeArray($input->getArrayCopy());
    }

    /**
     * @return Element[]
     */
    public function getArrayCopy(): array
    {
        return parent::getArrayCopy();
    }
}
