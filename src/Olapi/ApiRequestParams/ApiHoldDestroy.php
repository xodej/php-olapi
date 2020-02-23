<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/hold/destroy.
 *
 * Removes one or all holds of a cube
 */
class ApiHoldDestroy extends ApiAbstractRequest
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
     * If 1 then all holds of the cube are removed (default is 0).
     *
     * Jedox-Doc type: integer
     */
    public ?bool $complete = null;

    /**
     * Identifier of the hold. Used only if complete=0.
     *
     * Jedox-Doc type: identifier
     */
    public ?string $hold = null;

    public function url(): ?string
    {
        return '/hold/destroy';
    }
}
