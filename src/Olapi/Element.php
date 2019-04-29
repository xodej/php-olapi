<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Element.
 */
class Element implements IBase
{
    public const API_ELEMENT_INFO = '/element/info';
    public const API_ELEMENT_REPLACE = '/element/replace';
    public const API_ELEMENT_REPLACE_BULK = '/element/replace_bulk';

    public const ELEMENT_TYPE_NUMERIC = 1;
    public const ELEMENT_TYPE_STRING = 2;
    public const ELEMENT_TYPE_CONSOLIDATED = 4;

    /**
     * @var Dimension
     */
    private $dimension;

    /**
     * @var array
     */
    private $metaInfo;

    /**
     * Element constructor.
     *
     * @param Dimension $dimension
     * @param array     $meta_info
     */
    public function __construct(Dimension $dimension, array $meta_info)
    {
        $this->dimension = $dimension;
        $this->metaInfo = $meta_info;
    }

    /**
     * @param Element    $child
     * @param null|float $weight
     *
     * @throws \Exception
     *
     * @return null|Store
     */
    public function addChild(Element $child, ?float $weight = null): ?Store
    {
        $children_ids = $this->getChildrenIds();
        $children_ids[] = $child->getOlapObjectId();

        $weights = $this->getChildrenWeights();
        $weights[] = $weight ?? 1.0;

        return $this->modify(
            $children_ids,
            $weights,
            self::ELEMENT_TYPE_CONSOLIDATED
        );
    }

    /**
     * @throws \Exception
     *
     * @return \SimpleXMLElement
     */
    public function asXml(): \SimpleXMLElement
    {
        $elem_sxe = new \SimpleXMLElement('<element />');
        $elem_sxe->addAttribute('name', $this->getName());
        $elem_sxe->addAttribute('id', (string) $this->getOlapObjectId());
        $elem_sxe->addAttribute('type', (string) $this->getElementType(false));

        /*
        $attributes = (array) $this->getDimension()->getAttributes([$this->getName()], null, true);

        print_r($attributes);

        foreach ($attributes as $attribute_name => $attribute_val) {
            $elem_sxe->addAttribute($attribute_name, $attribute_val);
        }
        */

        if ($this->hasChildren()) {
            $elem_sxe->addAttribute('children_ids', \implode(',', $this->getChildrenIds()));
            $elem_sxe->addAttribute('children_weights', \implode(',', $this->getChildrenWeights()));
        }

        return $elem_sxe;
    }

    /**
     * @param Element    $parent
     * @param null|float $weight
     *
     * @throws \Exception
     *
     * @return null|Store
     */
    public function attach(Element $parent, ?float $weight = null): ?Store
    {
        $weight = $weight ?? 1.0;

        return $parent->addChild($this, $weight);
    }

    /**
     * @return int
     */
    public function countChildren(): int
    {
        return \substr_count($this->metaInfo[10], ',') + 1;
    }

    /**
     * @return int
     */
    public function countParents(): int
    {
        return \substr_count($this->metaInfo[8], ',') + 1;
    }

    /**
     * deletes element from dimension.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->getDimension()
            ->deleteElementById($this->getOlapObjectId())
        ;
        // @todo trigger reload of elements in dimension (can be slow for many deletes)
    }

    /**
     * @param null|string[] $children
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function detachChildren(?array $children = null): bool
    {
        if (null === $children) {
            // @todo Element::detachChildren()
            $this->modify([]);

            return true;
        }

        $children_ids = [];
        foreach ($children as $child_name) {
            $children_ids[] = $this->getDimension()->getElementIdFromName($child_name);
        }

        return true;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function exists(): bool
    {
        // needs to be a function with ability to suppress errors
        $response = $this->getInfo();

        return isset($response[0][0]) && \is_numeric($response[0][0]);
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getAncestors(): array
    {
        $return = [];

        foreach ($this->getParents() as $parent) {
            $return[] = $parent->getAncestors();
        }

        $return[] = [[
            'name' => $this->getName(),
            'type' => $this->getElementType(),
            'identifier' => $this->getOlapObjectId(),
        ]];

        return \array_merge(...$return);
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getAttributes(): array
    {
        $area = new Area($this->getDimension()->getAttributeCube());
        $area->addElements($this->getDimension()->getName(), [$this->getName()]);

        return $this->getDimension()->getAttributeCube()->arrayExport(['area' => $area->getArea()], false);
    }

    /**
     * @throws \Exception
     *
     * @return Element[]
     */
    public function getChildren(): array
    {
        if (!$this->hasChildren()) {
            return [];
        }

        $children = \explode(',', $this->metaInfo[10]);

        $return = [];
        foreach ($children as $child) {
            $return[] = $this->getDimension()->getElementById((int) $child);
        }

        return $return;
    }

    /**
     * @return int[]
     */
    public function getChildrenIds(): array
    {
        if ('' === $this->metaInfo[10]) {
            return [];
        }

        return \array_map(static function ($v) {
            return (int) $v;
        }, \explode(',', $this->metaInfo[10]));
    }

    /**
     * @return array
     */
    public function getChildrenWeights(): array
    {
        // var_dump($this->metaInfo[11]);
        return \explode(',', $this->metaInfo[11]);
    }

    /**
     * @throws \Exception
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->getDimension()->getConnection();
    }

    /**
     * @throws \Exception
     *
     * @return array|Element[]
     */
    public function getConsolidationChildren(): array
    {
        return $this->getChildren();
    }

    /**
     * @param Element $parent
     *
     * @throws \Exception
     *
     * @return null|float
     */
    public function getConsolidationFactor(Element $parent): ?float
    {
        $children = $parent->getChildren();
        $weights = $parent->getChildrenWeights();

        foreach ($children as $index => $child) {
            if ($child->getName() === $this->getName()) {
                return (float) $weights[(int) $index];
            }
        }

        return null;
    }

    /**
     * @throws \Exception
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->getDimension()->getDatabase();
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getDescendants(): array
    {
        $return = [];

        foreach ($this->getChildren() as $child) {
            $return[] = $child->getDescendants();
        }

        $return[] = [[
            'name' => $this->getName(),
            'type' => $this->getElementType(),
            'identifier' => $this->getOlapObjectId(),
        ]];

        return \array_merge(...$return);
    }

    /**
     * @throws \Exception
     *
     * @return Dimension
     */
    public function getDimension(): Dimension
    {
        return $this->dimension;
    }

    /**
     * @param null|bool $asNumber
     *
     * @return int|string
     */
    public function getElementType(?bool $asNumber = null)
    {
        $as_number = $asNumber ?? false;
        if ($as_number) {
            return (int) $this->metaInfo[6];
        }

        return self::getTypeNameFromTypeNumber((int) $this->metaInfo[6]);
    }

    /**
     * @throws \Exception
     *
     * @return Store
     */
    public function getInfo(): Store
    {
        // @todo overwrite $this->metaInfo
        return $this->getConnection()->request(self::API_ELEMENT_INFO, [
            'query' => [
                'name_database' => $this->getDatabase()->getName(),
                'name_dimension' => $this->getDimension()->getName(),
                'name_element' => $this->getName(),
                'show_permission' => 0, // default
            ],
        ]);
    }

    /**
     * @param Dimension $dimension
     * @param string    $element_name
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return Element
     */
    public static function getInstance(Dimension $dimension, string $element_name): Element
    {
        return $dimension->getElementByName($element_name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->metaInfo[1];
    }

    /**
     * @return int
     */
    public function getOlapObjectId(): int
    {
        return (int) $this->metaInfo[0];
    }

    /**
     * @return int[]
     */
    public function getParentIds(): array
    {
        if ('' === $this->metaInfo[8]) {
            return [];
        }

        return \array_map(static function ($v) {
            return (int) $v;
        }, \explode(',', $this->metaInfo[8]));
    }

    /**
     * @throws \Exception
     *
     * @return Element[]
     */
    public function getParents(): array
    {
        if (!$this->hasParents()) {
            return [];
        }

        $parents = \explode(',', $this->metaInfo[8]);

        $return = [];
        foreach ($parents as $parent) {
            $return[] = $this->getDimension()->getElementById((int) $parent);
        }

        return $return;
    }

    /**
     * @throws \Exception
     *
     * @return Element[]
     */
    public function getSiblings(): array
    {
        $parents = $this->getParents();

        $return = [];
        foreach ($parents as $parent) {
            /**
             * @var Element[]
             */
            $siblings = $parent->getChildren();
            foreach ($siblings as $sibling) {
                if ($sibling->getName() === $this->getName()) {
                    continue;
                }
                // @todo includes still doubled siblings
                $return[$sibling->getOlapObjectId()] = $sibling;
            }
        }

        return \array_values($return);
    }

    /**
     * @param int $type
     *
     * @return string
     */
    public static function getTypeNameFromTypeNumber(int $type): string
    {
        switch ($type) {
            case self::ELEMENT_TYPE_NUMERIC:
                return 'numeric';

                break;
            case self::ELEMENT_TYPE_STRING:
                return 'string';

                break;
            case self::ELEMENT_TYPE_CONSOLIDATED:
                return 'consolidated';

                break;
        }

        return 'unknown';
    }

    /**
     * @param null|Element $parent
     *
     * @throws \Exception
     *
     * @return null|float
     */
    public function getWeight(Element $parent = null): ?float
    {
        if (null === $parent) {
            $parents = $this->getParents();

            // no parent was found
            if (!isset($parents[0])) {
                // @todo check whether no parent means conso factor of 1
                return 1.0;
            }

            // no explicit parent given but more than one was found
            if (isset($parents[1])) {
                // @todo iterate over all parents and check for same conso factor
                // otherwise throw exception because parent dependant conso factor
                throw new \DomainException('more than two parents');
            }

            // if exactly one parent override parent
            $parent = $parents[0];
        }

        return $this->getConsolidationFactor($parent);
    }

    /**
     * @param string $child_element_name
     *
     * @throws \Exception
     *
     * @return bool
     *
     * @see Element::hasChildByName() alias
     */
    public function hasChild(string $child_element_name): bool
    {
        return $this->hasChildByName($child_element_name);
    }

    /**
     * @param int $child_element_id
     *
     * @return bool
     */
    public function hasChildById(int $child_element_id): bool
    {
        return \in_array($child_element_id, $this->getChildrenIds(), true);
    }

    /**
     * @param string $child_element_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasChildByName(string $child_element_name): bool
    {
        if (!$this->getDimension()->hasElementByName($child_element_name)) {
            throw new \InvalidArgumentException('given element name '.$child_element_name.' for dimension '.
                $this->getDimension()->getName().' unknown');
        }

        return $this->hasChildById($this->getDimension()->getElementIdFromName($child_element_name));
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return !('' === $this->metaInfo[10]);
    }

    /**
     * @return bool
     */
    public function hasParents(): bool
    {
        return !('' === $this->metaInfo[8]);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function hasSiblings(): bool
    {
        $parents = $this->getParents();
        foreach ($parents as $parent) {
            if ($parent->hasChildren()) {
                return true;
            }
        }

        return false;
    }

    /**
     * returns true if element is of type base.
     *
     * @return bool
     */
    public function isBaseElement(): bool
    {
        return !(self::ELEMENT_TYPE_CONSOLIDATED === (int) $this->metaInfo[6]);
    }

    /**
     * returns true if element is of type consolidated.
     *
     * @return bool
     */
    public function isConsolidatedElement(): bool
    {
        return !$this->isBaseElement();
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->getConnection()->isDebugMode();
    }

    /**
     * @param null|array $children
     * @param null|array $weights
     * @param null|int   $type
     *
     * @throws \Exception
     *
     * @return null|Store
     */
    public function modify(?array $children = null, ?array $weights = null, ?int $type = null): ?Store
    {
        // @todo implement Element:modify()

        // shall work as /element/replace
        $element_type = $type;

        if (null === $element_type) {
            $element_type = $this->getElementType(true);
        }

        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getDimension()->getOlapObjectId(),
                'element' => $this->getOlapObjectId(),
                // 'new_name' => ...,
                'type' => $element_type,
                // 'children' => ,
                // 'weights' =>
            ],
        ];

        // it's a consolidated element but no children were given
        // set current children
        if (null === $children && self::ELEMENT_TYPE_CONSOLIDATED === $element_type) {
            $children = $this->getChildrenIds();
        }

        if (null !== $children) {
            if (null === $weights) {
                $weights = \array_fill(0, \count($children), self::ELEMENT_TYPE_NUMERIC);
            }

            $params['query']['children'] = \implode(',', $children);
            $params['query']['weights'] = \implode(',', $weights);
            $params['query']['type'] = self::ELEMENT_TYPE_CONSOLIDATED; // consolidated
        }

        return $this->getDatabase()->getConnection()->request(self::API_ELEMENT_REPLACE, $params);
    }

    /**
     * @param null|Element $fromParent
     * @param null|Element $toParent
     *
     * @throws \Exception
     */
    public function move(?Element $fromParent = null, ?Element $toParent = null): void
    {
        if (null !== $toParent) {
            $toParent->addChild($this);
        }

        if (null !== $fromParent) {
            $fromParent->detachChildren([$this->getName()]);
        }
    }

    /**
     * @throws \Exception
     *
     * @return Element
     */
    public function reload(): self
    {
        return $this->getConnection()
            ->getDatabaseById($this->getDatabase()->getOlapObjectId())
            ->getDimensionById($this->getDimension()->getOlapObjectId())
            ->getElementById($this->getOlapObjectId())
        ;
    }

    /**
     * @param null|bool $delete false by default
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function removeHierarchy(?bool $delete = null): bool
    {
        $delete = $delete ?? false;

        return $this->getDimension()->removeHierarchy($this, $delete);
    }

    /**
     * @throws \Exception
     *
     * @return Store
     */
    public function replaceBulk(): Store
    {
        // @todo Element::replaceBulk()
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getDimension()->getOlapObjectId(),
                'name_elements' => $this->getOlapObjectId(),
                // 'type' => ,
                // 'types' =>
                // 'children' => ,
                // 'weights' =>
            ],
        ];

        return $this->getDatabase()->getConnection()->request(self::API_ELEMENT_REPLACE_BULK, $params);
    }

    /**
     * @param null|bool $recursive
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function unconsolidate(?bool $recursive = null): bool
    {
        if ($this->isBaseElement()) {
            return false;
        }

        $recursive = $recursive ?? false;

        if ($recursive) {
            foreach ($this->getChildren() as $child) {
                $child->unconsolidate($recursive);
            }
        }

        // remove all children
        return $this->detachChildren();
    }
}
