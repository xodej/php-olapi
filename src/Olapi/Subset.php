<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use Xodej\Olapi\Filter\AttributeFilter;
use Xodej\Olapi\Filter\DataFilter;
use Xodej\Olapi\Filter\Filter;
use Xodej\Olapi\Filter\GeneralFilter;
use Xodej\Olapi\Filter\HierarchyFilter;
use Xodej\Olapi\Filter\PicklistFilter;
use Xodej\Olapi\Filter\SortFilter;
use Xodej\Olapi\Filter\TextFilter;

/**
 * Class Subset.
 */
class Subset
{
    private $name;

    private $generalFilter;
    private $hierFilter;
    private $textFilter;
    private $pickFilter;
    private $attrFilter;
    private $dataFilter;
    private $sortFilter;

    private $dimension;

    /**
     * Subset constructor.
     *
     * @param Dimension $dimension
     *
     * @throws \Exception
     */
    public function __construct(Dimension $dimension)
    {
        $this->name = "\t".\sha1(\random_bytes(160));
        $this->dimension = $dimension;

        // apply default filters
        $this->generalFilter = new GeneralFilter($this->dimension);
        $this->sortFilter = new SortFilter($this->dimension);
        $this->textFilter = new TextFilter($this->dimension);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $return = [];
        $return[] = $this->name;
        $return[] = $this->dimension->getName();

        foreach ($this->getTextFilter()->parse() as $value) {
            $return[] = $value;
        }

        foreach ($this->getSortFilter()->parse() as $value) {
            $return[] = $value;
        }

        if (null !== $this->getPicklistFilter()) {
            foreach ($this->getPicklistFilter()->parse() as $value) {
                $return[] = $value;
            }
        }

        if (null !== $this->getAttributeFilter()) {
            try {
                foreach ($this->getAttributeFilter()->parse() as $value) {
                    $return[] = $value;
                }
            } catch (\Exception $exception) {
                file_put_contents('php://stderr', $exception->getMessage());
                $return[] = '';
            }
        }

        if (null !== $this->getHierarchyFilter()) {
            try {
                foreach ($this->getHierarchyFilter()->parse() as $value) {
                    $return[] = $value;
                }
            } catch (\Exception $exception) {
                file_put_contents('php://stderr', $exception->getMessage());
                $return[] = '';
            }
        }

        // apply filters
        return Util::strputcsv($return, ';');
    }

    /**
     * @param Filter $filter
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function addFilter(Filter $filter): bool
    {
        if ($this->dimension->getOlapObjectId() !== $filter->getDimension()->getOlapObjectId()) {
            throw new \InvalidArgumentException('given subset filter does not match subset dimension');
        }

        if ($filter instanceof GeneralFilter) {
            $this->setGeneralFilter($filter);

            return true;
        }

        if ($filter instanceof HierarchyFilter) {
            $this->setHierarchyFilter($filter);

            return true;
        }

        if ($filter instanceof TextFilter) {
            $this->setTextFilter($filter);

            return true;
        }

        if ($filter instanceof PicklistFilter) {
            $this->setPicklistFilter($filter);

            return true;
        }

        if ($filter instanceof AttributeFilter) {
            $this->setAttributeFilter($filter);

            return true;
        }

        if ($filter instanceof DataFilter) {
            $this->setDataFilter($filter);

            return true;
        }

        if ($filter instanceof SortFilter) {
            $this->setSortFilter($filter);

            return true;
        }

        return false;
    }

    /**
     * @return null|AttributeFilter
     */
    public function getAttributeFilter(): ?AttributeFilter
    {
        return $this->attrFilter;
    }

    /**
     * @return null|DataFilter
     */
    public function getDataFilter(): ?DataFilter
    {
        return $this->dataFilter;
    }

    /**
     * @return GeneralFilter
     */
    public function getGeneralFilter(): GeneralFilter
    {
        return $this->generalFilter;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return \spl_object_hash($this);
    }

    /**
     * @return null|HierarchyFilter
     */
    public function getHierarchyFilter(): ?HierarchyFilter
    {
        return $this->hierFilter;
    }

    public function getHttpQuery(): void
    {
        //@todo
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|PicklistFilter
     */
    public function getPicklistFilter(): ?PicklistFilter
    {
        return $this->pickFilter;
    }

    /**
     * @return SortFilter
     */
    public function getSortFilter(): SortFilter
    {
        // if not set return default sort filter
        if (null === $this->sortFilter) {
            return new SortFilter($this->dimension);
        }

        return $this->sortFilter;
    }

    /**
     * @return TextFilter
     */
    public function getTextFilter(): TextFilter
    {
        return $this->textFilter;
    }

    /**
     * @param AttributeFilter $attrFilter
     */
    public function setAttributeFilter(AttributeFilter $attrFilter): void
    {
        $this->attrFilter = $attrFilter;
    }

    /**
     * @param DataFilter $dataFilter
     */
    public function setDataFilter(DataFilter $dataFilter): void
    {
        $this->dataFilter = $dataFilter;
    }

    /**
     * @param GeneralFilter $generalFilter
     */
    public function setGeneralFilter(GeneralFilter $generalFilter): void
    {
        $this->generalFilter = $generalFilter;
    }

    /**
     * @param HierarchyFilter $hierFilter
     */
    public function setHierarchyFilter(HierarchyFilter $hierFilter): void
    {
        $this->hierFilter = $hierFilter;
    }

    /**
     * @param PicklistFilter $pickFilter
     */
    public function setPicklistFilter(PicklistFilter $pickFilter): void
    {
        $this->pickFilter = $pickFilter;
    }

    /**
     * @param SortFilter $sortFilter
     */
    public function setSortFilter(SortFilter $sortFilter): void
    {
        $this->sortFilter = $sortFilter;
    }

    /**
     * @param TextFilter $textFilter
     */
    public function setTextFilter(TextFilter $textFilter): void
    {
        $this->textFilter = $textFilter;
    }
}
