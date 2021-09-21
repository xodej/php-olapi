<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/cell/goalseek.
 *
 * Puts value into cell and calculates values for sister cells in order to
 * parents remain unchanged.
 */
class ApiCellGoalseek extends ApiAbstractRequest
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
     * Comma separated list of element identifiers. Type 0-2: Target cell path.
     * Type 3-4: Source cell of transfer.
     *
     * Jedox-Doc type: path
     */
    public ?string $path = null;

    /**
     * Comma separated list of element names. Used only if path parameter is
     * omitted. Type 0-2: Target cell path. Type 3-4: Source cell of transfer.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path = null;

    /**
     * The type of goal-seek algorithm. 0 complete allocation, 1 equal, 2
     * relative, 3 transfer, 4 full transfer (0 default).
     *
     * Jedox-Doc type: integer
     */
    public ?int $type = null;

    /**
     * Comma separated list of element identifiers list. Each element identifiers
     * list is colon separated. Types 0, 4: Not used. Type 1-2: Siblings to
     * reallocate for each dimension. Type 3: Target area of transfer.                     
     *
     * Jedox-Doc type: area
     */
    public ?string $area = null;

    /**
     * Comma separated list of element names list. Each element names list is
     * colon separated. Used only if area parameter is omitted. Types 0, 4: Not
     * used. Type 1-2: Siblings to reallocate for each dimension. Type 3: Target
     * area of transfer.
     *
     * Jedox-Doc type: narea
     */
    public ?string $name_area = null;

    /**
     * Comma separated list of element identifiers. Type 4 only: Target cell of
     * full transfer.
     *
     * Jedox-Doc type: path
     */
    public ?string $path_to = null;

    /**
     * Comma separated list of element names. Used only if path_to parameter is
     * omitted. Type 4 only: Target cell of full transfer.
     *
     * Jedox-Doc type: npath
     */
    public ?string $name_path_to = null;

    /**
     * Type 0-2: The numeric value of the target cube cell. Type 3: The numeric
     * value to transfer from source cell to target area.
     *
     * Jedox-Doc type: double
     */
    public ?float $value = null;

    /**
     * If 1 then waits until the asynchronous part of operation is finished
     * (optional, default is 0).
     *
     * Jedox-Doc type: boolean
     */
    public ?bool $wait = null;

    public function url(): ?string
    {
        return '/cell/goalseek';
    }
}
