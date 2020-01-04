<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/dimension/dfilter.
 *
 * Filters dimension elements.
 */
class ApiDimensionDfilterParams extends RequestParams
{
    /**
     * Identifier of the database.
     *
     * Jedox-Doc type: identifier
     */
    public int $database;

    /**
     * Name of the database. Used only if database parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_database = null;

    /**
     * Identifier of the dimension.
     *
     * Jedox-Doc type: identifier
     */
    public int $dimension;

    /**
     * Name of the dimension. Used only if dimension parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_dimension = null;

    /**
     * Identifier of the cube.
     *
     * Jedox-Doc type: identifier
     */
    public int $cube;

    /**
     * Name of the cube. Used only if cube parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_cube = null;

    /**
     * Comma separated list of element identifiers list. Each element identifiers
     * list is colon separated. The area is the cartesian product. Elements for
     * dimension specified is initial subset that will be filtered.
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * DATA_MIN = 1, DATA_MAX = 2, DATA_SUM = 4, DATA_AVERAGE = 8, DATA_ANY = 16,
     * DATA_ALL = 32, DATA_STRING = 64, ONLY_CONSOLIDATED = 128, ONLY_LEAVES =
     * 256, UPPER_PERCENTAGE = 512, LOWER_PERCENTAGE = 1024, MID_PERCENTAGE =
     * 2048, TOP = 4096, NORULES = 8192.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    /**
     * Condition on the value of numeric or string cells (default is no
     * condition). A condition starts with >, >=, <, <=, ==, or != and is followed
     * by a double or a string. Two condition can be combined by and, or, xor. If
     * you specify a string value, the value has to be csv encoded. Do not forget
     * to URL encode the complete condition string.
     *
     * Jedox-Doc type: string
     */
    public ?string $condition = null;

    /**
     * Values for Top, Upper % and Lower % in this order.
     *
     * Jedox-Doc type: double
     */
    public ?float $values = null;

    /**
     * If 1 then children and parents list are shortened to intervals (1-10) and
     * weights are shortened to weight:count.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $squash_list = null;
}
