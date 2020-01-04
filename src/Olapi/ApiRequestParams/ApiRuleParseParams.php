<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/rule/parse.
 *
 * Parse an enterprise rule
 */
class ApiRuleParseParams extends RequestParams
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
     * Optional Identifier of the rule using CONTINUE() or 4294967294 for STET()
     * reference. Used by calculation tracker.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $rule = null;

    /**
     * List of function names.
     *
     * Jedox-Doc type: string
     */
    public ?string $functions = null;

    /**
     * Urlencoded enterprise rule.
     *
     * Jedox-Doc type: string
     */
    public ?string $definition = null;

    /**
     * Use identifier in textual representation of the rule in the result. The
     * definition can use name or identifier independent of the parameter. Default
     * is 0.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $use_identifier = null;

    /**
     * Comma separated list of element identifiers (database or name_database has
     * to be specified) the rule calculation is evaluated for.
     *
     * Jedox-Doc type: path
     */
    public ?string $path = null;

    /**
     * Comma separated list of element names (database or name_database has to be
     * specified) the rule calculation is evaluated for.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path = null;
}
