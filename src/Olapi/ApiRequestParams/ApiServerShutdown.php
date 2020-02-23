<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/shutdown.
 *
 * Shuts down the server
 */
class ApiServerShutdown extends ApiAbstractRequest
{
    public function url(): ?string
    {
        return '/server/shutdown';
    }
}
