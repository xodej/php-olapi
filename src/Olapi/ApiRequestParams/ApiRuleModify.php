<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/rule/modify.
 *
 * Modifies an enterprise rule for a cube. Use the parameter "definition" for
 * changing the rule or use the parameter "activate" for activating and
 * deactivating.
 */
class ApiRuleModify extends ApiAbstractRequest
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
     * Comma separated list of rule identifiers to be modified. If definition is
     * specified, only first rule is modified!
     *
     * Jedox-Doc type: identifier
     */
    public ?string $rule = null;

    /**
     * Urlencoded enterprise rule.
     *
     * Jedox-Doc type: string
     */
    public ?string $definition = null;

    /**
     * activate rule with "1", deactivate rule with "0" or toggle rule active
     * state with "2".
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
     * Optional position of the rule in the list of cube rules. By default the
     * position stays unchanged. If multiple rules are specified in parameter
     * 'rule' all these rules will get new position. First rule from the list get
     * position 'position', second gets 'position'+1 etc.
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

    public function url(): ?string
    {
        return '/rule/modify';
    }
}
