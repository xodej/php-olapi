<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

use Xodej\Olapi\Connection;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class DimensionTest.
 *
 * @internal
 * @coversNothing
 */
class DimensionTest extends OlapiTestCase
{
    private static ?Connection $connection;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
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
    public function testAddDimension(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension()
        ;

        self::assertIsString($user_dim->getFirstElement());
        self::assertSame('admin', $user_dim->getFirstElement());

        self::assertIsArray($user_dim->getTopElements());
        self::assertCount(8, $user_dim->getTopElements());
    }

    /**
     * @throws \Exception
     */
    public function testDimensionInfo(): void
    {
        $info = self::$connection
            ->getDatabase('Demo')
            ->getDimension('Products')
            ->info()
        ;

        self::assertCount(1, $info);
        self::assertIsArray($info[0]);
    }

    /**
     * @throws \Exception
     */
    public function testDebugMode(): void
    {
        $user_dim = self::$connection
            ->getSystemDatabase()
            ->getUserDimension()
        ;

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
            ->getDimensionByName('Products')
        ;

        self::assertSame(3, $dim_prods->getMaxDepth(), 'Dimension::getMaxDepth() failed');
        self::assertSame(4, $dim_prods->getMaxIndent(), 'Dimension::getMaxIndent() failed');
        self::assertSame(3, $dim_prods->getMaxLevel(), 'Dimension::getMaxLevel() failed');

        self::assertSame(0, $dim_prods->getType(), 'Dimension::getType() failed');
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
            ->getElementIdFromName('99999999')
        ;
    }
}
