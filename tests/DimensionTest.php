<?php
declare(strict_types=1);
namespace Xodej\Olapi\Test;

use Xodej\Olapi\Connection;
use Xodej\Olapi\Element;

include_once __DIR__ . '/OlapiTestCase.php';

/**
 * Class DimensionTest
 */
class DimensionTest extends OlapiTestCase
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
        parent::setUpBeforeClass();
        self::$connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
    }

    /**
     * @throws \Exception
     */
    public function testAddDimension(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension();

        self::assertIsString($user_dim->getFirstElement());
        self::assertEquals('admin', $user_dim->getFirstElement());

        self::assertIsArray($user_dim->getTopElements());
        self::assertCount(8, $user_dim->getTopElements());
    }

    /**
     * @throws \Exception
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$connection->close();
    }

    /**
     * @throws \Exception
     */
    public function testDebugMode(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension();

        self::assertFalse($user_dim->isDebugMode());
        Connection::$debugMode = true;
        self::assertTrue($user_dim->isDebugMode());
        Connection::$debugMode = false;
    }

    /**
     * @throws \Exception
     */
    public function testMaxFuncs(): void
    {
        $dim_prods = self::$connection
            ->getDatabaseByName('Biker')
            ->getDimensionByName('Products');

        self::assertEquals(3, $dim_prods->getMaxDepth(), 'Dimension::getMaxDepth() failed');
        self::assertEquals(4, $dim_prods->getMaxIndent(), 'Dimension::getMaxIndent() failed');
        self::assertEquals(3, $dim_prods->getMaxLevel(), 'Dimension::getMaxLevel() failed');

        self::assertEquals(0, $dim_prods->getType(), 'Dimension::getType() failed');
    }

    /**
     * @throws \Exception
     */
    public function testGetElementIdFromNameFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        self::$connection
            ->getSystemDatabase()
            ->getDimension('#_USER_')
            ->getElementIdFromName('99999999');
    }
}
