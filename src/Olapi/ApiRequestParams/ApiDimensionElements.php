<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/dimension/elements.
 *
 * Shows all elements of a dimension
 */
class ApiDimensionElements extends ApiAbstractRequest
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
     * Identifier of parent for element filtering or empty parameter for root
     * elements. Root elements includes children of hidden elements. (no filter by
     * default).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $parent = null;

    /**
     * Optional comma delimited offset of first element returned (0-default) and
     * maximal count of returned element (all elements by default).
     *
     * Jedox-Doc type: identifier
     */
    public ?string $limit = null;

    /**
     * Element identifier. If it's specified, server goes from limit start and
     * finds block that contains this element. Actual start is returned before the
     * list of elements.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $element = null;

    /**
     * If 1 then additional information about the user's permission on element is
     * returned (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $show_permission = null;

    /**
     * If 1 then children and parents list are shortened to intervals (1-10) and
     * weights are shortened to weight:count.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $squash_list = null;

    public function url(): ?string
    {
        return '/dimension/elements';
    }
}
