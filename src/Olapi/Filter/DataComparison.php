<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

/**
 * Class DataComparison.
 */
class DataComparison
{
    public const COMPARE_OP_GT = 1; // >
    public const COMPARE_OP_LT = 2; // <
    public const COMPARE_OP_GTE = 4; // >=
    public const COMPARE_OP_LTE = 8; // <=
    public const COMPARE_OP_EQ = 16; // =
    public const COMPARE_OP_NEQ = 32; // <>
    public const COMPARE_OP_TRUE = 64; // true

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var float|string
     */
    protected $parameter;

    /**
     * DataComparison constructor.
     *
     * @param int $operator DataComparison::COMPARE_OP_XX constants
     * @param $parameter
     */
    public function __construct(int $operator, $parameter)
    {
        $this->operator = $operator;
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
     * @return float|string
     */
    public function getParameter()
    {
        return $this->parameter;
    }
}
