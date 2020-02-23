<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/dimension/create.
 *
 * Creates a dimension
 */
class ApiDimensionCreate extends ApiAbstractRequest
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
     * Name of the new dimension.
     *
     * Jedox-Doc type: string
     */
    public ?string $new_name = null;

    /**
     * Type of the dimension (0=normal (default), 3=user info).
     *
     * Jedox-Doc type: identifier
     */
    public ?int $type = null;

    /**
     * If 1 default subsets are generated (optional, default is 0).
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    public function url(): ?string
    {
        return '/dimension/create';
    }
}
