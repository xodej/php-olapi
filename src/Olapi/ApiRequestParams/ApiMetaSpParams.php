<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/meta-sp.
 *
 * SAML Service Provider metadata XML
 */
class ApiMetaSpParams extends RequestParams
{
    /**
     * AssertionConsumerService location if provided from outside and not from
     * palo.ini (optional).
     *
     * Jedox-Doc type: string
     */
    public ?string $saml_login_consumer = null;

    /**
     * SingleLogoutService location if provided from outside (optional).
     *
     * Jedox-Doc type: string
     */
    public ?string $saml_logout_consumer = null;
}
