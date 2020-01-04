<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/database/create.
 *
 * Creates a database
 */
class ApiDatabaseCreateParams extends RequestParams
{
    /**
     * Name of the new database.
     *
     * Jedox-Doc type: string
     */
    public string $new_name;

    /**
     * Type of the database (0=normal (default), 3=user info).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $type = null;

    /**
     * (Optional) Path to backup file where the database will be loaded from.
     *
     * Jedox-Doc type: string
     */
    public ?string $external_identifier = null;

    /**
     * (Optional) If in restore mode, password to provided encrypted archive with
     * database.
     *
     * Jedox-Doc type: string
     */
    public ?string $password = null;
}
