<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/info.
 *
 * Information about the server
 */
class ApiServerInfoParams extends RequestParams
{
    /**
     * If 1 then uptime, load time and memory size are returned (optional, default
     * is 0).
     *
     * Jedox-Doc type: boolean
     */
    public bool $show_counters = false;

    /**
     * If 1 then RSA public key is also returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public bool $show_enckey = false;

    /**
     * If 1 then number of interactive sessions is returned (optional, default is
     * 0).
     *
     * Jedox-Doc type: boolean
     */
    public bool $show_user_info = false;
}
