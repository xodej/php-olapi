<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/cell/area.
 *
 * Shows values of cube cells
 */
class ApiCellArea extends ApiAbstractRequest
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
     * Comma separated list of element identifier lists. Identifier lists are
     * separated by colons. The area is the cartesian product.
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * Comma separated list of element name lists. Name lists are separated by
     * colons. The area is the cartesian product. Used only if area parameter is
     * omitted.
     *
     * Jedox-Doc type: narea
     */
    public ?string $name_area = null;

    /**
     * Optional aggregation function. (1=AVG, 2=COUNT, 3=MAX, 4=MIN, 5=MEDIAN).
     *
     * Jedox-Doc type: integer
     */
    public ?int $function = null;

    /**
     * Comma separated list of expand functions. (1=SELF, 2=CHILDREN, 4=LEAVES).
     *
     * Jedox-Doc type: integer
     */
    public ?int $expand = null;

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

    public function url(): ?string
    {
        return '/cell/area';
    }
}
