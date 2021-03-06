<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/rule/destroy.
 *
 * Removes an enterprise rule from a cube
 */
class ApiRuleDestroy extends ApiAbstractRequest
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
     * Comma separated list of rule identifiers to be deleted.
     *
     * Jedox-Doc type: identifier
     */
    public ?string $rule = null;

    public function url(): ?string
    {
        return '/rule/destroy';
    }
}
