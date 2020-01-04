<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/activate_license.
 *
 * Activate license
 */
class ApiServerActivateLicenseParams extends RequestParams
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
}
