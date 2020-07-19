<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/databases.
 *
 * Returns the list of databases.
 */
class ApiServerDatabases extends ApiAbstractRequest
{
    /**
     * Show databases of type normal (0=do not show normal databases, 1=show
     * (default)).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_normal = null;

    /**
     * Show databases of type system (0=do not show system databases (default),
     * 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_system = null;

    /**
     * Show databases of type user info (0=do not show user info databases
     * (default), 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_user_info = null;

    /**
     * If 1 then additional information about the user's permission on database is
     * returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_permission = null;

    /**
     * If 1 then the database's load time and memory size are returned (optional,
     * default is 0)
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_counters = null;

    /**
     * If 1 then count of different types of dimensions and cubes is returned
     * (optional, default is 0, returns [number_normal_dimensions, ...,
     * number_gpu_cubes])
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_count_by_type = null;

    /**
     * If 1 then information about occurring error during database load is
     * returned (optional, default is 0, returns [error code, description,
     * message, details])
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_error = null;

    public function url(): ?string
    {
        return '/server/databases';
    }
}
