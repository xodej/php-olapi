<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/rule/create.
 *
 * Creates a new enterprise rule for a cube
 */
class ApiRuleCreateParams extends RequestParams
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
     * Urlencoded enterprise rule.
     *
     * Jedox-Doc type: string
     */
    public string $definition;

    /**
     * create active rule with "1" (default) or a not active rule with "0".
     *
     * Jedox-Doc type: integer
     */
    public ?int $activate = null;

    /**
     * Urlencoded external identifier.
     *
     * Jedox-Doc type: string
     */
    public ?string $external_identifier = null;

    /**
     * Urlencoded comment.
     *
     * Jedox-Doc type: string
     */
    public ?string $comment = null;

    /**
     * Use identifier in textual representation of the ACTIVE rule in the result.
     * The definition can use name or identifier independent of the parameter.
     * Default is 0.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $use_identifier = null;

    /**
     * Optional position of the rule in the list of cube rules. By default(0) the
     * rule gets position of last rule +1.
     *
     * Jedox-Doc type: double
     */
    public ?float $position = null;

    /**
     * Urlencoded query definition for template rules. Empty for regular rules.
     *
     * Jedox-Doc type: string
     */
    public ?string $source = null;

    /**
     * If 1 rules is marked as prepared form IP protection. Default is 0.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $ip_protection = null;

    /**
     * If 1 return also IP protection info. Default is 0.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_protection = null;
}
