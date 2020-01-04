<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/databases.
 *
 * Returns the list of databases.
 */
class ApiServerDatabasesParams extends RequestParams
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
}
