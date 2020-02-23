<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/hold/create.
 *
 * Creates a hold
 */
class ApiHoldCreate extends ApiAbstractRequest
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
     * Comma separated list of element identifiers list. Each element identifiers
     * list is colon separated. The area is the cartesian product.
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * Comma separated list of element names list. Each element names list is
     * colon separated. The area is the cartesian product. Used only if area is
     * omitted.
     *
     * Jedox-Doc type: narea
     */
    public ?string $name_area = null;

    public function url(): ?string
    {
        return '/hold/create';
    }
}
