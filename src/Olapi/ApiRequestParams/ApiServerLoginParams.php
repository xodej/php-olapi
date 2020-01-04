<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/server/login.
 *
 * This request is used for user authentication and to get session identifiers
 * (sid).
 */
class ApiServerLoginParams extends RequestParams
{
    /**
     * The name of the user (required if require-user is enabled).
     *
     * Jedox-Doc type: string
     */
    public ?string $user = null;

    /**
     * Obsolote. Use the extern_password parameter.
     *
     * Jedox-Doc type: string
     */
    public ?string $password = null;

    /**
     * The plain text password of the user (required if require-user is enabled
     * and the supervision server is used for authentication or authorization).
     *
     * Jedox-Doc type: string
     */
    public ?string $extern_password = null;

    /**
     * When 1 server only tries the login validity without creating session. When
     * 2 (SVS mode) it check internal authentication only.
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;

    /**
     * Optional parameter. Machine identifier.
     *
     * Jedox-Doc type: string
     */
    public ?string $machine = null;

    /**
     * Optional parameter. List of required features.
     *
     * Jedox-Doc type: string
     */
    public ?string $required = null;

    /**
     * Optional parameter. List of optional features.
     *
     * Jedox-Doc type: string
     */
    public ?string $optional = null;

    /**
     * Optional parameter. Name of the session. Displayed in management console.
     *
     * Jedox-Doc type: string
     */
    public ?string $new_name = null;

    /**
     * Optional parameter. User's locale. Used for subset sorting.
     *
     * Jedox-Doc type: string
     */
    public ?string $external_identifier = null;

    /**
     * Mode type used internally for SAML authentication.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;
}
