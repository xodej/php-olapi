<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/database/rename.
 *
 * Renames a database.
 */
class ApiDatabaseRename extends ApiAbstractRequest
{
    /**
     * Identifier of the database.
     *
     * Jedox-Doc type: identifier
     */
    public int $database;

    /**
     * Name of the database. Used only if database parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_database = null;

    /**
     * New name of the database.
     *
     * Jedox-Doc type: string
     */
    public ?string $new_name = null;

    public function url(): ?string
    {
        return '/database/rename';
    }
}
