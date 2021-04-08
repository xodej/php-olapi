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

    /**
     * If 1 then count of different types of dimensions and cubes is returned
     * (optional, default is 0, returns [number_normal_dimensions, ...,
     * number_gpu_cubes])
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_count_by_type = null;

    /**
     * If 1 then information about occurring error during database load is
     * returned (optional, default is 0, returns [error code, description,
     * message, details])
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_error = null;

    /**
     * If 1 and show_count_by_type=1 then return the count of virtual attribute
     * dimensions (number_virtual_dimensions)
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_virtual = null;

    public function url(): ?string
    {
        return '/database/info';
    }
}
