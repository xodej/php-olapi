<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/cell/export.
 *
 * Exports values of cube cells
 */
class ApiCellExport extends ApiAbstractRequest
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
     * Maximal number of cells to export (default is 1000).
     *
     * Jedox-Doc type: integer
     */
    public ?int $blocksize = null;

    /**
     * Comma separated list of element identifiers. Begin export after the path
     * (default is to start with the first path).
     *
     * Jedox-Doc type: path
     */
    public ?string $path = null;

    /**
     * Comma separated list of element names. Begin export after the path (default
     * is to start with the first path). Used only if path parameter is omitted.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path = null;

    /**
     * Comma separated list of element identifiers list. Each element identifiers
     * list is colon separated. The area is the cartesian product. Default is the
     * complete cube area.
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * Comma separated list of element names list. Each element names list is
     * colon separated. The area is the cartesian product. Default is the complete
     * cube area. Used only if database area is omitted.
     *
     * Jedox-Doc type: narea
     */
    public ?string $name_area = null;

    /**
     * Condition on the value of numeric or string cells (default is no
     * condition). A condition starts with >, >=, <, <=, ==, or != and is followed
     * by a double or a string. Two condition can be combined by and, or, xor. If
     * you specify a string value, the value has to be csv encoded. Do not forget
     * to URL encode the complete condition string.
     *
     * Jedox-Doc type: string
     */
    public ?string $condition = null;

    /**
     * If 1, then export rule based cell values (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $use_rules = null;

    /**
     * If 1, then export only base cells (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $base_only = null;

    /**
     * 0 - all cells, 1 - skip empty, 2 - skip empty, zero and empty string
     * (default is 1).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $skip_empty = null;

    /**
     * Type of exported cells. 0=numeric and string, 1=only numeric, 2=only string
     * (default is 0).
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;

    /**
     * Comma separated list of cell property ids.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $properties = null;

    /**
     * If 1, then additional information about the cell value is returned, in case
     * the value originates from an enterprise rule.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_rule = null;

    public function url(): ?string
    {
        return '/cell/export';
    }
}
