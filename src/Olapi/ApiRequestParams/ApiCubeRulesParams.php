<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cube/rules.
 *
 * Lists the rules for a cube
 */
class ApiCubeRulesParams extends RequestParams
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
     * Use identifier in textual representation of the ACTIVE rule. Default is 0.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $use_identifier = null;

    /**
     * If 1 return also IP protection info. Default is 0.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_protection = null;
}
