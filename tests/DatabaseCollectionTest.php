<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

use Xodej\Olapi\Connection;
use Xodej\Olapi\Database;
use Xodej\Olapi\DatabaseCollection;
use Xodej\Olapi\SystemDatabase;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class DatabaseCollectionTest.
 *
 * @internal
 * @coversNothing
 */
class DatabaseCollectionTest extends OlapiTestCase
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

    public function testPreSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DatabaseCollection([1, 2, 3]);
    }

    public function testUseOffset()
    {
        $store = new DatabaseCollection();
        $this->expectException(\InvalidArgumentException::class);
        $store[0] = new \stdClass();
    }

    /**
     * @throws \Exception
     */
    public function testUseAppend(): void
    {
        $store = new DatabaseCollection();
        $this->expectException(\InvalidArgumentException::class);
        $store->append(new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testUseExchangeArray(): void
    {
        $store = new DatabaseCollection();
        $this->expectException(\InvalidArgumentException::class);
        $store->exchangeArray(new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testAddDatabase(): void
    {
        $db_sys = self::$connection->getSystemDatabase();

        $store = new DatabaseCollection();
        $store[] = $db_sys;

        self::assertInstanceOf(Database::class, $store[0]);
    }

    /**
     * @throws \Exception
     */
    public function testAddDatabaseUseOffset(): void
    {
        $db_sys = self::$connection->getSystemDatabase();

        $store = new DatabaseCollection();
        $store[4] = $db_sys;

        self::assertInstanceOf(Database::class, $store[4]);
    }

    /**
     * @throws \Exception
     */
    public function testAppendDatabaseUseOffset(): void
    {
        $db_sys = self::$connection->getSystemDatabase();

        $store = new DatabaseCollection();
        $store->append($db_sys);

        self::assertInstanceOf(Database::class, $store[0]);
    }

    /**
     * @throws \Exception
     */
    public function testInstance(): void
    {
        $store = new DatabaseCollection();
        self::assertInstanceOf(DatabaseCollection::class, $store);
    }

    /**
     * @throws \Exception
     */
    public function testArrayCopy(): void
    {
        $db_sys = self::$connection->getSystemDatabase();

        $store = new DatabaseCollection();
        $store->append($db_sys);

        $result = $store->getArrayCopy();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertInstanceOf(Database::class, $result[0]);
        self::assertInstanceOf(SystemDatabase::class, $result[0]);
    }

    /**
     * @throws \Exception
     */
    public function testExchangeArray(): void
    {
        $db_sys = self::$connection->getSystemDatabase();

        $store = new DatabaseCollection();
        $store->append($db_sys);

        $exchange = new DatabaseCollection();
        $exchange->append($db_sys);
        $exchange->append($db_sys);

        $old_result = $store->exchangeArray($exchange);

        self::assertIsArray($old_result);
        self::assertCount(1, $old_result);

        $new_result = $store->getArrayCopy();
        self::assertIsArray($new_result);
        self::assertCount(2, $new_result);
        self::assertInstanceOf(Database::class, $new_result[0]);
        self::assertInstanceOf(SystemDatabase::class, $new_result[0]);
    }
}
