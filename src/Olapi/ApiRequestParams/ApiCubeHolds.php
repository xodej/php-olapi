<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/cube/holds.
 *
 * Lists holds of a cube
 */
class ApiCubeHolds extends ApiAbstractRequest
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
     * Comma separated list of element identifiers (default is an empty list).
     *
     * Jedox-Doc type: path
     */
    public ?string $path = null;

    /**
     * Comma separated list of element names. Used only if path parameter is
     * omitted (default is an empty list).
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path = null;

    /**
     * Maximal number of listed holds (default is 1000).
     *
     * Jedox-Doc type: integer
     */
    public ?int $blocksize = null;

    /**
     * Hold identifier. List of holds starts with the next hold (default is to
     * start with the first hold).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $hold = null;

    public function url(): ?string
    {
        return '/cube/holds';
    }
}
