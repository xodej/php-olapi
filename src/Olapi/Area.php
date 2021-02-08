<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Area.
 */
class Area
{
    /**
     * @var array<string,array<string,bool>>
     */
    private array $area;
    private Cube $cube;

    /**
     * Area constructor.
     *
     * @param Cube                                  $cube
     * @param null|array<string,array<string,bool>> $area
     */
    public function __construct(Cube $cube, ?array $area = null)
    {
        $this->cube = $cube;
        $this->area = $area ?? [];
    }

    /**
     * @param string               $dimension_name
     * @param array<string>|string $elements
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function addElements(string $dimension_name, $elements): self
    {
        if (!$this->cube->hasDimensionByName($dimension_name)) {
            throw new \InvalidArgumentException('unknown dimension '.
                $dimension_name.' in cube '.$this->cube->getName());
        }

        $elements = (array) $elements;

        if (!isset($this->area[$dimension_name])) {
            $this->area[$dimension_name] = [];
        }

        foreach ($elements as $element) {
            $this->area[$dimension_name][$element] = true;
        }

        return $this;
    }

    /**
     * @param string               $dimension_name
     * @param array<string>|string $elements
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setElements(string $dimension_name, $elements): self
    {
        if (!$this->cube->hasDimensionByName($dimension_name)) {
            throw new \InvalidArgumentException('unknown dimension '.
                $dimension_name.' in cube '.$this->cube->getName());
        }

        $this->area[$dimension_name] = [];

        return $this->addElements($dimension_name, $elements);
    }

    /**
     * @param string               $dimension_name
     * @param array<string>|string $except_elements
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function allExcept(string $dimension_name, $except_elements): self
    {
        $except_elements = (array) $except_elements;

        $element_list = $this->cube->getDatabase()
            ->getDimensionByName($dimension_name)
            ->listElements()
        ;

        if (null === $element_list || empty($element_list)) {
            return $this;
        }

        $element_list = \array_map(static function (array $v) {
            return $v[1] ?? null;
        }, $element_list);

        $except_elements = \array_map(static function ($v): string {
            return \strtolower($v);
        }, $except_elements);
        $except_elements_flipped = \array_flip($except_elements);

        $element_list = \array_filter($element_list, static function ($v) use ($except_elements_flipped): bool {
            if (isset($except_elements_flipped[\strtolower($v)])) {
                return false;
            }

            return true;
        });

        $this->area[$dimension_name] = \array_flip($element_list);

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getArea(): string
    {
        return $this->cube->createArea($this->prepareArea());
    }

    /**
     * @throws \Exception
     *
     * @return array<string>
     */
    public function getAreaAsArray(): array
    {
        return $this->cube->createSubcube($this->prepareArea());
    }

    /**
     * @return array<string,array<string>>
     */
    protected function prepareArea(): array
    {
        $temp_area = [];
        foreach ($this->area as $dimension => $values) {
            $temp_area[$dimension] = \array_keys($values);
        }

        return $temp_area;
    }
}
