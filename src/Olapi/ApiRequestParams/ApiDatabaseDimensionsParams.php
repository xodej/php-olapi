<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/database/dimensions.
 *
 * Returns the list of dimensions.
 */
class ApiDatabaseDimensionsParams extends RequestParams
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
     * Show dimensions of type normal (0=do not show normal dimensions, 1=show
     * (default)).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_normal = null;

    /**
     * Show dimensions of type system (0=do not show system dimensions (default),
     * 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_system = null;

    /**
     * Show dimensions of type attribute (0=do not show attribute dimensions
     * (default), 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_attribute = null;

    /**
     * Show dimensions of type user info (0=do not show user info dimensions
     * (default), 1=show).
     *
     * Jedox-Doc type: identifier
     */
    public ?bool $show_info = null;

    /**
     * If 1 then additional information about the user's permission on dimension
     * is returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_permission = null;

    /**
     * Return only dimensions containing specified element.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_element = null;

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
}
