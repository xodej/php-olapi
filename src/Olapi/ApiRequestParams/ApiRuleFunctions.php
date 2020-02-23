<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/rule/functions.
 *
 * List of available functions
 */
class ApiRuleFunctions extends ApiAbstractRequest
{
    public function url(): ?string
    {
        return '/rule/functions';
    }
}
