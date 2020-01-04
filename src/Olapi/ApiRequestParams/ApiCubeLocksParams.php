<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cube/locks.
 *
 * Lists the locked cube areas
 */
class ApiCubeLocksParams extends RequestParams
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
     * separated by colons. The area is the cartesian product. (optional).
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * Comma separated list of element name lists. Name lists are separated by
     * colons. The area is the cartesian product. Used only if area parameter is
     * omitted. (optional).
     *
     * Jedox-Doc type: narea
     */
    public ?string $name_area = null;

    /**
     * The name of the user. (optional).
     *
     * Jedox-Doc type: string
     */
    public ?string $user = null;
}
