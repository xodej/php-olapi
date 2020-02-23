<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/database/save.
 *
 * Saves a database to disk.
 */
class ApiDatabaseSave extends ApiAbstractRequest
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
     * (Optional) Path to file with zip extension where the backup of the database
     * will be saved.
     *
     * Jedox-Doc type: string
     */
    public ?string $external_identifier = null;

    /**
     * (Optional) Mode == 2 means chunked network transfer of zip file with backup
     * via jedox client libraries. Mode == 1 is a direct download. Cannot be
     * combined with external identifier.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    /**
     * (Optional) If in backup mode, include also System DB to the archive
     * (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_system = null;

    /**
     * (Optional) 0 - save System DB as script. 1 - save as compressed folder. 2 -
     * save both. (default is 0).
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;

    /**
     * (Optional) If in backup mode, include also archive files (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_archive = null;

    /**
     * (Optional) If in backup mode, include also audit file to the archive
     * (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_audit = null;

    /**
     * (Optional) If in backup mode, include also csv files (default is 1).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $include_csv = null;

    /**
     * (Optional) If in backup mode, encrypt archive with password.
     *
     * Jedox-Doc type: string
     */
    public ?string $password = null;

    public function url(): ?string
    {
        return '/database/save';
    }
}
