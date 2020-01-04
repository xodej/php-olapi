<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/element/replace.
 *
 * Creates or updates an element
 */
class ApiElementReplaceParams extends RequestParams
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
     * Name of the element. (alternative for element parameter when the element
     * exists, alternative for new_name parameter when new element is created).
     *
     * Jedox-Doc type: string
     */
    public ?string $name_element = null;

    /**
     * Name of the new element.
     *
     * Jedox-Doc type: string
     */
    public ?string $new_name = null;

    /**
     * Type of the element (1=NUMERIC, 2=STRING, 4=CONSOLIDATED).
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;

    /**
     * Comma separated list of children identifiers. (Only for type=4).
     *
     * Jedox-Doc type: identifier
     */
    public ?string $children = null;

    /**
     * Comma separated list of children names. Used only if children parameter is
     * omitted. (Only for type=4).
     *
     * Jedox-Doc type: string
     */
    public ?string $name_children = null;

    /**
     * Optional comma separated list of children weights. (defaults to weight=1
     * for each child) (Only for type=4).
     *
     * Jedox-Doc type: double
     */
    public ?string $weights = null;

    /**
     * If 1 then children and parents list are shortened to intervals (1-10) and
     * weights are shortened to weight:count.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $squash_list = null;
}
