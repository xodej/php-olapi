<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class RequestParams.
 */
abstract class RequestParams
{
    /**
     * Returns array from class properties for http request call.
     *
     * @return array<string, array<string, mixed>>
     */
    public function asArray(): array
    {
        $return = [];

        // iterate over all defined properties (class definition)
        foreach (\get_class_vars(\get_class($this)) as $key => $unused_val) {
            $key = (string) $key;
            if (null !== $this->{$key} && !isset($return['query'][$key])) {
                $value = $this->{$key};

                // for boolean values use numeric representation
                if (\is_bool($value)) {
                    $value = (int) $value;
                }

                $return['query'][$key] = $value;
            }
        }

        return $return;
    }
}
