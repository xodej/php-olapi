<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

use Xodej\Olapi\Connection;
use Xodej\Olapi\Element;
use Xodej\Olapi\ElementCollection;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class ElementCollectionTest.
 *
 * @internal
 * @coversNothing
 */
class ElementCollectionTest extends OlapiTestCase
{
    /**
     * @var Connection
     */
    private static $connection;

    public static function setUpBeforeClass(): void
    {
        self::$connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
    }

    /**
     * @throws \Exception
     */
    public static function tearDownAfterClass(): void
    {
        self::$connection->close();
    }

    /**
     * @throws \Exception
     */
    public function testPreSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ElementCollection([1, 2, 3]);
    }

    /**
     * @throws \Exception
     */
    public function testUseOffset(): void
    {
        $store = new ElementCollection();
        $this->expectException(\InvalidArgumentException::class);
        $store[0] = new \stdClass();
    }

    /**
     * @throws \Exception
     */
    public function testUseAppend(): void
    {
        $store = new ElementCollection();
        $this->expectException(\InvalidArgumentException::class);
        $store->append(new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testUseExchangeArray(): void
    {
        $store = new ElementCollection();
        $this->expectException(\InvalidArgumentException::class);
        $store->exchangeArray(new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testAddElement(): void
    {
        $element_admin = self::$connection
            ->getSystemDatabase()
            ->getUserDimension()
            ->getElementByName('admin')
        ;

        $store = new ElementCollection();
        $store[] = $element_admin;

        self::assertInstanceOf(Element::class, $store[0]);
    }

    /**
     * @throws \Exception
     */
    public function testAddElementUseOffset(): void
    {
        $element_admin = self::$connection
            ->getSystemDatabase()
            ->getUserDimension()
            ->getElementByName('admin')
        ;

        $store = new ElementCollection();
        $store[4] = $element_admin;

        self::assertInstanceOf(Element::class, $store[4]);
    }

    /**
     * @throws \Exception
     */
    public function testAppendElementUseOffset(): void
    {
        $element_admin = self::$connection
            ->getSystemDatabase()
            ->getUserDimension()
            ->getElementByName('admin')
        ;

        $store = new ElementCollection();
        $store->append($element_admin);

        self::assertInstanceOf(Element::class, $store[0]);
    }

    /**
     * @throws \Exception
     */
    public function testInstance(): void
    {
        $store = new ElementCollection();
        self::assertInstanceOf(ElementCollection::class, $store);
    }

    /**
     * @throws \Exception
     */
    public function testArrayCopy(): void
    {
        $element_admin = self::$connection
            ->getSystemDatabase()
            ->getUserDimension()
            ->getElementByName('admin')
        ;

        $store = new ElementCollection();
        $store->append($element_admin);

        $result = $store->getArrayCopy();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertInstanceOf(Element::class, $result[0]);
    }

    /**
     * @throws \Exception
     */
    public function testExchangeArray(): void
    {
        $element_admin = self::$connection
            ->getSystemDatabase()
            ->getUserDimension()
            ->getElementByName('admin')
        ;

        $store = new ElementCollection();
        $store->append($element_admin);

        $exchange = new ElementCollection();
        $exchange->append($element_admin);
        $exchange->append($element_admin);

        $old_result = $store->exchangeArray($exchange);

        self::assertIsArray($old_result);
        self::assertCount(1, $old_result);

        $new_result = $store->getArrayCopy();
        self::assertIsArray($new_result);
        self::assertCount(2, $new_result);
        self::assertInstanceOf(Element::class, $new_result[0]);
    }
}
