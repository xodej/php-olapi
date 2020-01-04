<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/save.
 *
 * Saves the server to disk
 */
class ApiServerSaveParams extends RequestParams
{
    /**
     * (Optional) If "1" then also databases and all cube data will be saved
     * (default "0").
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;
}
