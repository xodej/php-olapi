<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cell/drillthrough.
 *
 * Retrieves detailed data for a cube cell.
 */
class ApiCellDrillthroughParams extends RequestParams
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
     * Comma separated list of element identifiers list. Each element identifiers
     * list is colon separated. The area is the cartesian product. Only for mode
     * 3.
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * 1 - SVS drillthrough, 2 - SVS secondary mode, 3 - Audit cell history.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    /**
     * string encapsulating the info about sorting, example: "USER-D" - sort by
     * USER column descending (only for audit mode).
     *
     * Jedox-Doc type: string
     */
    public ?string $definition = null;

    /**
     * maximum number of lines retrieved, default 1000 (only for audit mode).
     *
     * Jedox-Doc type: integer
     */
    public ?int $blocksize = null;

    /**
     * start output result from this line number, default 0 - beginning (only for
     * audit mode).
     *
     * Jedox-Doc type: integer
     */
    public ?int $value = null;

    /**
     * string encapsulating the list filter conditions for non-dimensional columns
     * (only for audit mode).
     *
     * Jedox-Doc type: string
     */
    public ?string $source = null;

    /**
     * string encapsulating the filter condition expressions for non-dimensional
     * columns (only for audit mode).
     *
     * Jedox-Doc type: string
     */
    public ?string $condition = null;
}
