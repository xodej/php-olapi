<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/user_info.
 *
 * Shows information about user
 */
class ApiServerUserInfoParams extends RequestParams
{
    /**
     * If 1 then additional information about the user's permissions on right
     * objects is returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_permission = null;

    /**
     * If 1 then additional info fields are returned - license key and description.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_info = null;

    /**
     * If 1 then the status of the gpu engine (enabled/disabled) is returned
     * (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_gpuflag = null;
}
