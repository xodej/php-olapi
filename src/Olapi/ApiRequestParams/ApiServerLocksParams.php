<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/locks.
 *
 * Lists the locked cube areas
 */
class ApiServerLocksParams extends RequestParams
{
    /**
     * (Optional) If 1 then all locks otherwise only locks defined by the current
     * user are listed (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;
}
