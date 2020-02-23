<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/script/variables.
 *
 * Retrieves information about input variables of the provided script.
 */
class ApiScriptVariables extends ApiAbstractRequest
{
    /**
     * Content of the script (at least first lines containing VARIABLE_DECLARE
     * functions).
     *
     * Jedox-Doc type: string
     */
    public ?string $definition = null;

    public function url(): ?string
    {
        return '/script/variables';
    }
}
