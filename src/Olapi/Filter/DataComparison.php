<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

/**
 * Class DataComparison.
 */
class DataComparison
{
    public const OPERATOR_GT = 1; // >
    public const OPERATOR_LT = 2; // <
    public const OPERATOR_GTE = 4; // >=
    public const OPERATOR_LTE = 8; // <=
    public const OPERATOR_EQ = 16; // =
    public const OPERATOR_NEQ = 32; // <>
    public const OPERATOR_TRUE = 64; // true

    protected int $operator;

    /**
     * @var bool|float|int|string
     */
    protected $parameter;

    /**
     * DataComparison constructor.
     *
     * @param int   $operator  DataComparison::OPERATOR_XX constants
     * @param mixed $parameter
     */
    public function __construct(int $operator, $parameter)
    {
        $this->operator = $operator;

        if (!is_scalar($parameter)) {
            throw new \InvalidArgumentException('Argument for DataComparison must be scalar and not null.');
        }
        $this->parameter = $parameter;
    }

    /**
     * @return int
     */
    public function getOperator(): int
    {
        return $this->operator;
    }

    /**
     * @return bool|float|int|string
     */
    public function getParameter()
    {
        return $this->parameter;
    }
}
