<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/licenses.
 *
 * Information about the server license
 */
class ApiServerLicenses extends ApiAbstractRequest
{
    /**
     * Version of license info. Default 0.
     *
     * Jedox-Doc type: string
     */
    public int $mode = 0;

    public function url(): ?string
    {
        return '/server/licenses';
    }
}
