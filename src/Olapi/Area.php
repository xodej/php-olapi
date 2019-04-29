<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Area.
 */
class Area
{
    /**
     * @var null|array
     */
    private $area;

    /**
     * @var Cube
     */
    private $cube;

    /**
     * Area constructor.
     *
     * @param Cube       $cube
     * @param null|array $area
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
     */
    public function addElements(string $dimension_name, $elements): void
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
    }

    /**
     * @param string               $dimension_name
     * @param array<string>|string $elements
     *
     * @throws \Exception
     */
    public function setElements(string $dimension_name, $elements): void
    {
        if (!$this->cube->hasDimensionByName($dimension_name)) {
            throw new \InvalidArgumentException('unknown dimension '.
                $dimension_name.' in cube '.$this->cube->getName());
        }

        $elements = (array) $elements;
        $this->area[$dimension_name] = [];

        foreach ($elements as $element) {
            $this->area[$dimension_name][$element] = true;
        }
    }

    /**
     * @param string               $dimension
     * @param array<string>|string $elements
     *
     * @throws \Exception
     */
    public function allExcept(string $dimension, $elements): void
    {
        $elements = (array) $elements;

        $element_list = $this->cube->getDatabase()
            ->getDimensionByName($dimension)
            ->listElements()
        ;

        if (null === $element_list || !isset($element_list['olap_name'])) {
            return;
        }

        $this->area = (array) $this->area;

        $this->area[$dimension] = $element_list['olap_name'];

        foreach ($elements as $element) {
            if (isset($this->area[$dimension][$element])) {
                unset($this->area[$dimension][$element]);
            }
        }
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getArea(): string
    {
        $temp_area = [];

        $this->area = (array) $this->area;

        foreach ($this->area as $dimension => $values) {
            $temp_area[$dimension] = \array_keys($values);
        }

        $this->area = $temp_area;

        return $this->cube->createArea($this->area);
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getAreaAsArray(): array
    {
        $temp_area = [];

        $this->area = (array) $this->area;

        foreach ($this->area as $dimension => $values) {
            $temp_area[$dimension] = \array_keys($values);
        }

        $this->area = $temp_area;

        return $this->cube->createSubcube($this->area);
    }
}
