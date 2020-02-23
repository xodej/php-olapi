<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/activate_license.
 *
 * Activate license
 */
class ApiServerActivateLicense extends ApiAbstractRequest
{
    /**
     * License key.
     *
     * Jedox-Doc type: string
     */
    public ?string $lickey = null;

    /**
     * Activation code. When empty license is deactivated.
     *
     * Jedox-Doc type: string
     */
    public ?string $actcode = null;

    public function url(): ?string
    {
        return '/server/activate_license';
    }
}
