<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/cube/rollback.
 *
 * rollback changes of a locked cube area
 */
class ApiCubeRollback extends ApiAbstractRequest
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
     * Indentifier of the locked area.
     *
     * Jedox-Doc type: integer
     */
    public ?int $lock = null;

    /**
     * number of steps to rollback (an empty value means undo all steps and remove
     * lock).
     *
     * Jedox-Doc type: integer
     */
    public ?int $steps = null;

    public function url(): ?string
    {
        return '/cube/rollback';
    }
}
