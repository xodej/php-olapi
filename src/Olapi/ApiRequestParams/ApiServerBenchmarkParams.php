<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/benchmark.
 *
 * Starts server benchmark test and shows results.
 */
class ApiServerBenchmarkParams extends RequestParams
{
    /**
     * 1 - start benchmark test, 2 - show results.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;
}
