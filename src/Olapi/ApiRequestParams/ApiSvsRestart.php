<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/svs/restart.
 *
 * Restarts, starts or stops all configured SVS processes
 */
class ApiSvsRestart extends ApiAbstractRequest
{
    /**
     * (Optional) Without mode parameter configured SVS processes are (re)started.
     * If mode = 1 then running SVS processes are stopped.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    public function url(): ?string
    {
        return '/svs/restart';
    }
}
