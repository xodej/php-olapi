<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/cube/lock.
 *
 * locks a cube area
 */
class ApiCubeLock extends ApiAbstractRequest
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
     * If 1 and no area is specified, special lock for whole cube is applied. No
     * other user can read the cube and it's not sorted and markers are not
     * generated until commit.
     *
     * Jedox-Doc type: integer
     */
    public ?bool $complete = null;

    public function url(): ?string
    {
        return '/cube/lock';
    }
}
