<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/change_password.
 *
 * Changes user's password.
 */
class ApiServerChangePasswordParams extends RequestParams
{
    /**
     * The name of the user whose password should be changed. (If no user is
     * specified - password is changed for current user).
     *
     * Jedox-Doc type: string
     */
    public string $user;

    /**
     * The plain text new password.
     *
     * Jedox-Doc type: string
     */
    public string $password;
}
