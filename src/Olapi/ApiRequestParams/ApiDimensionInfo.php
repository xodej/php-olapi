<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/dimension/info.
 *
 * Returns dimension information.
 */
class ApiDimensionInfo extends ApiAbstractRequest
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
     * Identifier of the dimension.
     *
     * Jedox-Doc type: identifier
     */
    public int $dimension;

    /**
     * Name of the dimension. Used only if dimension parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_dimension = null;

    /**
     * If 1 then additional information about the user's permission on dimension
     * is returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_permission = null;

    /**
     * If 1 then the dimension's load time and memory size are returned (optional,
     * default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_counters = null;

    /**
     * If 1 then additional information about default read, default write, default
     * parent, total and NA elements are returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_default_elements = null;

    /**
     * If 1 then additional information about count of N, C and S elements
     * returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_count_by_type = null;

    public function url(): ?string
    {
        return '/dimension/info';
    }
}
