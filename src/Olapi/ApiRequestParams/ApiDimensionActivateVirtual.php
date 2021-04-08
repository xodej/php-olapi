<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/dimension/activate_virtual
 *
 * Creates or deletes a virtual dimension
 * Creates or deletes a virtual dimension specified by a dimension attribute.
 */
class ApiDimensionActivateVirtual extends ApiAbstractRequest
{
    /**
     * Identifier of the database
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
     * Identifier of the dimension containing the attribute.
     *
     * Jedox-Doc type: identifier
     */
    public int $dimension;

    /**
     * Name of the dimension containing the attribute. Used only if dimension
     * parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_dimension = null;

    /**
     * Identifier of the attribute element
     *
     * Jedox-Doc type: identifier
     */
    public int $attribute;

    /**
     * Name of the attribute element. Used only if attribute parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_attribute = null;

    /**
     * creates (activate=1) or deletes (activate=0) the virtual dimension
     *
     * Jedox-Doc type: integer
     */
    public bool $activate;

    public function url(): ?string
    {
        return '/dimension/activate_virtual';
    }
}
