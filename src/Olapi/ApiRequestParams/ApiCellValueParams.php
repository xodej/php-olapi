<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cell/value.
 *
 * Shows the value of a cube cell
 */
class ApiCellValueParams extends RequestParams
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
     * Comma separated list of element identifiers.
     *
     * Jedox-Doc type: path
     */
    public ?string $path = null;

    /**
     * Comma separated list of element names. Used only if path parameter is
     * omitted.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path = null;

    /**
     * If 1, then additional information about the cell value is returned, in case
     * the value originates from an enterprise rule.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_rule = null;

    /**
     * If 1, then additional information about the cell lock is returned.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_lock_info = null;

    /**
     * Comma separated list of cell property ids.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $properties = null;

    /**
     * If 1 (default 0) default write element is used for omitted dimensions
     * instead of default read element.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $use_default_write = null;
}
