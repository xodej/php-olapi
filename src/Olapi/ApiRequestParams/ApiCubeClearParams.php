<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cube/clear.
 *
 * Clears a cube
 */
class ApiCubeClearParams extends RequestParams
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
     * Comma separated list of element identifier lists. Identifier lists are
     * separated by colons. The area is the cartesian product.
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * Comma separated list of element name lists. Name lists are separated by
     * colons. The area is the cartesian product. Used only if area parameter is
     * omitted.
     *
     * Jedox-Doc type: narea
     */
    public ?string $name_area = null;

    /**
     * (Optional) If complete is "1" then the whole cube - regardless of the
     * specified area - will be cleared. It is not necessary to even specify the
     * parameter "area" in this case. Default is to use "area".
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;
}
