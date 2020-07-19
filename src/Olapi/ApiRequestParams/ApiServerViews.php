<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/views.
 *
 * Returns the list of global and local views
 */
class ApiServerViews extends ApiAbstractRequest
{
    /**
     * (Optional) If 1 then all global and local views otherwise all global views
     * but only local views defined by the current user are listed (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;

    /**
     * (Optional) If 1 then the view definition is returned (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_definition = null;
}
