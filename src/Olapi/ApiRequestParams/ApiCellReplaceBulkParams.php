<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

use Xodej\Olapi\RequestParams;

/**
 * Parameters for /api/cell/replace_bulk.
 *
 * Sets or changes the value of cube cell
 */
class ApiCellReplaceBulkParams extends RequestParams
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
     * Identifier of the cube.
     *
     * Jedox-Doc type: identifier
     */
    public int $cube;

    /**
     * Name of the cube. Used only if cube parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_cube = null;

    /**
     * Colon separated list of paths. Each path is a comma separated list of
     * element identifiers.
     *
     * Jedox-Doc type: path
     */
    public ?string $paths = null;

    /**
     * Colon separated list of paths. Each path is a comma separated list of
     * element names. Used only if paths parameter is omitted.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_paths = null;

    /**
     * Colon separated list of values. Each value is the new numeric or string
     * value for the cell corresponding to the above path. If you specify a string
     * value, the value has to be csv encoded.
     *
     * Jedox-Doc type: double/string
     */
    public ?string $values = null;

    /**
     * If 0 (the default), then a numeric value given is stored in the cube. If 1,
     * then a numeric value given is added to the existing value or set if no
     * value currently exists. Setting add to 1, requires splash mode 0 or 1.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $add = null;

    /**
     * Optional splash mode for setting values if the comma separated list of
     * elements contains consolidated elements. (0=no splashing, 1=default, 2=add,
     * 3=set).
     *
     * Jedox-Doc type: integer
     */
    public ?int $splash = null;

    /**
     * Optional colon separated list of paths. Each path is a comma separated list
     * of element identifiers. Splashing will not change locked paths and sources
     * areas of these paths if they are consolidated.
     *
     * Jedox-Doc type: path
     */
    public ?string $locked_paths = null;

    /**
     * If 1 (the default), then setting a new value will possibly call the
     * supervision event processor. If 0, then the supervision event processor is
     * circumvented. Note that you need extra permissions to use this feature.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $event_processor = null;

    /**
     * If 1 then waits until the asynchronous part of operation is finished
     * (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $wait = null;
}
