<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/logout.
 *
 * This request is used to close a connection
 */
class ApiServerLogoutParams extends RequestParams
{
    /**
     * When 1 all session jobs are terminated.
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;
}
