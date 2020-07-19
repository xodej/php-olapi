<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/view/calculate.
 *
 * Calculates view
 */
class ApiViewCalculate extends ApiAbstractRequest
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
     * $ separated list of subset definitions.
     *
     * Jedox-Doc type: string
     */
    public ?string $view_subsets = null;

    /**
     * $ separated list of axis definitions. There always has to be 3 axes
     * header$columns$rows. Axis definition can be empty.
     *
     * Jedox-Doc type: string
     */
    public ?string $view_axes = null;

    /**
     * Semicolon separated list of area options. First is colon separated list of
     * cell properties, followed by optional pairs start:limit for each axis and
     * list of additional properties for each axis.
     *
     * Jedox-Doc type: string
     */
    public ?string $view_area = null;

    /**
     * $ separated list of expander definitions.
     *
     * Jedox-Doc type: string
     */
    public ?string $view_expanders = null;

    /**
     * <name>;<, separated list dimensions>;<: separated list of tuples, tuple is
     * , separated list of elements>;<, separated list of calculations>
     *
     * Jedox-Doc type: string
     */
    public ?string $view_tuples = null;

    /**
     * optional colon separated list of comma separated pairs axisid, subset
     * position in the same order as dimensions in cube. Necessary when dimension
     * is in the cube several times.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $paths = null;

    /**
     * optional flags 0x01 - ommit header, 0x02 - ommit rows, 0x04 - ommit
     * columns, 0x08 ommit area, 0x10 return subset names, 0x20 compress result,
     * 0x40 - return only axes sizes, 0x80 - send parent paths, 0x100 - return
     * also [Size] section.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $mode = null;

    /**
     * If 1 then children and parents list are shortened to intervals (1-10) and
     * weights are shortened to weight:count.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $squash_list = null;

    public function url(): ?string
    {
        return '/view/calculate';
    }
}
