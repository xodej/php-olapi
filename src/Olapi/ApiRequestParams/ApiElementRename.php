<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/element/rename.
 *
 * Changes the name of an element.
 */
class ApiElementRename extends ApiAbstractRequest
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

    /**
     * New name of the element.
     *
     * Jedox-Doc type: string
     */
    public ?string $new_name = null;

    /**
     * If 1 then children and parents list are shortened to intervals (1-10) and
     * weights are shortened to weight:count.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $squash_list = null;

    public function url(): ?string
    {
        return '/element/rename';
    }
}
