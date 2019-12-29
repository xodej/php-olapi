<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class GenericCollection.
 */
class GenericCollection extends \ArrayObject
{
    /**
     * @param string        $func
     * @param array<string> $argv
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function __call(string $func, array $argv)
    {
        if (!\is_callable($func) || 0 !== \strpos($func, 'array_')) {
            throw new \BadMethodCallException(__CLASS__.'::'.$func.'()');
        }

        return \call_user_func_array($func, \array_merge([$this->getArrayCopy()], $argv));
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return \spl_object_hash($this);
    }
}
