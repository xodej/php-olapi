<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/element/replace_bulk.
 *
 * Creates or updates list of elements
 */
class ApiElementReplaceBulkParams extends RequestParams
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
     * Comma separated list of element identifiers.
     *
     * Jedox-Doc type: identifier
     */
    public ?string $elements = null;

    /**
     * Comma separated list of names of elements. Used only if elements parameter
     * is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_elements = null;

    /**
     * Type of the element (1=NUMERIC, 2=STRING, 4=CONSOLIDATED).
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;

    /**
     * Type of the elements (1=NUMERIC, 2=STRING, 4=CONSOLIDATED) as comma
     * separated list. Either type or types must be specified. If type is
     * specified all elements are of the same type. If types is specified then
     * children and weights must be empty for elements of numeric or string type.
     *
     * Jedox-Doc type: integer
     */
    public ?int $types = null;

    /**
     * Comma and colon separate list of children identifiers. (Only for type=4).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $children = null;

    /**
     * Optional comma and colon separate list of children weight. (defaults to
     * weight=1 for each child) (Only for type=4).
     *
     * Jedox-Doc type: double
     */
    public ?float $weights = null;
}
