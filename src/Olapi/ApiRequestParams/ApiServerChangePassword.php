<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/change_password.
 *
 * Changes user's password.
 */
class ApiServerChangePassword extends ApiAbstractRequest
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

    public function url(): ?string
    {
        return '/server/change_password';
    }
}
