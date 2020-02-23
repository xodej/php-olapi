<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/locks.
 *
 * Lists the locked cube areas
 */
class ApiServerLocks extends ApiAbstractRequest
{
    /**
     * (Optional) If 1 then all locks otherwise only locks defined by the current
     * user are listed (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;

    public function url(): ?string
    {
        return '/server/locks';
    }
}
