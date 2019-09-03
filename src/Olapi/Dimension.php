<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Dimension.
 */
class Dimension implements IBase
{
    public const API_ELEMENT_APPEND = '/element/append';
    public const API_ELEMENT_CREATE = '/element/create';
    public const API_ELEMENT_CREATE_BULK = '/element/create_bulk';
    public const API_ELEMENT_DESTROY = '/element/destroy';
    public const API_ELEMENT_DESTROY_BULK = '/element/destroy_bulk';
    public const API_ELEMENT_INFO = '/element/info';
    public const API_ELEMENT_REPLACE_BULK = '/element/replace_bulk';

    public const API_DIMENSION_CLEAR = '/dimension/clear';
    // public const API_DIMENSION_ELEMENT = '/dimension/element';
    public const API_DIMENSION_ELEMENTS = '/dimension/elements';
    public const API_DIMENSION_GENERATE_SCRIPT = '/dimension/generate_script';
    // public const API_DIMENSION_CUBES = '/dimension/cubes';
    // public const API_DIMENSION_RENAME = '/dimension/rename';
    // public const API_DIMENSION_INFO = '/dimension/info';
    public const API_DIMENSION_DFILTER = '/dimension/dfilter';

    /**
     * @var Database
     */
    private $database;

    /**
     * @var ElementStore
     */
    private $elements;

    /**
     * @var array
     */
    private $elementList;

    /**
     * @var array
     */
    private $metaInfo;

    /**
     * Dimension constructor.
     *
     * @param Database $database
     * @param array    $metaInfo
     *
     * @throws \Exception
     */
    public function __construct(Database $database, array $metaInfo)
    {
        $this->database = $database;
        $this->metaInfo = $metaInfo;

        $this->elements = new ElementStore();

        $this->init();
    }

    /**
     * @param string      $elementName
     * @param null|string $parent_element
     * @param null|int    $element_type
     * @param null|float  $consolidation_factor
     *
     * @throws \Exception
     *
     * @return array
     */
    public function addElement(
        string $elementName,
        ?string $parent_element = null,
        ?int $element_type = null,
        ?float $consolidation_factor = null
    ): array {
        // @todo Dimension::addElement()
        $element_type = $element_type ?? Element::ELEMENT_TYPE_NUMERIC;
        $consolidation_factor = $consolidation_factor ?? 1.0;

        $response = $this->getConnection()->request(self::API_ELEMENT_CREATE, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'new_name' => $elementName,
                'type' => $element_type,
                'children' => '',
                'weights' => '',
                'squash_list' => '1',
            ],
        ]);

        if (null !== $parent_element) {
            $this->appendElement($parent_element);
        }

        // parent element is a Base element - do not consolidate you may loose data
        return $response[0];
    }

    /**
     * @param string $element_name
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function appendElement(string $element_name): Store
    {
        // @todo Dimension::appendElement()
        return $this->getConnection()->request(self::API_ELEMENT_APPEND, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'element' => $this->getElementIdFromName($element_name),
                'children' => '',
                'weights' => '',
            ],
        ]);
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
            Util::simplexml_append($dim_sxe, $e_xml);
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
        return Util::simplexml_beauty_xml($this->asXml());
    }

    /**
     * Clears a dimension by removing all elements within the dimension. The dimension itself remains,
     * however all associated cubes are also cleared.
     *
     * @param null|int $type Optional - Clear only elements of specified type (1=NUMERIC, 2=STRING, 4=CONSOLIDATED)
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function clear(?int $type = null): bool
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
            ],
        ];

        if (null !== $type) {
            $params['query']['type'] = $type;
        }

        $response = $this->getConnection()->request(self::API_DIMENSION_CLEAR, $params);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function create(): bool
    {
        return $this->getDatabase()->createDimension($this->getName());
    }

    /**
     * @param string        $element_name
     * @param null|int      $element_type default 1 = numeric
     * @param null|string[] $children
     * @param null|float[]  $weights      default 1
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
     * createElements([['Element Name', 1, ['child1','child2','child3'], [0.5,-1,1]]])
     * only existing children are allowed otherwise use /element/replace.
     *
     * @param array $elements
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
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'name_elements' => \implode(',', $element_names),
                'types' => \implode(',', $types),
                'name_children' => \implode(':', $children),
                'weights' => \implode(':', $weights),
            ],
        ];

        $response = $this->getConnection()->request(self::API_ELEMENT_CREATE_BULK, $params);

        if (0 === $response->count()) {
            return false;
        }

        return (bool) ($response[0] ?? false);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->getDatabase()->deleteDimension($this->getName());
    }

    /**
     * @param string $element_name
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
     * @param int[] $element_ids
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteElementBulkByIds(array $element_ids): bool
    {
        $element_ids = \array_map(static function ($v) {
            if (!\is_numeric($v)) {
                throw new \ErrorException('element list contains non numeric value '.$v);
            }

            return (int) $v;
        }, $element_ids);

        $response = $this->getConnection()->request(self::API_ELEMENT_DESTROY_BULK, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'elements' => \implode(',', $element_ids),
            ],
        ]);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param string[] $element_names
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteElementBulkByNames(array $element_names): bool
    {
        $this_obj = $this;
        $element_ids = \array_map(static function ($e) use ($this_obj) {
            return $this_obj->getElementIdFromName((string) $e);
        }, $element_names);

        return $this->deleteElementBulkByIds($element_ids);
    }

    /**
     * @param int $element_id
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

        $response = $this->getConnection()->request(self::API_ELEMENT_DESTROY, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'element' => $element_id,
            ],
        ]);

        return (bool) ($response[0][0] ?? false);
    }

    /**
     * @param string $element_name
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
     * @param string      $cube_name | null
     * @param null|Area   $area
     * @param null|int    $mode
     * @param null|string $condition
     * @param null|float  $values
     * @param null|array  $options
     *
     * @throws \Exception
     *
     * @return Store
     */
    public function dfilter(
        ?int $mode = null,
        string $cube_name = null,
        ?Area $area = null,
        ?string $condition = null,
        ?float $values = null,
        ?array $options = null
    ): Store {
        // 256 == ONLY_LEAVES
        $mode = $mode ?? 256;

        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'mode' => $mode,
            ],
        ];

        if (null === $cube_name || !\in_array($cube_name, $this->listCubes(), true)) {
            throw new \Exception();
        }

        $cube = $this->getDatabase()->getCubeByName($cube_name);
        $params['query']['cube'] = $cube->getOlapObjectId();

        if (null === $area) {
            $area = new Area($cube);
        }
        $params['query']['area'] = $area->getArea();

        if (null !== $condition) {
            $params['query']['condition'] = $condition;
        }

        if (null !== $values) {
            $params['query']['values'] = $values;
        }

        if (isset($options['squash_list'])) {
            $params['query']['squash_list'] = $options['squash_list'];
        }

        return $this->getConnection()->request(self::API_DIMENSION_DFILTER, $params);
    }

    /**
     * @param null|array $options
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return string
     */
    public function exportAsScript(?array $options = null): string
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'complete' => $options['complete'] ?? 1,
                'show_attribute' => $options['show_attribute'] ?? 1,
                'languages' => $options['languages'] ?? '*',
            ],
        ];

        if (isset($options['name_elements'])) {
            $params['query']['name_elements'] = $options['name_elements'];
        }

        if (isset($options['elements'])) {
            $params['query']['elements'] = $options['elements'];
        }

        if (isset($options['include_local_subsets'])) {
            $params['query']['include_local_subsets'] = $options['include_local_subsets'];
        }

        if (isset($options['include_global_subsets'])) {
            $params['query']['include_global_subsets'] = $options['include_global_subsets'];
        }

        if (isset($options['include_dimension_rights'])) {
            $params['query']['include_dimension_rights'] = $options['include_dimension_rights'];
        }

        if (isset($options['clear'])) {
            $params['query']['clear'] = $options['clear'];
        }

        if (isset($options['script_create_clause'])) {
            $params['query']['script_create_clause'] = $options['script_create_clause'];
        }

        if (isset($options['script_modify_clause'])) {
            $params['query']['script_modify_clause'] = $options['script_modify_clause'];
        }

        /** @var resource $stream_resource */
        if (null === ($stream_resource = $this->getConnection()
            ->requestRaw(self::API_DIMENSION_GENERATE_SCRIPT, $params))) {
            throw new \ErrorException('failed to establish stream resource');
        }

        return (string) \stream_get_contents($stream_resource);
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getAllBaseElements(): array
    {
        return $this->getBaseElementsOfNode();
    }

    /**
     * @param null|string $node
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getAllConsolidatedElements(?string $node = null): array
    {
        return $this->getConsolidatedElementsOfNode($node);
    }

    /**
     * @throws \Exception
     *
     * @return Store
     */
    public function getAllElements(): Store
    {
        return $this->getElementListOfNode();
    }

    /**
     * @throws \Exception
     *
     * @return Cube
     */
    public function getAttributeCube(): Cube
    {
        $attribute_cube = '#_'.$this->getName();

        return $this->getDatabase()->getCubeByName($attribute_cube);
    }

    /**
     * @throws \Exception
     *
     * @return Store
     */
    public function getAttributeList(): Store
    {
        $attribute_dimension_name = '#_'.$this->getName().'_';
        $attribute_dimension = $this->getDatabase()->getDimensionByName($attribute_dimension_name);

        return $attribute_dimension->getAllElements();
    }

    /**
     * @param null|string[] $element_names
     * @param null|string[] $attribute_names
     * @param null|bool     $show_headers
     *
     * @throws \Exception
     *
     * @return null|array
     */
    public function getAttributes(
        ?array $element_names = null,
        ?array $attribute_names = null,
        ?bool $show_headers = null
    ): ?array {
        if ('System' === $this->getDatabase()->getName()) {
            throw new \DomainException('System database does not support attributes.');
        }

        $show_headers = $show_headers ?? false;

        $attribute_cube = $this->getAttributeCube();

        // no filters (default)
        $area_export = null;

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

            $area_export = ['area' => $area->getArea()];
        }

        // fetch data from cube
        $attributes = $attribute_cube->arrayExport($area_export, true);

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
     * @param null|string $element_name
     * @param null        $filter
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getBaseElementsOfNode(?string $element_name = null, $filter = null): array
    {
        // // debugging in case of error
        // if (!is_array($this->getElementsOfNode($node))) {
        // throw new \Exception($node);
        // }

        $return = $this->basifyElementList($this->getElementListOfNode($element_name));

        if (null === $filter) {
            return $return;
        }

        return \array_filter($return, static function ($e) use ($filter): bool {
            return 1 === \preg_match($filter, $e);
        });
    }

    /**
     * @param string $element_name
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
     * @param null|string $node
     * @param null|string $filter
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getConsolidatedElementsOfNode(?string $node = null, ?string $filter = null): array
    {
        $result = $this->consolifyElementList($this->getElementListOfNode($node));

        if (null === $filter) {
            return $result;
        }

        return \array_filter($result, static function ($e) use ($filter): bool {
            return 1 === \preg_match($filter, $e);
        });
    }

    /**
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getDimensionToken(): string
    {
        return (string) $this->metaInfo[10];
    }

    /**
     * @todo does not find duplicates
     *
     * @param string $element_name
     *
     * @throws \Exception
     *
     * @return array
     *
     * @see Dimension::testDoubleDescendants()
     */
    public function getDoubleBaseElementsOfNode(string $element_name): array
    {
        $base_elements = $this->traverse($element_name, 1);

        $base_elements = \array_map(static function ($v) {
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
     * @param string $element_name
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
     * @param int $eId
     *
     * @throws \Exception
     *
     * @return Element|Group|User
     */
    public function getElementById(int $eId): Element
    {
        if (!isset($this->elementList['olap_id'][$eId])) {
            throw new \InvalidArgumentException('Unknown element id '.$eId.' given.');
        }

        if ('#_USER_' === $this->getName() && 'System' === $this->getDatabase()->getName()) {
            $this->elements[$eId] = new User($this, $this->getElementListRecordById($eId));
        }

        if ('#_GROUP_' === $this->getName() && 'System' === $this->getDatabase()->getName()) {
            $this->elements[$eId] = new Group($this, $this->getElementListRecordById($eId));
        }

        if (!isset($this->elements[$eId])) {
            $this->elements[$eId] = new Element($this, $this->getElementListRecordById($eId));
        }

        return $this->elements[$eId];
    }

    /**
     * @param string $eName
     *
     * @throws \Exception
     * @throws \ErrorException
     *
     * @return Element
     */
    public function getElementByName(string $eName): Element
    {
        if (!isset($this->elementList['olap_name'][$eName])) {
            throw new \ErrorException('unknown element '.$eName);
        }

        $dim_el = $this->elementList['olap_name'][$eName];

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
            throw new \InvalidArgumentException('ID for unknown element '.$element_name.' from dimension '.
                $this->getName().' requested.');
        }

        return $this->elementList['olap_name'][$element_name];
    }

    /**
     * @param null|int $fromDepth default -1
     * @param null|int $toDepth   default 0
     *
     * @throws \Exception
     *
     * @return array
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
     * @return Store
     */
    public function getElementListOfNode(?string $element_name = null): Store
    {
        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                // 'parent' => '',
                'show_lock_info' => 1,
                'show_permission' => 1,
                'limit' => 0, // default
            ],
        ];

        // @todo what is the purpose of Dimension::getElementListOfNode()? what is it supposed to do?
        // @todo Dimension::getElementListOfNode() needs OlapIdentifier:: support
        if (null === $element_name) {
            return $this->getConnection()->request(self::API_DIMENSION_ELEMENTS, $params);
        }

        return $this->fullTraverse($element_name);
    }

    /**
     * @param string $element_name
     *
     * @throws \Exception
     *
     * @return array
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
     * @return array
     */
    public function getElementListRecordById(int $element_id): array
    {
        $this->listElements();
        if (!$this->hasElementById($element_id)) {
            throw new \InvalidArgumentException('Unknown element ID '.$element_id.' given.');
        }

        return $this->elementList['olap_id'][$element_id];
    }

    /**
     * @param string $element_name
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getElementListRecordByName(string $element_name): array
    {
        $this->listElements();
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
        if (!isset($this->elementList['olap_id'][$elementId][1])) {
            throw new \InvalidArgumentException('Name for unknown element ID '.$elementId.' from dimension '.
                $this->getName().' requested.');
        }

        return $this->elementList['olap_id'][$elementId][1];
    }

    /**
     * @return null|string
     */
    public function getFirstElement(): ?string
    {
        $this->elementList['olap_name'] = $this->elementList['olap_name'] ?? [];

        \reset($this->elementList['olap_name']);

        if (null === ($return = \key($this->elementList['olap_name']))) {
            return null;
        }

        return (string) $return;
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
     * create a parent child list for given node.
     *
     * @param null|array|string $nodes
     * @param null|int          $level
     *
     * @throws \Exception
     *
     * @return array
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

                if (Element::ELEMENT_TYPE_CONSOLIDATED === (int) $element_child_record[6]) {
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

        $elem_list = \array_map(static function ($e) {
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
        return isset($this->elementList['olap_id'][$element_id]);
    }

    /**
     * @param string $element_name
     *
     * @return bool
     */
    public function hasElementByName(string $element_name): bool
    {
        return isset($this->elementList['olap_name'][$element_name]);
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
     * @return array
     */
    public function listCubes(): array
    {
        // @todo improve performance - it's very slow
        $return = [];
        foreach ($this->getDatabase()->listCubes() as $cube_name) {
            $cube_record = $this->getDatabase()->getCubeListRecordByName($cube_name);

            if (\in_array($this->getOlapObjectId(), \explode(',', $cube_record[3]), false)) {
                $return[$cube_record[1]] = true;
            }
        }

        return \array_keys($return);
    }

    /**
     * @param null|bool $cached
     *
     * @throws \Exception
     *
     * @return null|array
     */
    public function listElements(?bool $cached = null): ?array
    {
        $cached = $cached ?? true;

        if (true === $cached && null !== $this->elementList) {
            return $this->elementList;
        }

        $this->elementList = [];

        $element_list = $this->getConnection()->request(self::API_DIMENSION_ELEMENTS, [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'show_lock_info' => 1,
                'show_permission' => 1,
                'limit' => 0, // default
            ],
        ]);

        foreach ($element_list as $element_row) {
            $this->elementList['olap_id'][(int) $element_row[0]] = $element_row;
            $this->elementList['olap_name'][$element_row[1]] = (int) $element_row[0];
        }

        return $this->elementList;
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
     * @param Element    $node
     * @param null|bool  $delete
     * @param null|bool  $force
     * @param null|int   $level
     * @param null|Store $remove_collection
     * @param null|Store $tree_elements
     * @param null|Store $blacklist
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
        ?Store $remove_collection = null,
        ?Store $tree_elements = null,
        ?Store $blacklist = null
    ): bool {
        $force = $force ?? false;
        $delete = $delete ?? false;
        $level = $level ?? 0;
        $remove_collection = $remove_collection ?? new Store();
        $tree_elements = $tree_elements ?? new Store();
        $blacklist = $blacklist ?? new Store();

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
                        \array_map(static function ($descendant) use ($blacklist) {
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

        $params = [
            'query' => [
                'database' => $this->getDatabase()->getOlapObjectId(),
                'dimension' => $this->getOlapObjectId(),
                'elements' => \implode(',', \array_map(static function (Element $v) {
                    return $v->getOlapObjectId();
                }, $remove_collection->getArrayCopy())),
            ],
        ];

        if (!$delete) {
            $params['query']['type'] = Element::ELEMENT_TYPE_NUMERIC;
        }

        $api_url = self::API_ELEMENT_REPLACE_BULK;
        if ($delete) {
            $api_url = self::API_ELEMENT_DESTROY_BULK;
        }

        $response = $this->getConnection()->request($api_url, $params);

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

        $return = new Store();
        $this->internShowParents($element, $return, $full_path);

        return $return->getArrayCopy();
    }

    /**
     * @param string $element_name
     *
     * @throws \Exception
     *
     * @return array
     */
    public function testDoubleDescendants(string $element_name): array
    {
        $list_of_descendants = $this->traverse($element_name);

        $result = [];
        $doubles = [];
        foreach ($list_of_descendants as $descendant) {
            if (isset($result[$descendant['id']])) {
                $doubles[] = $descendant;

                continue;
            }
            $result[$descendant['id']] = true;
        }

        return $doubles;
    }

    /**
     * simulated palo_list_ancestors() to get double
     * base elements instead of a list of unique ancestors.
     *
     * Fetch modes
     * 1 - Base elements only
     * 2 - consolidated only
     * 3 - fetch all (default)
     *
     * @param string     $element_name
     * @param null|int   $fetch_mode   (1 - base elements|2 - consolidated elements|3 - all = default)
     * @param null|bool  $remove_duplicates
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

    public function fullTraverse(
        string $element_name,
        ?int $fetch_mode = null,
        ?Store $result = null,
        ?int $level = null,
        ?bool $remove_duplicates = null
    ): Store {
        $fetch_mode = $fetch_mode ?? 3;
        $result = $result ?? new Store();
        $level = $level ?? 0;
        $remove_duplicates = $remove_duplicates ?? true;

        $element_record = $this->getElementListRecordByName($element_name);

        // don't run on already known elements
        if ($remove_duplicates && isset($result[(int) $element_record[0]])) {
            return $result;
        }

        // iterate over children of consolidated element
        if (Element::ELEMENT_TYPE_CONSOLIDATED === (int) $element_record[6]) {
            // collect if not "only Base elements"
            if (1 !== $fetch_mode) {
                if ($remove_duplicates) {
                    $result[(int)$element_record[0]] = $element_record;
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
                if (Element::ELEMENT_TYPE_CONSOLIDATED === (int) $element_record[6]) {
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
                $result[(int)$element_record[0]] = $element_record;
            } else {
                $result[] = $element_record;
            }
        }

        return $result;
    }

    /**
     * @param string[] $listOfElementNames
     *
     * @return array
     */
    public function uniquifyElementList(array $listOfElementNames): array
    {
        // remove duplicate elements
        return \array_unique($listOfElementNames, \SORT_STRING);
    }

    /**
     * @param Element       $element
     * @param null|Store    $return
     * @param null|bool     $full_path
     * @param null|int      $level
     * @param null|string[] $ancestors
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function internShowParents(
        Element $element,
        ?Store $return = null,
        ?bool $full_path = null,
        ?int $level = null,
        ?array $ancestors = null
    ): array {
        $ancestors = $ancestors ?? [];
        $level = $level ?? 0;
        $full_path = $full_path ?? true;
        $return = $return ?? new Store();

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
     * @param Store $elementList
     *
     * @return string[]
     */
    private function basifyElementList(Store $elementList): array
    {
        // remove consolidated elements
        $element_list = \array_filter($elementList->getArrayCopy(), static function ($e) {
            return !(Element::ELEMENT_TYPE_CONSOLIDATED === (int) $e[6]);
        });

        // fetch only element names
        $element_list = \array_map(static function ($e) {
            return $e[1];
        }, $element_list);

        // reindex array
        return \array_values($element_list);
    }

    /**
     * @param Store $elementList
     *
     * @throws \Exception
     *
     * @return array
     */
    private function consolifyElementList(Store $elementList): array
    {
        $element_list = $elementList->getArrayCopy();

        // remove Base elements
        $element_list = \array_filter($element_list, static function ($e) {
            return Element::ELEMENT_TYPE_CONSOLIDATED === (int) $e[6];
        });

        // fetch only element names
        $element_list = \array_map(static function ($e) {
            return $e[1];
        }, $element_list);

        // reindex array
        return \array_values($element_list);
    }

    /**
     * @param Element $node
     *
     * @throws \Exception
     *
     * @return string
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

    /**
     * @throws \Exception
     */
    private function init(): void
    {
        $this->listElements();
    }
}
