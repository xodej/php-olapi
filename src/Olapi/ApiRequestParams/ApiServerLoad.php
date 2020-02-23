<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/load.
 *
 * Reloads the server from disk
 */
class ApiServerLoad extends ApiAbstractRequest
{
    public function url(): ?string
    {
        return '/server/load';
    }
}
