<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/server/save.
 *
 * Saves the server to disk
 */
class ApiServerSave extends ApiAbstractRequest
{
    /**
     * (Optional) If "1" then also databases and all cube data will be saved
     * (default "0").
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $complete = null;

    public function url(): ?string
    {
        return '/server/save';
    }
}
