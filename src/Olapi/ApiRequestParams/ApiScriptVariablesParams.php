<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/script/variables.
 *
 * Retrieves information about input variables of the provided script.
 */
class ApiScriptVariablesParams extends RequestParams
{
    /**
     * Content of the script (at least first lines containing VARIABLE_DECLARE
     * functions).
     *
     * Jedox-Doc type: string
     */
    public ?string $definition = null;
}
