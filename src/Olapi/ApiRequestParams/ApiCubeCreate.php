<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/cube/create.
 *
 * Creates a cube
 */
class ApiCubeCreate extends ApiAbstractRequest
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
     * Optional: Identifier of the cube. If used, current cube is transferred to
     * the new dimension structure. If also new_name is specified, transferred cube
     * is create with the new name and old cube is kept as legacy.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $cube = null;

    /**
     * Optional: Name of the cube. Used only if cube parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_cube = null;

    /**
     * Name of the new cube.
     *
     * Jedox-Doc type: string
     */
    public ?string $new_name = null;

    /**
     * Comma separated list of dimension identifiers.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $dimensions = null;

    /**
     * Comma separated list of dimension names. Used only if dimensions parameter
     * is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_dimensions = null;

    /**
     * Type of the dimension (0=normal (default), 3=user info).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $type = null;

    public function url(): ?string
    {
        return '/cube/create';
    }
}
