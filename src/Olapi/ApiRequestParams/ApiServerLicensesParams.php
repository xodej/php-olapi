<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/licenses.
 *
 * Information about the server license
 */
class ApiServerLicensesParams extends RequestParams
{
    /**
     * Version of license info. Default 0.
     *
     * Jedox-Doc type: string
     */
    public int $mode = 0;
}
