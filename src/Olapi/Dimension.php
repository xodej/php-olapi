<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use Xodej\Olapi\ApiRequestParams\ApiCellExport;
use Xodej\Olapi\ApiRequestParams\ApiDimensionClear;
use Xodej\Olapi\ApiRequestParams\ApiDimensionDfilter;
use Xodej\Olapi\ApiRequestParams\ApiDimensionElements;
use Xodej\Olapi\ApiRequestParams\ApiDimensionGenerateScript;
use Xodej\Olapi\ApiRequestParams\ApiDimensionInfo;
use Xodej\Olapi\ApiRequestParams\ApiElementAppend;
use Xodej\Olapi\ApiRequestParams\ApiElementCreate;
use Xodej\Olapi\ApiRequestParams\ApiElementCreateBulk;
use Xodej\Olapi\ApiRequestParams\ApiElementDestroy;
use Xodej\Olapi\ApiRequestParams\ApiElementDestroyBulk;
use Xodej\Olapi\ApiRequestParams\ApiElementReplaceBulk;
use Xodej\Olapi\Filter\DataFilter;

/**
 * Class Dimension.
 */
class Dimension implements IBase
{
    private Database $database;
    /**
     * @var ElementCollection<Element>
     */
    private ElementCollection $elements;

    /**
     * @var null|array<int,array<string>>
     */
    private ?array $elementLookupByID = null;

    /**
     * @var null|array<string,int>
     */
    private ?array $elementLookupByName = null;

    /**
     * @var string[]
     */
    private array $metaInfo;

    /**
     * Dimension constructor.
     *
     * @param Database $database database object
     * @param string[] $metaInfo array with meta information about the dimension
     *
     * @throws \Exception
     */
    public function __construct(Database $database, array $metaInfo)
    {
        $this->database = $database;
        $this->metaInfo = $metaInfo;

        $this->elements = new ElementCollection();

        $this->listElements(false);
    }

    /**
     * Adds a new element to a dimension.
     *
     * @param string      $elementName          element name
     * @param null|string $parent_element       parent element name
     * @param null|int    $element_type         element type Element::TYPE_x
     * @param null|float  $consolidation_factor consolidation factor
     *
     * @throws \Exception
     *
     * @return array<string>
     */
    public function addElement(
        string $elementName,
        ?string $parent_element = null,
        ?int $element_type = null,
        ?float $consolidation_factor = null
    ): array {
        // @todo implement Dimension::addElement()
        $element_type = $element_type ?? Element::TYPE_NUMERIC;
        $consolidation_factor = $consolidation_factor ?? 1.0;

        $request = new ApiElementCreate();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->new_name = $elementName;
        $request->type = $element_type;
        $request->squash_list = true;

        $response = $this->getConnection()->request($request);

        if (null !== $parent_element) {
            // @todo Dimension::addElement() fetch parent element and append new Element to parent
            // if parent element is a Base element - do not consolidate you may loose data
            // $this->appendElement($parent_element);
        }

        return $response[0];
    }

    /**
     * Append child element to parent node.
     *
     * @param string $element_name element name
     *
     * @throws \Exception
     *
     * @return GenericCollection
     */
    public function appendElement(string $element_name): GenericCollection
    {
        // @todo implement Dimension::appendElement()

        $request = new ApiElementAppend();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->element = $this->getElementIdFromName($element_name);

        // $params->children = ;
        // $params->weights = ;

        // if parent element is a Base element - do not consolidate you may loose data
        return $this->getConnection()->request($request);
    }

    /**
     * @throws \Exception
     *
     * @return \SimpleXMLElement
     */
    public function asXml(): \SimpleXMLElement
    {
        $dim_sxe = new \SimpleXMLElement('<dimension />');

        $dim_sxe->addAttribute('name', $this->getName());
        $dim_sxe->addAttribute('id', (string) $this->getOlapObjectId());

        // iterate over elements and add attributes
        $elements = $this->getAllElements();
        foreach ($elements as $element) {
            $element_id = (int) $element[0];
            $element = $this->getElementById($element_id);
            $attributes = $element->getAttributes();

            $e_xml = $this->getElementById($element_id)->asXml();
            foreach ($attributes as $attribute) {
                $e_xml->addAttribute($attribute[0].'@'.$attribute[2], $attribute[3]);
            }
            Util::simpleXmlAppend($dim_sxe, $e_xml);
        }

        return $dim_sxe;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function asXmlString(): string
    {
        return Util::simpleXmlBeautify($this->asXml());
    }

    /**
     * Clears a dimension by removing all elements within the dimension. The dimension itself remains,
     * all associated cubes are also cleared because of the deleted elements.
     *
     * @param null|int $type Optional - Clear only elements of specified type (1=NUMERIC, 2=STRING, 4=CONSOLIDATED)
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function clear(?int $type = null): bool
    {
        $request = new ApiDimensionClear();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();

        if (null !== $type) {
            $request->type = $type;
        }

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Create new dimension.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function create(): bool
    {
        return $this->getDatabase()->createDimension($this->getName());
    }

    /**
     * Create a new element.
     *
     * @param string        $element_name element name
     * @param null|int      $element_type element type with default 1 = numeric
     * @param null|string[] $children     array of child element names
     * @param null|float[]  $weights      weights with default 1
     *
     * @example $d->createElement('New Element Name', ELEMENT::TYPE_NUMERIC, ['child1','child2','child3'], [0.5,-1,1]);
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createElement(
        string $element_name,
        ?int $element_type = null,
        ?array $children = null,
        ?array $weights = null
    ): bool {
        $element_type = $element_type ?? 1;
        $children = $children ?? [];
        $weights = $weights ?? [];

        return $this->createElements([$element_name, $element_type, $children, $weights]);
    }

    /**
     * Create a set of elements
     * only existing children are allowed otherwise use /element/replace.
     *
     * @param array<int, array<int, mixed>> $elements list of arrays defining new elements
     *
     * @example $d->createElements([['New Element Name', ELEMENT::TYPE_NUMERIC, ['child1','child2','child3'], [0.5,-1,1]], [..], ..]);
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createElements(array $elements): bool
    {
        $element_names = [];
        $weights = [];
        $types = [];
        $children = [];
        foreach ($elements as $element) {
            $element_names[] = $element[0];
            $element_type = $element[1] ?? 1;

            // if children are set
            if (isset($element[2][0])) {
                $element_type = 4;
                $children[] = \implode(',', $element[2]);

                // fetch weight per child
                $tmp_weights = [];
                foreach ((array) $element[2] as $child_index => $child) {
                    $tmp_weights[] = $element[3][$child_index] ?? 1;
                }
                $weights[] = \implode(',', $tmp_weights);
            }

            $types[] = $element_type;
        }

        // send create API request
        $request = new ApiElementCreateBulk();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->name_elements = \implode(',', $element_names);
        $request->name_children = \implode(':', $children);
        $request->types = \implode(',', $types);
        $request->weights = \implode(':', $weights);

        $response = $this->getConnection()->request($request);

        if (0 === $response->count()) {
            return false;
        }

        return (bool) ($response[0] ?? false);
    }

    /**
     * Delete dimension, which impacts all cubes related to this dimension.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->getDatabase()->deleteDimension($this->getName());
    }

    /**
     * Deletes element from dimension.
     *
     * @param string $element_name element name
     *
     * @throws \Exception
     *
     * @return bool
     *
     * @see Dimension::deleteElementByName() alias
     */
    public function deleteElement(string $element_name): bool
    {
        return $this->deleteElementByName($element_name);
    }

    /**
     * Delete elements by element IDs.
     *
     * @param int[] $element_ids array of element IDs
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteElementBulkByIds(array $element_ids): bool
    {
        $element_ids = \array_map(static function ($v) {
            if (!\is_numeric($v)) {
                throw new \InvalidArgumentException('element list contains non numeric value '.$v);
            }

            return (int) $v;
        }, $element_ids);

        $request = new ApiElementDestroyBulk();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->elements = \implode(',', $element_ids);

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Delete elements by element names.
     *
     * @param string[] $element_names array of element names
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteElementBulkByNames(array $element_names): bool
    {
        $element_ids = \array_map(static function (string $e) {
            return $this->getElementIdFromName($e);
        }, $element_names);

        return $this->deleteElementBulkByIds($element_ids);
    }

    /**
     * Delete element by element ID.
     *
     * @param int $element_id element id
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteElementById(int $element_id): bool
    {
        if (!$this->hasElementById($element_id)) {
            throw new \InvalidArgumentException('given element ID '.$element_id.' not found');
        }

        $request = new ApiElementDestroy();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->element = $element_id;

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0][0] ?? false);
    }

    /**
     * Delete element by element name.
     *
     * @param string $element_name element name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteElementByName(string $element_name): bool
    {
        if (!$this->hasElementByName($element_name)) {
            throw new \InvalidArgumentException('given element name '.$element_name.' not found');
        }

        return $this->deleteElementById($this->getElementIdFromName($element_name));
    }

    /**
     * Filter dimension elements by OLAP dfilter expression.
     *
     * @param string                   $cube_name cube name
     * @param null|int                 $mode      one of the Dimension::DFILTER_X modes
     * @param null|Area                $area      area object
     * @param null|string              $condition Condition on the value of numeric or string cells (default is no condition). A condition starts with >, >=, <, <=, ==, or != and is followed by a double or a string. Two condition can be combined by and, or, xor. If you specify a string value, the value has to be csv encoded. Do not forget to URL encode the complete condition string.
     * @param null|float               $values    values for Top, Upper % and Lower % in this order
     * @param null|ApiDimensionDfilter $request   array of options
     *
     * @throws \Exception
     *
     * @return GenericCollection
     */
    public function dfilter(
        string $cube_name,
        ?int $mode = null,
        ?Area $area = null,
        ?string $condition = null,
        ?float $values = null,
        ?ApiDimensionDfilter $request = null
    ): GenericCollection {
        $request ??= new ApiDimensionDfilter();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->mode = $mode ?? DataFilter::FLAG_ONLY_LEAVES;

        // @todo Dimension::dfilter() strtolower() for cube_name required?
        if (!\in_array($cube_name, $this->listCubes(), true)) {
            throw new \InvalidArgumentException('Dimension::dfilter() cube name '.$cube_name.' not found');
        }

        $cube = $this->getDatabase()->getCubeByName($cube_name);
        $request->cube = $cube->getOlapObjectId();

        $area ??= new Area($cube);
        $request->area = $area->getArea();

        $request->condition = $condition ?? null;
        $request->values = $values ?? null;

        return $this->getConnection()->request($request);
    }

    /**
     * @param null|ApiDimensionGenerateScript $request
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return string
     */
    public function exportAsScript(?ApiDimensionGenerateScript $request = null): string
    {
        $request ??= new ApiDimensionGenerateScript();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();

        $request->complete ??= 1;
        $request->show_attribute ??= true;
        $request->languages ??= '*';

        /** @var resource $stream_resource */
        if (null === ($stream_resource = $this->getConnection()
            ->requestRaw($request->url(), $request->asArray()))) {
            throw new \ErrorException('failed to establish stream resource in Dimension::exportAsScript()');
        }

        return (string) \stream_get_contents($stream_resource);
    }

    /**
     * Returns an array with all base elements of the dimension.
     *
     * @throws \Exception
     *
     * @return array<string>
     */
    public function getAllBaseElements(): array
    {
        return $this->getBaseElementsOfNode();
    }

    /**
     * Returns an array with consolidated elements below a certain element
     * if no element name or null is given, all consolidated elements are returned.
     *
     * @param null|string $node element
     *
     * @throws \Exception
     *
     * @return array<string>
     */
    public function getAllConsolidatedElements(?string $node = null): array
    {
        return $this->getConsolidatedElementsOfNode($node);
    }

    /**
     * Returns a list of all elements of the dimension.
     *
     * @throws \Exception
     *
     * @return GenericCollection<array<string>>
     */
    public function getAllElements(): GenericCollection
    {
        return $this->getElementListOfNode();
    }

    /**
     * Returns the dimensions attribute cube object.
     *
     * @throws \Exception
     *
     * @return Cube
     */
    public function getAttributeCube(): Cube
    {
        if (preg_match('~^#_~', $this->getName())) {
            throw new \DomainException('Dimension::getAttributeCube() not supported by dimension '.$this->getName());
        }
        $attribute_cube = '#_'.$this->getName();

        return $this->getDatabase()->getCubeByName($attribute_cube);
    }

    /**
     * Returns a list of attributes used in the dimension.
     *
     * @throws \Exception
     *
     * @return GenericCollection<array<string>>
     */
    public function getAttributeList(): GenericCollection
    {
        if (preg_match('~^#_~', $this->getName())) {
            throw new \DomainException('Dimension::AttributeList() not supported by dimension '.$this->getName());
        }

        $attribute_dimension_name = '#_'.$this->getName().'_';

        return $this->getDatabase()->getDimensionByName($attribute_dimension_name)->getAllElements();
    }

    /**
     * Returns a list of attributes based on given elements and attributes.
     *
     * @param null|string[] $element_names   array of element names
     * @param null|string[] $attribute_names array of attributes
     * @param null|bool     $show_headers    show headers
     *
     * @throws \Exception
     *
     * @return null|array<int|string,array<int|string,array<int|string,mixed>>>
     */
    public function getAttributes(
        ?array $element_names = null,
        ?array $attribute_names = null,
        ?bool $show_headers = null
    ): ?array {
        if ('System' === $this->getDatabase()->getName()) {
            throw new \DomainException('System database does not support attributes.');
        }
        if (preg_match('~^#_~', $this->getName())) {
            throw new \DomainException('Dimension::getAttributes() not supported by dimension '.$this->getName());
        }

        $show_headers = $show_headers ?? false;

        $attribute_cube = $this->getAttributeCube();

        // no filters (default)
        $request = null;

        // apply filters from parameters if necessary
        if (null !== $element_names || null !== $attribute_names) {
            $area = new Area($attribute_cube);

            if (null !== $element_names) {
                $area->addElements($this->getName(), $element_names);
            }

            if (null !== $attribute_names) {
                $attribute_dimension_name = '#_'.$this->getName().'_';
                $area->addElements($attribute_dimension_name, $attribute_names);
            }

            $request = new ApiCellExport();
            $request->area = $area->getArea();
        }

        // fetch data from cube
        $attributes = $attribute_cube->arrayExport($request, true, null, 100000);

        // fetch indexes of table headers
        $keys = \array_flip($attributes[0]);
        $key_elem = $keys[$this->getName()];
        $key_attr = $keys['#_'.$this->getName().'_'];
        $key_lang = $keys['#_LANGUAGE'];
        $key_value = $keys['#VALUE'];

        // remove header if requested
        if (!$show_headers) {
            unset($attributes[0]);
        }

        // prepare return-data-set
        $return = [];
        foreach ($attributes as $line) {
            $return[$line[$key_elem]][$line[$key_attr]][$line[$key_lang]] = $line[$key_value];
        }

        return $return;
    }

    /**
     * Returns array of base elements.
     *
     * @param null|string $element_name element name
     * @param null|string $filter       regex-pattern to filter element names
     *
     * @throws \Exception
     *
     * @return string[]
     */
    public function getBaseElementsOfNode(?string $element_name = null, ?string $filter = null): array
    {
        $return = $this->basifyElementList($this->getElementListOfNode($element_name));

        if (null === $filter) {
            return $return;
        }

        return \array_filter($return, static function (string $e) use ($filter): bool {
            return 1 === \preg_match($filter, $e);
        });
    }

    /**
     * Returns an array of child elements of given element as objects.
     *
     * @param string $element_name element name
     *
     * @throws \Exception
     *
     * @return Element[]
     */
    public function getChildrenOfNode(string $element_name): array
    {
        if (!$this->hasElementByName($element_name)) {
            throw new \ErrorException('unknown element '.$element_name.' in dimension '.$this->getName());
        }

        return $this->getElementByName($element_name)->getChildren();
    }

    /**
     * @throws \Exception
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->getDatabase()->getConnection();
    }

    /**
     * Returns array of consolidated elements.
     *
     * @param null|string $node   element name
     * @param null|string $filter regex-pattern to filter element names
     *
     * @throws \Exception
     *
     * @return array<string>
     */
    public function getConsolidatedElementsOfNode(?string $node = null, ?string $filter = null): array
    {
        $result = $this->consolifyElementList($this->getElementListOfNode($node));

        if (null === $filter) {
            return $result;
        }

        return \array_filter($result, static function (string $e) use ($filter): bool {
            return 1 === \preg_match($filter, $e);
        });
    }

    /**
     * Returns database object containing dimension.
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * @param null|bool $live
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getDimensionToken(?bool $live = null): string
    {
        $live = (bool) $live;

        if (!$live) {
            return (string) $this->metaInfo[10];
        }

        $info = $this->info();
        if (!isset($info[10])) {
            throw new \ErrorException('failed Dimension::getDimensionsToken() - live call to OLAP failed');
        }

        return (string) $info[10];
    }

    /**
     * @param string $element_name element name
     *
     * @throws \Exception
     *
     * @return string[]
     *
     * @see Dimension::testDuplicateDescendants()
     */
    public function getDuplicateBaseElementsOfNode(string $element_name): array
    {
        // @todo does not find duplicates
        $base_elements = $this->traverse($element_name, 1, false);

        $base_elements = \array_map(static function (array $v) {
            return $v['name'];
        }, $base_elements);

        $non_unique_base_elements = [];
        $counts_per_base_element = \array_count_values($base_elements);

        foreach ($counts_per_base_element as $base_element => $counts) {
            if ($counts > 1) {
                $non_unique_base_elements[] = $base_element;
            }
        }

        return $non_unique_base_elements;
    }

    /**
     * Returns element object by element name.
     *
     * @param string $element_name element name
     *
     * @throws \Exception
     *
     * @return Element
     *
     * @see Dimension::getElementByName() alias
     */
    public function getElement(string $element_name): Element
    {
        return $this->getElementByName($element_name);
    }

    /**
     * Returns element object by element ID.
     *
     * @param int $eId element ID
     *
     * @throws \Exception
     *
     * @return Element|Group|User
     */
    public function getElementById(int $eId): Element
    {
        if (!isset($this->elementLookupByID[$eId])) {
            throw new \InvalidArgumentException('Unknown element id '.$eId.' given.');
        }

        if (isset($this->elements[$eId])) {
            return $this->elements[$eId];
        }

        // return special objects within system database
        if ('System' === $this->getDatabase()->getName()) {
            switch ($this->getName()) {
                case '#_USER_':
                    $this->elements[$eId] = new User($this, $this->getElementListRecordById($eId));

                    return $this->elements[$eId];
                case '#_GROUP_':
                    $this->elements[$eId] = new Group($this, $this->getElementListRecordById($eId));

                    return $this->elements[$eId];
                case '#_ROLE_':
                    $this->elements[$eId] = new Role($this, $this->getElementListRecordById($eId));

                    return $this->elements[$eId];
            }
        }

        $this->elements[$eId] = new Element($this, $this->getElementListRecordById($eId));

        return $this->elements[$eId];
    }

    /**
     * Returns element object by element ID.
     *
     * @param string $eName
     *
     * @throws \Exception
     * @throws \ErrorException
     *
     * @return Element
     */
    public function getElementByName(string $eName): Element
    {
        if (!isset($this->elementLookupByName[\strtolower($eName)])) {
            throw new \ErrorException('unknown element '.$eName);
        }

        $dim_el = $this->elementLookupByName[\strtolower($eName)];

        return $this->getElementById($dim_el);
    }

    /**
     * @param string $element_name
     *
     * @throws \Exception
     *
     * @return int
     */
    public function getElementIdFromName(string $element_name): int
    {
        if (!$this->hasElementByName($element_name)) {
            throw new \InvalidArgumentException('ID for unknown element `'.$element_name.'` from dimension '.
                $this->getName().' requested.');
        }

        return $this->elementLookupByName[\strtolower($element_name)];
    }

    /**
     * @param null|int $fromDepth default -1
     * @param null|int $toDepth   default 0
     *
     * @throws \Exception
     *
     * @return array<int,array<string,string>>
     */
    public function getElementListByDepth(?int $fromDepth = null, ?int $toDepth = null): array
    {
        $from_depth = $fromDepth ?? -1;
        $to_depth = $toDepth ?? 0;

        // /dimension/elements?show_lock_info=1&database=20&dimension=13&limit=0
        $elements = $this->getElementListOfNode();

        $elements = \array_filter($elements->getArrayCopy(), static function ($v) use ($from_depth, $to_depth) {
            // 5 = depth
            return $v[5] >= $from_depth && $v[5] <= $to_depth;
        });

        $return = [];
        foreach ($elements as $element) {
            // @var array $element
            $return[] = [
                'name' => $element[1], // 1 = name
                'type' => $element[6], // 6 = type
                'identifier' => $element[0], // 0 = identifier
            ];
        }

        return $return;
    }

    /**
     * @param null|string $element_name
     *
     * @throws \Exception
     *
     * @return GenericCollection<array<string>>
     */
    public function getElementListOfNode(?string $element_name = null): GenericCollection
    {
        $request = new ApiDimensionElements();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->show_permission = true;

        // @todo what is the purpose of Dimension::getElementListOfNode()? what is it supposed to do?
        // @todo Dimension::getElementListOfNode() needs OlapIdentifier:: support
        if (null === $element_name) {
            return $this->getConnection()->request($request);
        }

        return $this->fullTraverse($element_name);
    }

    /**
     * @param string $element_name
     *
     * @throws \Exception
     *
     * @return array<string>
     */
    public function getElementListRecord(string $element_name): array
    {
        return $this->getElementListRecordByName($element_name);
    }

    /**
     * @param int $element_id
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return array<string>
     */
    public function getElementListRecordById(int $element_id): array
    {
        if (!$this->hasElementById($element_id)) {
            throw new \InvalidArgumentException('Unknown element ID '.$element_id.' given.');
        }

        return $this->elementLookupByID[$element_id];
    }

    /**
     * @param string $element_name
     *
     * @throws \Exception
     *
     * @return array<string>
     */
    public function getElementListRecordByName(string $element_name): array
    {
        if (!$this->hasElementByName($element_name)) {
            throw new \InvalidArgumentException('Unknown element name '.$element_name.' given.');
        }

        return $this->getElementListRecordById($this->getElementIdFromName($element_name));
    }

    /**
     * @param int $elementId
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getElementNameFromId(int $elementId): string
    {
        // unknown elementID requested
        if (!isset($this->elementLookupByID[$elementId][1])) {
            throw new \InvalidArgumentException('Name for unknown element ID '.$elementId.' from dimension '.
                $this->getName().' requested.');
        }

        return $this->elementLookupByID[$elementId][1];
    }

    /**
     * @return null|string
     */
    public function getFirstElement(): ?string
    {
        if (null !== ($first_key = \array_key_first($this->elementLookupByID))) {
            return (string) $this->elementLookupByID[$first_key][1];
        }

        return null;
    }

    /**
     * @param Database $database
     * @param string   $dimension_name
     *
     * @throws \Exception
     *
     * @return Dimension
     */
    public static function getInstance(Database $database, string $dimension_name): Dimension
    {
        return $database->getDimensionByName($dimension_name);
    }

    /**
     * @return int
     */
    public function getMaxLevel(): int
    {
        return (int) $this->metaInfo[3];
    }

    /**
     * @return int
     */
    public function getMaxIndent(): int
    {
        return (int) $this->metaInfo[4];
    }

    /**
     * @return int
     */
    public function getMaxDepth(): int
    {
        return (int) $this->metaInfo[5];
    }

    /**
     * Returns the name of the dimension.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->metaInfo[1];
    }

    /**
     * Returns Jedox OLAP internal object ID (integer).
     *
     * @return int
     */
    public function getOlapObjectId(): int
    {
        return (int) $this->metaInfo[0];
    }

    /**
     * Create a parent child list for given node.
     *
     * @param null|string|string[] $nodes
     * @param null|int             $level
     *
     * @throws \Exception
     *
     * @return array<int,array<int,string>>
     */
    public function getParentChildListOfNode($nodes = null, ?int $level = null): array
    {
        $level = $level ?? 0;
        $nodes = (array) ($nodes ?? $this->getTopElements());

        $result = [[]];

        // iterate over given elements
        foreach ($nodes as $element_name) {
            // fetch element record and retrieve child ids
            $element_record = $this->getElementListRecordByName($element_name);
            $element_children_ids = \explode(',', $element_record[10]);

            // iterate over child ids and recursively scan for their children
            foreach ($element_children_ids as $element_child_id) {
                $element_child_record = $this->getElementListRecordById((int) $element_child_id);
                $conso_factor = $this->getElementById((int) $element_child_id)
                    ->getConsolidationFactor($this->getElementByName($element_name))
                ;
                $result[][] = [(string) $element_record[1], (string) $element_child_record[1], (float) $conso_factor];

                if (Element::TYPE_CONSOLIDATED === (int) $element_child_record[6]) {
                    $result[] = $this->getParentChildListOfNode($element_child_record[1], $level + 1);

                    continue;
                }
            }
        }

        // merge result sets
        return \array_merge(...$result);
    }

    /**
     * @throws \Exception
     *
     * @return string[]
     */
    public function getTopElements(): array
    {
        $elem_list = $this->getElementListByDepth(-1, 0);

        $elem_list = \array_map(static function (array $e) {
            return $e['name'];
        }, $elem_list);

        return $elem_list;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return (int) $this->metaInfo[6];
    }

    /**
     * @param Element $node
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getXml(Element $node): string
    {
        return $this->getXmlNode($node);
    }

    /**
     * @param string $element_name
     *
     * @return bool
     *
     * @see Dimension::hasElementByName() alias
     */
    public function hasElement(string $element_name): bool
    {
        return $this->hasElementByName($element_name);
    }

    /**
     * @param int $element_id
     *
     * @return bool
     */
    public function hasElementById(int $element_id): bool
    {
        return isset($this->elementLookupByID[$element_id]);
    }

    /**
     * @param string $element_name
     *
     * @return bool
     */
    public function hasElementByName(string $element_name): bool
    {
        return isset($this->elementLookupByName[\strtolower($element_name)]);
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
     * @throws \Exception
     *
     * @return string[]
     */
    public function listCubes(): array
    {
        $return = [];
        foreach ($this->getDatabase()->listCubes() as $cube_record) {
            if (\in_array($this->getOlapObjectId(), \explode(',', $cube_record[3]), false)) {
                $return[$cube_record[0]] = $cube_record[1];
            }
        }

        return $return;
    }

    /**
     * @param null|bool                       $cached
     * @param null|ApiDimensionElements $request
     *
     * @throws \Exception
     *
     * @return null|array<int,array<string>>
     */
    public function listElements(?bool $cached = null, ?ApiDimensionElements $request = null): ?array
    {
        $cached = $cached ?? true;

        if (true === $cached && null !== $this->elementLookupByID) {
            return $this->elementLookupByID;
        }

        $request ??= new ApiDimensionElements();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->show_permission = true;

        $element_list = $this->getConnection()->request($request);

        $this->elements = new ElementCollection();
        $this->elementLookupByID = [];
        $this->elementLookupByName = [];

        foreach ($element_list as $element_row) {
            $this->elementLookupByID[(int) $element_row[0]] = $element_row;
            $this->elementLookupByName[\strtolower($element_row[1])] = (int) $element_row[0];
        }

        return $this->elementLookupByID;
    }

    /**
     * @throws \Exception
     *
     * @return Dimension
     */
    public function reload(): self
    {
        return $this->getDatabase()
            ->getDimensionById($this->getOlapObjectId(), false)
        ;
    }

    /**
     * @param Element                $node
     * @param null|bool              $delete
     * @param null|bool              $force
     * @param null|int               $level
     * @param null|GenericCollection $remove_collection
     * @param null|GenericCollection $tree_elements
     * @param null|GenericCollection $blacklist
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function removeHierarchy(
        Element $node,
        ?bool $delete = null,
        ?bool $force = null,
        ?int $level = null,
        ?GenericCollection $remove_collection = null,
        ?GenericCollection $tree_elements = null,
        ?GenericCollection $blacklist = null
    ): bool {
        $force = $force ?? false;
        $delete = $delete ?? false;
        $level = $level ?? 0;
        $remove_collection = $remove_collection ?? new GenericCollection();
        $tree_elements = $tree_elements ?? new GenericCollection();
        $blacklist = $blacklist ?? new GenericCollection();

        // you don't need to de-consolidate base elements
        if ($node->isBaseElement()) {
            return false;
        }

        // if node is in blacklist skip it
        if (isset($blacklist[$node->getOlapObjectId()])) {
            return false;
        }

        // if force mode is disabled do additional checks before remove
        if (!$force) {
            // load all tree elements for additional checks
            if (0 === $level) {
                \array_map(static function ($descendant) use ($tree_elements) {
                    if ('consolidated' === $descendant['type']) {
                        $tree_elements[(int) $descendant['identifier']] = $descendant;
                    }
                }, $node->getDescendants());
            }

            // if node has more than 1 parent, remove hierarchy only
            // if both parents are under the given root tree otherwise
            // skip node for de-consolidation
            if ($node->countParents() > 1) {
                // iterate over parents and check if in tree
                foreach ($node->getParents() as $parent) {
                    if (!isset($tree_elements[$parent->getOlapObjectId()])) {
                        \file_put_contents('php://stderr', 'node '.$node->getName().
                            ' is required by a different tree and is therefore skipped. Try force mode.');
                        // add all nodes of sub-tree to blacklist
                        \array_map(static function ($descendant) use (&$blacklist) {
                            if ('consolidated' === $descendant['type']) {
                                $blacklist[(int) $descendant['identifier']] = $descendant;
                            }
                        }, $node->getDescendants());

                        return false;
                    }
                }
            }
        }

        // iterate over children
        $children = $node->getChildren();
        foreach ($children as $child) {
            if ($child->hasChildren()) {
                $this->removeHierarchy(
                    $child,
                    $delete,
                    $force,
                    $level + 1,
                    $remove_collection,
                    $tree_elements,
                    $blacklist
                );
            }
        }

        // add node to the collection which can be safely de-consolidated
        $remove_collection[] = $node;

        if (0 !== $level) {
            return false;
        }

        $request = null;
        if (!$delete) {
            $request = new ApiElementReplaceBulk();
            $request->database = $this->getDatabase()->getOlapObjectId();
            $request->dimension = $this->getOlapObjectId();
            $request->elements = \implode(',', \array_map(static function (Element $v) {
                return $v->getOlapObjectId();
            }, $remove_collection->getArrayCopy()));
            $request->type = Element::TYPE_NUMERIC;
        }

        if ($delete) {
            $request = new ApiElementDestroyBulk();
            $request->database = $this->getDatabase()->getOlapObjectId();
            $request->dimension = $this->getOlapObjectId();
            $request->elements = \implode(',', \array_map(static function (Element $v) {
                return $v->getOlapObjectId();
            }, $remove_collection->getArrayCopy()));
        }

        if (null === $request) {
            throw new \ErrorException('Internal logic error - parameter definition missing');
        }

        $response = $this->getConnection()->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param Element|string $element
     * @param null|bool      $full_path
     *
     * @throws \Exception
     *
     * @return null|array
     */
    public function showParents($element, ?bool $full_path = null): ?array
    {
        $full_path = $full_path ?? true;
        if (!($element instanceof Element)) {
            $element = $this->getElementByName($element);
        }

        $return = new GenericCollection();
        $this->internShowParents($element, $return, $full_path);

        return $return->getArrayCopy();
    }

    /**
     * Returns an array{string} of duplicate elements of a node.
     *
     * @param string $element_name element name / node name
     *
     * @throws \Exception
     *
     * @return string[]
     */
    public function testDuplicateDescendants(string $element_name): array
    {
        $list_of_descendants = $this->traverse($element_name, 3, false);

        $result = [];
        $doubles = [];
        foreach ($list_of_descendants as $descendant) {
            if (isset($result[$descendant['id']])) {
                $doubles[] = (string) $descendant;

                continue;
            }
            $result[$descendant['id']] = true;
        }

        return $doubles;
    }

    /**
     * Simulated palo_list_ancestors() to get double
     * base elements instead of a list of unique ancestors.
     *
     * Fetch modes
     * 1 - Base elements only
     * 2 - consolidated only
     * 3 - fetch all (default)
     *
     * @param string    $element_name      dimension element name or node name
     * @param null|int  $fetch_mode        (1 - base elements|2 - consolidated elements|3 - all = default)
     * @param null|bool $remove_duplicates remove duplicates (default=true)
     *
     * @throws \Exception
     *
     * @return array
     */
    public function traverse(
        string $element_name,
        ?int $fetch_mode = null,
        ?bool $remove_duplicates = null
    ): array {
        $fetch_mode = $fetch_mode ?? 3;
        $remove_duplicates = $remove_duplicates ?? true;

        $store = $this->fullTraverse($element_name, $fetch_mode, null, null, $remove_duplicates);

        $return = [];
        foreach ($store as $elem) {
            $return[] = [
                'id' => $elem[0],
                'name' => $this->getElementNameFromId((int) $elem[0]),
                'type' => Element::getTypeNameFromTypeNumber((int) $elem[6]),
            ];
        }

        return $return;
    }

    /**
     * As a user please use Dimension::traverse() instead.
     *
     * @param string                 $element_name      dimension element name or node name
     * @param null|int               $fetch_mode        (1 - base elements|2 - consolidated elements|3 - all = default)
     * @param null|GenericCollection $result            internally used for the result set (users should always use null)
     * @param null|int               $level             internally used for level of recursion (users should always use null)
     * @param null|bool              $remove_duplicates remove duplicate nodes
     *
     * @throws \Exception
     *
     * @return GenericCollection
     *
     * @see Dimension::traverse()
     */
    public function fullTraverse(
        string $element_name,
        ?int $fetch_mode = null,
        ?GenericCollection $result = null,
        ?int $level = null,
        ?bool $remove_duplicates = null
    ): GenericCollection {
        $fetch_mode = $fetch_mode ?? 3;
        $result = $result ?? new GenericCollection();
        $level = $level ?? 0;
        $remove_duplicates = $remove_duplicates ?? true;

        $element_record = $this->getElementListRecordByName($element_name);

        // don't run on already known elements
        if ($remove_duplicates && isset($result[(int) $element_record[0]])) {
            return $result;
        }

        // iterate over children of consolidated element
        if (Element::TYPE_CONSOLIDATED === (int) $element_record[6]) {
            // collect if not "only Base elements"
            if (1 !== $fetch_mode) {
                if ($remove_duplicates) {
                    $result[(int) $element_record[0]] = $element_record;
                } else {
                    $result[] = $element_record;
                }
            }

            // fetch children
            $children = \explode(',', $element_record[10]);

            // >>> start: order elements to show base elements first and then consolidated elements
            $base_elements = [];
            $conso_elements = [];
            foreach ($children as $child_element_id) {
                $element_record = $this->getElementListRecordById((int) $child_element_id);
                if (Element::TYPE_CONSOLIDATED === (int) $element_record[6]) {
                    $conso_elements[] = $child_element_id;

                    continue;
                }
                $base_elements[] = $child_element_id;
            }
            $children = \array_merge($base_elements, $conso_elements);
            // <<< end: order elements to show base elements first and then consolidated elements

            // operate on children
            foreach ($children as $child_element_id) {
                $child_element_name = $this->getElementNameFromId((int) $child_element_id);
                $this->fullTraverse($child_element_name, $fetch_mode, $result, $level++, $remove_duplicates);
            }

            return $result;
        }

        // collect if not "only consolidated elements"
        if (2 !== $fetch_mode) {
            if ($remove_duplicates) {
                $result[(int) $element_record[0]] = $element_record;
            } else {
                $result[] = $element_record;
            }
        }

        return $result;
    }

    /**
     * @param string[] $listOfElementNames
     *
     * @return string[]
     */
    public function uniquifyElementList(array $listOfElementNames): array
    {
        // remove duplicate elements
        return \array_unique($listOfElementNames, \SORT_STRING);
    }

    /**
     * @throws \Exception
     *
     * @return GenericCollection<array<string>>
     */
    public function info(): GenericCollection
    {
        $request = new ApiDimensionInfo();
        $request->database = $this->getDatabase()->getOlapObjectId();
        $request->dimension = $this->getOlapObjectId();
        $request->show_permission = true;
        $request->show_counters = true;
        $request->show_default_elements = true;
        $request->show_count_by_type = true;

        return $this->getConnection()->request($request);
    }

    /**
     * @param Element                $element
     * @param null|GenericCollection $return
     * @param null|bool              $full_path
     * @param null|int               $level
     * @param null|string[]          $ancestors
     *
     * @throws \Exception
     *
     * @return array<string>
     */
    protected function internShowParents(
        Element $element,
        ?GenericCollection $return = null,
        ?bool $full_path = null,
        ?int $level = null,
        ?array $ancestors = null
    ): array {
        $ancestors = $ancestors ?? [];
        $level = $level ?? 0;
        $full_path = $full_path ?? true;
        $return = $return ?? new GenericCollection();

        $parents = $element->getParents();
        $ancestors[] = $element->getName();

        $temporary = $ancestors;

        foreach ($parents as $parent) {
            if (true === $full_path) {
                if (0 === $level || \count($parents) > 1) {
                    $return[] = $this->internShowParents($parent, $return, $full_path, $level + 1, $temporary);

                    continue;
                }
                $ancestors = $this->internShowParents($parent, $return, $full_path, $level + 1, $temporary);
            }
        }

        return $ancestors;
    }

    /**
     * Removes all non-base elements from a list of elements.
     *
     * @param GenericCollection<array<string>> $elementList list of dimension elements
     *
     * @return string[]
     *
     * @internal
     */
    private function basifyElementList(GenericCollection $elementList): array
    {
        // remove consolidated elements
        $element_list = \array_filter($elementList->getArrayCopy(), static function (array $e) {
            return !(Element::TYPE_CONSOLIDATED === (int) $e[6]);
        });

        // fetch only element names
        $element_list = \array_map(static function (array $e) {
            return $e[1];
        }, $element_list);

        // reindex array
        return \array_values($element_list);
    }

    /**
     * Removes all base elements from a list of elements.
     *
     * @param GenericCollection<array<string>> $elementList list of dimension elements
     *
     * @throws \Exception
     *
     * @return string[]
     *
     * @internal
     */
    private function consolifyElementList(GenericCollection $elementList): array
    {
        $element_list = $elementList->getArrayCopy();

        // remove Base elements
        $element_list = \array_filter($element_list, static function (array $e) {
            return Element::TYPE_CONSOLIDATED === (int) $e[6];
        });

        // fetch only element names
        $element_list = \array_map(static function (array $e) {
            return $e[1];
        }, $element_list);

        // reindex array
        return \array_values($element_list);
    }

    /**
     * @param Element $node element object
     *
     * @return string
     * @throws \Exception
     *
     * @internal
     */
    private function getXmlNode(Element $node): string
    {
        $return = '<node>'.\htmlentities($node->getName(), \ENT_XML1);

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child_node) {
                $return .= $this->getXmlNode($child_node);
            }
        }
        $return .= '</node>';

        return $return;
    }
}
