<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/benchmark.
 *
 * Starts server benchmark test and shows results.
 */
class ApiServerBenchmark extends ApiAbstractRequest
{
    /**
     * 1 - start benchmark test, 2 - show results.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    public function url(): ?string
    {
        return '/server/benchmark';
    }
}
