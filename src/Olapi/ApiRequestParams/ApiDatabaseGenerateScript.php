<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/database/generate_script.
 *
 * Generates new script for a database.
 */
class ApiDatabaseGenerateScript extends ApiAbstractRequest
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
     * Dimensions to include in the script. Empty means none, to include all
     * dimensions use asterisk. Comma separated list of dimension identifiers.
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
     * Cubes to include in the script. Empty means none, to include all cubes use
     * asterisk. Comma separated list of cube identifiers.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $cubes = null;

    /**
     * Comma separated list of cube names. Used only if cubes parameter is
     * omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_cubes = null;

    /**
     * Generate elements info or do not include elements (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_elements = null;

    /**
     * Generate cell values info or do not include cells (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;

    /**
     * Include all attributes and their values (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_attribute = null;

    /**
     * Include local subsets (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_local_subsets = null;

    /**
     * Include global subsets (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_global_subsets = null;

    /**
     * Include local views (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_local_views = null;

    /**
     * Include global views (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_global_views = null;

    /**
     * Include dimension rights (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_dimension_rights = null;

    /**
     * Include cube rights (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_cube_rights = null;

    /**
     * 1 - delete elements and cube cells first in the script (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $clear = null;

    /**
     * Languages (translations) to include in the script. Comma separated list of
     * languages. Empty for default language only, asterisk symbol for all
     * languages. Only with show_attribute=1.
     *
     * Jedox-Doc type: string
     */
    public ?string $languages = null;

    /**
     * If 1, then include also rules definition into script (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_rule = null;

    /**
     * 0 (default) - ERROR_IF_EXISTS, 1 - ERROR_IF_DIFFERS, 2 - NO_ERROR.
     *
     * Jedox-Doc type: integer
     */
    public ?int $script_create_clause = null;

    /**
     * 0 (default) - ERROR_IF_NOT_EXISTS, 1 - NO_ERROR.
     *
     * Jedox-Doc type: integer
     */
    public ?int $script_modify_clause = null;

    public function url(): ?string
    {
        return '/database/generate_script';
    }
}
