<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cube/generate_script.
 *
 * Generates new script for a database.
 */
class ApiCubeGenerateScriptParams extends RequestParams
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
     * (Optional) If complete is "1" then the whole cube - regardless of the
     * specified area - will be exported to the script.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;

    /**
     * Comma separated list of element identifier lists. Identifier lists are
     * separated by colons. The area is the cartesian product. If not present and
     * complete=0, no cells are exported.
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * Comma separated list of element name lists. Name lists are separated by
     * colons. The area is the cartesian product. Used only if area parameter is
     * omitted.
     *
     * Jedox-Doc type: narea
     */
    public ?string $name_area = null;

    /**
     * Include all attributes and their values (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_attribute = null;

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
     * Include rights (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_cube_rights = null;

    /**
     * Languages (translations) to include in the script. Comma separated list of
     * languages. Empty for default language only, asterisk symbol for all
     * languages. Only with show_attribute=1.
     *
     * Jedox-Doc type: string
     */
    public ?string $languages = null;

    /**
     * 1 - delete cube cells first in the script (default 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $clear = null;

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
}
