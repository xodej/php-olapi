<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/rule/info.
 *
 * Returns information about a defined rule.
 */
class ApiRuleInfoParams extends RequestParams
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
     * Comma separated list of rule identifiers to be shown.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $rule = null;

    /**
     * Use identifier in textual representation of the ACTIVE rule in the result.
     * The definition can use name or identifier independent of the parameter.
     * Default is 0.
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
