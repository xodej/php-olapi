<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/element/destroy.
 *
 * Deletes an element
 */
class ApiElementDestroyParams extends RequestParams
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
     * Identifier of the element.
     *
     * Jedox-Doc type: identifier
     */
    public ?int $element = null;

    /**
     * Name of the element. Used only if element parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_element = null;
}
