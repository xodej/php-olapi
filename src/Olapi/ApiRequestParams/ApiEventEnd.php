<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/event/end.
 *
 * Ends an event.
 */
class ApiEventEnd extends ApiAbstractRequest
{
    public function url(): ?string
    {
        return '/event/end';
    }
}
