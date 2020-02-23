<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/element/move_bulk.
 *
 * Changes the position of elements
 */
class ApiElementMoveBulk extends ApiAbstractRequest
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
    public ?int $elements = null;

    /**
     * Comma separated list of element names. Used only if elements parameter is
     * omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_elements = null;

    /**
     * Comma separated list of new positions of elements.
     *
     * Jedox-Doc type: integer
     */
    public ?int $positions = null;

    public function url(): ?string
    {
        return '/element/move_bulk';
    }
}
