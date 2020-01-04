<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/dimension/generate_script.
 *
 * Generates new script for a dimension.
 */
class ApiDimensionGenerateScriptParams extends RequestParams
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
     * Identifier of the dimension.
     *
     * Jedox-Doc type: identifier
     */
    public int $dimension;

    /**
     * Name of the dimension. Used only if dimension parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_dimension = null;

    /**
     * (Optional) If complete is "1" then the whole dimension - regardless of the
     * specified elements list - will be exported to the script.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;

    /**
     * Comma separated list of element identifiers (optional). Generate info just
     * for these elements. If empty or not present and complete=0, no elements are
     * exported.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $elements = null;

    /**
     * Comma separated list of element names (optional). Used only if elements
     * parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_elements = null;

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
     * Include dimension rights (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_dimension_rights = null;

    /**
     * Languages (translations) to include in the script. Comma separated list of
     * languages. Empty for default language only, asterisk symbol for all
     * languages. Only with show_attribute=1.
     *
     * Jedox-Doc type: string
     */
    public ?string $languages = null;

    /**
     * 1 - delete dimension elements first in the script (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $clear = null;

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
}
