<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

/**
 * Class GeneralFilter.
 */
class GeneralFilter extends Filter
{
    public const FLAG_LEVELTYPE_INDENT = 1;
    public const FLAG_LEVELTYPE_LEVEL = 2;
    public const FLAG_LEVELTYPE_DEPTH = 3;
    public const FLAG_LEVELTYPE_SUBSET = 4;
}
