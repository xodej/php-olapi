<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/dimension/rename.
 *
 * Returns dimension information.
 */
class ApiDimensionRename extends ApiAbstractRequest
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
     * New name of the dimension.
     *
     * Jedox-Doc type: string
     */
    public ?string $new_name = null;

    public function url(): ?string
    {
        return '/dimension/rename';
    }
}
