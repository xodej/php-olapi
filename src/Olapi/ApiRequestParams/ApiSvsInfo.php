<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/svs/info.
 *
 * Returns information about configuration and status of SupervisionServer
 * processes
 */
class ApiSvsInfo extends ApiAbstractRequest
{
    public function url(): ?string
    {
        return '/svs/info';
    }
}
