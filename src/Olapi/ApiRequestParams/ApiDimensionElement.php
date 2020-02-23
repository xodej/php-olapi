<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/dimension/element.
 *
 * Shows element at position "n"
 */
class ApiDimensionElement extends ApiAbstractRequest
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
     * Position of element.
     *
     * Jedox-Doc type: integer
     */
    public ?int $position = null;

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
        return '/dimension/element';
    }
}
