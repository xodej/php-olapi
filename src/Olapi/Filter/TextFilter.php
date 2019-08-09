<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

/**
 * Class TextFilter.
 */
class TextFilter extends Filter
{
    /**
     * Text filter.
     *
     * Flags
     * 0x02 do not use POSIX-Basic expressions but use Perl-Extended regular expressions
     * 0x04 use element name not alias
     * 0x08 case insensitive regex
     *
     * 3;<flags>;<: separated list of regular expressions>
     */
    public const FILTER_ID = 3;

    public const FLAG_PERL_REGEX = 2;
    public const FLAG_NOT_ALIAS = 4;
    public const FLAG_IGNORE_CASE = 8;

    /**
     * @var array
     */
    protected $expressions;

    /**
     * @param string $expression
     */
    public function addExpression(string $expression): void
    {
        if (null === $this->expressions) {
            $this->expressions = [];
        }

        $this->expressions[] = $expression;
    }

    public function reset(): void
    {
        $this->expressions = [];
    }

    /**
     * @return array
     */
    public function parse(): array
    {
        if (null === $this->expressions) {
            return [];
        }

        return [
            self::FILTER_ID,
            $this->getFlag(),
            \implode(':', $this->expressions),
        ];
    }
}
