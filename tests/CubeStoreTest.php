<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

use Xodej\Olapi\Connection;
use Xodej\Olapi\Cube;
use Xodej\Olapi\CubeStore;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class CubeStoreTest.
 *
 * @internal
 * @coversNothing
 */
class CubeStoreTest extends OlapiTestCase
{
    /**
     * @var Connection
     */
    private static $connection;

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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
        new CubeStore([1, 2, 3]);
    }

    /**
     * @throws \Exception
     */
    public function testUseOffset(): void
    {
        $store = new CubeStore();
        $this->expectException(\InvalidArgumentException::class);
        $store[0] = new \stdClass();
    }

    /**
     * @throws \Exception
     */
    public function testUseAppend(): void
    {
        $store = new CubeStore();
        $this->expectException(\InvalidArgumentException::class);
        $store->append(new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testUseExchangeArray(): void
    {
        $store = new CubeStore();
        $this->expectException(\InvalidArgumentException::class);
        $store->exchangeArray(new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testAddCube(): void
    {
        $user_group_cube = self::$connection
            ->getSystemDatabase()
            ->getCube('#_USER_GROUP')
        ;

        $store = new CubeStore();
        $store[] = $user_group_cube;

        self::assertInstanceOf(Cube::class, $store[0]);
    }

    /**
     * @throws \Exception
     */
    public function testAddCubeUseOffset(): void
    {
        $user_group_cube = self::$connection
            ->getSystemDatabase()
            ->getCube('#_USER_GROUP')
        ;

        $store = new CubeStore();
        $store[4] = $user_group_cube;

        self::assertInstanceOf(Cube::class, $store[4]);
    }

    /**
     * @throws \Exception
     */
    public function testAppendCubeUseOffset(): void
    {
        $user_group_cube = self::$connection
            ->getSystemDatabase()
            ->getCube('#_USER_GROUP')
        ;

        $store = new CubeStore();
        $store->append($user_group_cube);

        self::assertInstanceOf(Cube::class, $store[0]);
    }

    /**
     * @throws \Exception
     */
    public function testInstance(): void
    {
        $store = new CubeStore();
        self::assertInstanceOf(CubeStore::class, $store);
    }

    /**
     * @throws \Exception
     */
    public function testArrayCopy(): void
    {
        $user_group_cube = self::$connection
            ->getSystemDatabase()
            ->getCube('#_USER_GROUP')
        ;

        $store = new CubeStore();
        $store->append($user_group_cube);

        $result = $store->getArrayCopy();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertInstanceOf(Cube::class, $result[0]);
    }

    /**
     * @throws \Exception
     */
    public function testExchangeArray(): void
    {
        $user_group_cube = self::$connection
            ->getSystemDatabase()
            ->getCube('#_USER_GROUP')
        ;

        $store = new CubeStore();
        $store->append($user_group_cube);

        $exchange = new CubeStore();
        $exchange->append($user_group_cube);
        $exchange->append($user_group_cube);

        $old_result = $store->exchangeArray($exchange);

        // self::assertInternalType('array', $old_result);
        self::assertCount(1, $old_result);

        $new_result = $store->getArrayCopy();
        self::assertIsArray($new_result);
        self::assertCount(2, $new_result);
        self::assertInstanceOf(Cube::class, $new_result[0]);
    }
}
