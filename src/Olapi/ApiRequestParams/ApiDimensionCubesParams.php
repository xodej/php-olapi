<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/dimension/cubes.
 *
 * Returns the list of cubes using a dimension
 */
class ApiDimensionCubesParams extends RequestParams
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
     * Show cubes of type normal (0=do not show normal cubes, 1=show (default)).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $show_normal = null;

    /**
     * Show cubes of type system (0=do not show system cubes (default), 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $show_system = null;

    /**
     * Show cubes of type attribute (0=do not show attribute cubes (default),
     * 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $show_attribute = null;

    /**
     * Show cubes of type user info (0=do not show user info cubes (default),
     * 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $show_info = null;

    /**
     * Show cubes of type gpu type (0=do not show gpu type cubes , 1=show
     * (default)).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $show_gputype = null;
}
