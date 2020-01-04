<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/database/cubes.
 *
 * Returns the list of cubes.
 */
class ApiDatabaseCubesParams extends RequestParams
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
     * Show cubes of type normal (0=do not show normal cubes, 1=show (default)).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_normal = null;

    /**
     * Show cubes of type system (0=do not show system cubes (default), 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_system = null;

    /**
     * Show cubes of type attribute (0=do not show attribute cubes (default),
     * 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_attribute = null;

    /**
     * Show cubes of type user info (0=do not show user info cubes (default),
     * 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_info = null;

    /**
     * If 1 then additional information about the user's permission on cube is
     * returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_permission = null;

    /**
     * If 1 return also gpu_flag (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_gpuflag = null;

    /**
     * If 1 return also audit_days (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_audit = null;
}
