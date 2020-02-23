<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/event/begin.
 *
 * Starts an event.
 */
class ApiEventBegin extends ApiAbstractRequest
{
    /**
     * Session identifier used for logging during the event. All requests between
     * the "event/begin" and "event/end" are logged with the user of the source
     * session instead of the user of the current session denoted by the "sid"
     * parameter. A supervision server has to fill the source parameter with the
     * session identifier which triggered the supervision server.
     *
     * Jedox-Doc type: string
     */
    public string $source;

    /**
     * String used for logging during the event. All requests between the
     * "event/begin" and "event/end" are logged with this string as event. A
     * supervision server has to fill the event parameter with the area identifier
     * which triggered the supervision server.
     *
     * Jedox-Doc type: string
     */
    public string $event;

    public function url(): ?string
    {
        return '/event/begin';
    }
}
