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
     * @var null|string[]
     */
    protected ?array $expressions = null;

    /**
     * @param string $expression
     *
     * @return $this
     */
    public function addExpression(string $expression): self
    {
        if (null === $this->expressions) {
            $this->expressions = [];
        }

        $this->expressions[] = $expression;

        return $this;
    }

    /**
     * @return $this
     */
    public function reset(): self
    {
        $this->expressions = [];

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function parse(): array
    {
        if (null === $this->expressions) {
            return [];
        }

        if (null === $this->getFlag()) {
            $this->setFlag(0);
        }

        return [
            self::FILTER_ID,
            $this->getFlag(),
            \implode(':', $this->expressions),
        ];
    }
}
