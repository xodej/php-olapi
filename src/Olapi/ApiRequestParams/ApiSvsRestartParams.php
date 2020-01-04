<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/svs/restart.
 *
 * Restarts, starts or stops all configured SVS processes
 */
class ApiSvsRestartParams extends RequestParams
{
    /**
     * (Optional) Without mode parameter configured SVS processes are (re)started.
     * If mode = 1 then running SVS processes are stopped.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;
}
