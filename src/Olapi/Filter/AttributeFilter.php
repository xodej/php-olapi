<?php

declare(strict_types=1);

namespace Xodej\Olapi\Filter;

/**
 * Class AttributeFilter.
 */
class AttributeFilter extends Filter
{
    /**
     * Alias Filter.
     *
     * Flags
     * 0x01 search one attribute for an alias
     * 0x02 search two attributes for an alias
     * 0x10 use advanced filter expressions for attribute-values
     * 0x20 do not use POSIX-Basic expressions but use Perl-Extended regular expressions
     * 0x40 case insensitive regex
     *
     * 2;<flags>;<attribute1 name>;<attribute2 name>;<column length>:<, separated list of use_translation bools>;<: separated list of attribute filters>
     * Example:
     * Example 2;18;"Deutsch";"English";3;"English":"Feb*":"Jan*":"Deutsch":"*uar":"*"
     */
    public const FILTER_ID = 2;

    public const FLAG_SEARCH_ONE = 1;
    public const FLAG_SEARCH_TWO = 2;
    public const FLAG_USE_FILTER_EXP = 16;
    public const FLAG_PERL_REGEX = 32;
    public const FLAG_IGNORE_CASE = 64;

    protected ?array $attributes = null;

    /**
     * @param string $attribute_name
     * @param array  $filters
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function addAttribute(string $attribute_name, array $filters): self
    {
        $attribute_dim = $this->getDimension()
            ->getDatabase()->getDimension('#_'.$this->getDimension()->getName().'_');

        if (!$attribute_dim->hasElement($attribute_name)) {
            throw new \InvalidArgumentException('unknown attribute given as argument');
        }

        $this->attributes[$attribute_name] = [$attribute_name, $filters, \count($filters)];

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function parse(): array
    {
        // no flags set - use default flag
        if (null === $this->getFlag()) {
            $this->setFlag(self::FLAG_PERL_REGEX);
            $this->addFlag(self::FLAG_IGNORE_CASE);
        }

        $return = [
            self::FILTER_ID,
            $this->getFlag(),
        ];

        foreach ($this->getCsvArray() as $param) {
            $return[] = $param;
        }

        return $return;
    }

    /**
     * @return int
     */
    protected function getMaxColLen(): int
    {
        return \max(\array_column($this->attributes, 2));
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    protected function getCsvArray(): array
    {
        $max_filters = $this->getMaxColLen() + 1;

        $second_section = [];

        $return = [];

        $attribute_dim = $this->getDimension()
            ->getDatabase()->getDimension('#_'.$this->getDimension()->getName().'_');
        $attributes = $attribute_dim->getAllBaseElements();
        foreach ($attributes as $attribute) {
            $return[] = $attribute;
        }

        foreach ($attributes as $attribute) {
            $temp_filter = [$attribute];

            if (!isset($this->attributes[$attribute])) {
                $temp_filter[] = '';
            }

            if (isset($this->attributes[$attribute])) {
                $temp_x = (array) $this->attributes[$attribute][1];
                foreach ($temp_x as $filter) {
                    $temp_filter[] = $filter;
                }
            }

            $second_section[] = implode(':', \array_merge($temp_filter, \array_fill(0, $max_filters - \count($temp_filter), '')));
        }

        $return[] = $max_filters;
        $return[] = \implode(':', $second_section);

        return $return;
    }
}
