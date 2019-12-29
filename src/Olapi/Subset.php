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
    private string $name;

    private GeneralFilter $generalFilter;
    private ?HierarchyFilter $hierFilter = null;
    private TextFilter $textFilter;
    private ?PicklistFilter $pickFilter = null;
    private ?AttributeFilter $attrFilter = null;
    private ?DataFilter $dataFilter = null;
    private SortFilter $sortFilter;

    private Dimension $dimension;

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
        //@todo implement Subset::getHttpQuery()
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
     *
     * @return $this
     */
    public function setAttributeFilter(AttributeFilter $attrFilter): self
    {
        $this->attrFilter = $attrFilter;

        return $this;
    }

    /**
     * @param DataFilter $dataFilter
     *
     * @return $this
     */
    public function setDataFilter(DataFilter $dataFilter): self
    {
        $this->dataFilter = $dataFilter;

        return $this;
    }

    /**
     * @param GeneralFilter $generalFilter
     *
     * @return $this
     */
    public function setGeneralFilter(GeneralFilter $generalFilter): self
    {
        $this->generalFilter = $generalFilter;

        return $this;
    }

    /**
     * @param HierarchyFilter $hierFilter
     *
     * @return $this
     */
    public function setHierarchyFilter(HierarchyFilter $hierFilter): self
    {
        $this->hierFilter = $hierFilter;

        return $this;
    }

    /**
     * @param PicklistFilter $pickFilter
     *
     * @return $this
     */
    public function setPicklistFilter(PicklistFilter $pickFilter): self
    {
        $this->pickFilter = $pickFilter;

        return $this;
    }

    /**
     * @param SortFilter $sortFilter
     *
     * @return $this
     */
    public function setSortFilter(SortFilter $sortFilter): self
    {
        $this->sortFilter = $sortFilter;

        return $this;
    }

    /**
     * @param TextFilter $textFilter
     *
     * @return $this
     */
    public function setTextFilter(TextFilter $textFilter): self
    {
        $this->textFilter = $textFilter;

        return $this;
    }
}
