<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cube/info.
 *
 * Shows cube data
 */
class ApiCubeInfoParams extends RequestParams
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
     * Identifier of the cube.
     *
     * Jedox-Doc type: identifier
     */
    public int $cube;

    /**
     * Name of the cube. Used only if cube parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_cube = null;

    /**
     * If 1 then additional information about the user's permission on cube is
     * returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_permission = null;

    /**
     * If 1 then the cube's load time and memory size are returned (optional,
     * default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_counters = null;

    /**
     * If 1 then waits until all write operations of the session are processed
     * (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $wait = null;

    /**
     * If 1 return also gpu_flag (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_gpuflag = null;

    /**
     * If 1 return also audit_days (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_audit = null;

    /**
     * If 1 then store_zero_flag, zero_count and empty_string_count are returned
     * (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_zero = null;

    /**
     * If 1 only info about cube performance is returned (optional, default is 0).
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    /**
     * Timeout in seconds. Affects only mode 1. (optional, default 0).
     *
     * Jedox-Doc type: integer
     */
    public ?int $timeout = null;
}
