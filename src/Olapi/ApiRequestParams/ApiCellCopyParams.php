<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cell/copy.
 *
 * Copies a cell path or a calculated predictive value to an other cell path
 */
class ApiCellCopyParams extends RequestParams
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
     * Optional predictive function. (0 - no prediction (default), 1 - linear
     * regression on consolidated level).
     *
     * Jedox-Doc type: integer
     */
    public ?int $function = null;

    /**
     * Source cell path used for copy (function=0). Comma separated list of
     * element identifiers.
     *
     * Jedox-Doc type: path
     */
    public string $path;

    /**
     * Source cell path used for copy (function=0). Comma separated list of
     * element names. Used only if path parameter is omitted.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path = null;

    /**
     * Source area used for the predictive function (function=1). Comma separated
     * list of element identifiers list. Each element identifiers list is colon
     * separated, only one list can contain multiple elements. The area is the
     * cartesian product.
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * Target cell path. Comma separated list of element identifiers.
     *
     * Jedox-Doc type: path
     */
    public string $path_to;

    /**
     * Target cell path. Comma separated list of element names. Used only if
     * path_to parameter is omitted.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path_to = null;

    /**
     * If 1, then copy rule based cell values (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $use_rules = null;

    /**
     * The numeric value of the target cube cell. (optional).
     *
     * Jedox-Doc type: double
     */
    public ?float $value = null;

    /**
     * Optional colon separated list of paths. Each path is a comma separated list
     * of element identifiers. Splashing will not change locked paths and sources
     * areas of these paths if they are consolidated.
     *
     * Jedox-Doc type: path
     */
    public ?string $locked_paths = null;

    /**
     * If 1 then waits until the asynchronous part of operation is finished
     * (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $wait = null;
}
