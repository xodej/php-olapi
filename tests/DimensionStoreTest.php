<?php
declare(strict_types=1);
namespace Xodej\Olapi\Test;

use Xodej\Olapi\Connection;
use Xodej\Olapi\Dimension;
use Xodej\Olapi\DimensionStore;

include_once __DIR__ . '/OlapiTestCase.php';

/**
 * Class DimensionStoreTest
 */
class DimensionStoreTest extends OlapiTestCase
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
    public function testPreSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DimensionStore([1,2,3]);
    }

    /**
     * @throws \Exception
     */
    public function testUseOffset(): void
    {
        $store = new DimensionStore();
        $this->expectException(\InvalidArgumentException::class);
        $store[0] = new \stdClass();
    }

    /**
     * @throws \Exception
     */
    public function testUseAppend(): void
    {
        $store = new DimensionStore();
        $this->expectException(\InvalidArgumentException::class);
        $store->append(new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testUseExchangeArray(): void
    {
        $store = new DimensionStore();
        $this->expectException(\InvalidArgumentException::class);
        $store->exchangeArray(new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testAddDimension(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension();

        $store = new DimensionStore();
        $store[] = $user_dim;

        self::assertInstanceOf(Dimension::class, $store[0]);
    }

    /**
     * @throws \Exception
     */
    public function testAddDimensionUseOffset(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension();

        $store = new DimensionStore();
        $store[4] = $user_dim;

        self::assertInstanceOf(Dimension::class, $store[4]);
    }

    /**
     * @throws \Exception
     */
    public function testAppendDimensionUseOffset(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension();

        $store = new DimensionStore();
        $store->append($user_dim);

        self::assertInstanceOf(Dimension::class, $store[0]);
    }

    /**
     * @throws \Exception
     */
    public function testInstance(): void
    {
        $store = new DimensionStore();
        self::assertInstanceOf(DimensionStore::class, $store);
    }

    /**
     * @throws \Exception
     */
    public function testArrayCopy(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension();

        $store = new DimensionStore();
        $store->append($user_dim);

        $result = $store->getArrayCopy();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertInstanceOf(Dimension::class, $result[0]);
    }

    /**
     * @throws \Exception
     */
    public function testExchangeArray(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension();

        $store = new DimensionStore();
        $store->append($user_dim);

        $exchange = new DimensionStore();
        $exchange->append($user_dim);
        $exchange->append($user_dim);

        $old_result = $store->exchangeArray($exchange);

        self::assertIsArray($old_result);
        self::assertCount(1, $old_result);

        $new_result = $store->getArrayCopy();
        self::assertIsArray($new_result);
        self::assertCount(2, $new_result);
        self::assertInstanceOf(Dimension::class, $new_result[0]);
    }

    /**
     * @throws \Exception
     */
    public static function tearDownAfterClass(): void
    {
        self::$connection->close();
    }
}
