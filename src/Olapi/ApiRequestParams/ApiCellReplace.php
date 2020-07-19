<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/cell/replace.
 *
 * Sets or changes the value of cube cell
 */
class ApiCellReplace extends ApiAbstractRequest
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
     * Comma separated list of element identifiers.
     *
     * Jedox-Doc type: path
     */
    public ?string $path = null;

    /**
     * Comma separated list of element names. Used only if path parameter is
     * omitted.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path = null;

    /**
     * The numeric or string value of the cube cell. Do not forget to URL encode
     * string values.
     *
     * Jedox-Doc type: double/string
     */
    public ?Any $value = null;

    /**
     * Used only when mode is 0. If 0 (default), then a numeric value given is
     * stored in the cube. If 1, then a numeric value given is added to the
     * existing value or set if no value currently exists. Setting add to 1,
     * requires splash type 0 or 1.
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $add = null;

    /**
     * Used only when mode is 0. Optional splash type for setting values if the
     * comma separated list of elements contains consolidated elements. (0=no
     * splashing, 1=default, 2=add to base cells, 3=set to base cells)
     *
     * Jedox-Doc type: integer
     */
    public ?int $splash = null;

    /**
     * Used only when mode is 1. If 1, then copy, like and predict commands write
     * rule based cell values (default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $use_rules = null;

    /**
     * If 0 (default) the value is written to the cube. If 1 then the value is
     * checked to see if it contains a command (#, ##, !, !!, ?, copy, like,
     * predict, from, to, hold), which is then executed. If the value does not
     * contain a command then it is written to the cube as in mode 0.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

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

    public function url(): ?string
    {
        return '/cell/replace';
    }
}
