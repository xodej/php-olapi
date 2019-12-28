<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

use Xodej\Olapi\Connection;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class CubeTest.
 *
 * @internal
 * @coversNothing
 */
class CubeTest extends OlapiTestCase
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
    public function testCreateArea(): void
    {
        $cube = self::$connection->getCube('System/#_USER_GROUP');

        $this->expectException(\InvalidArgumentException::class);
        $cube->createArea(['a' => '*', 'b' => '*', 'c' => '*']);
    }

    /**
     * @throws \Exception
     */
    public function testCaching(): void
    {
        $cube = self::$connection->getCube('System/#_USER_GROUP');

        $admin_has_group_admin_wo_cache = $cube->getValue(['admin', 'admin']);
        self::assertEquals(1, $admin_has_group_admin_wo_cache, 'admin does not have group admin w/o caching');

        self::assertFalse($cube->cacheCollectionEnabled());

        $this->expectException(\ErrorException::class);
        $cube->endCache();

        $cube->startCache();
        self::assertTrue($cube->cacheCollectionEnabled());
        $admin_has_group_admin = $cube->getValueC(['admin', 'admin']);
        self::assertEquals('#NA', $admin_has_group_admin);
        self::assertEquals(1, $cube->getCacheSize(), 'size of cache should be 1');
        $cube->endCache();
        self::assertFalse($cube->cacheCollectionEnabled());

        self::assertEquals(0, $cube->getCacheSize(), 'size of cache should be 0');

        $admin_has_group_admin = $cube->getValueC(['admin', 'admin']);
        self::assertEquals(1, $admin_has_group_admin, 'admin does not have group admin with caching');
    }
}
