<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/dimension/clear.
 *
 * Clears a dimension
 */
class ApiDimensionClear extends ApiAbstractRequest
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
     * Optional parameter. Clear only elements of specified type (1=NUMERIC,
     * 2=STRING, 4=CONSOLIDATED).
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;

    public function url(): ?string
    {
        return '/dimension/clear';
    }
}
