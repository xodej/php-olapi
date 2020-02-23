<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/database/info.
 *
 * Returns database information.
 */
class ApiDatabaseInfo extends ApiAbstractRequest
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
     * If 1 then additional information about the user's permission on database is
     * returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_permission = null;

    /**
     * If 1 then the database's load time and memory size are returned (optional,
     * default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_counters = null;

    /**
     * If 1 then only database files sizes information is shown (optional, default
     * is 0, returns [folder_size, archives_size, memory_size, use_csv]).
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    public function url(): ?string
    {
        return '/database/info';
    }
}
