<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cell/values.
 *
 * Shows the values of cube cells
 */
class ApiCellValuesParams extends RequestParams
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
     * Colon separated list of paths. Each path is a comma separated list of
     * element identifiers.
     *
     * Jedox-Doc type: path
     */
    public ?string $paths = null;

    /**
     * Colon separated list of paths. Each path is a comma separated list of
     * element names. Used only if paths parameter is omitted.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_paths = null;

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
}
