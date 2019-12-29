<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class ElementCollection.
 */
class ElementCollection extends GenericCollection
{
    /**
     * ElementCollection constructor.
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
     * @param ElementCollection $input
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function exchangeArray($input): array
    {
        if (!($input instanceof static)) {
            throw new \InvalidArgumentException('parameter type ElementCollection expected');
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
