<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/logout.
 *
 * This request is used to close a connection
 */
class ApiServerLogout extends ApiAbstractRequest
{
    /**
     * When 1 all session jobs are terminated.
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;

    public function url(): ?string
    {
        return '/server/logout';
    }
}
