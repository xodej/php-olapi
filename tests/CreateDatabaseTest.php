<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

include_once __DIR__.'/OlapiTestCase.php';

use Xodej;
use Xodej\Olapi\Connection;
use Xodej\Olapi\User;

/**
 * Class CreateDatabaseTest.
 *
 * @internal
 * @coversNothing
 */
class CreateDatabaseTest extends OlapiTestCase
{
    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        // delete test database if exist
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        if ($connection->hasDatabase(self::DATABASE)) {
            $connection->deleteDatabase(self::DATABASE);
        }
        $connection->close();
    }

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public static function tearDownAfterClass(): void
    {
        // delete test database if exist
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        if ($connection->hasDatabase(self::DATABASE)) {
            $connection->deleteDatabase(self::DATABASE);
        }
        $connection->close();
    }

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testDimensionCreate(): void
    {
        // reload internal data model
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        self::assertFalse($connection->hasDatabase(self::DATABASE));
        self::assertTrue($connection->createDatabase(self::DATABASE));

        // reload internal data model
        $connection->reload();
        $database = $connection->getDatabase(self::DATABASE);
        self::assertInstanceOf(Xodej\Olapi\Database::class, $database);

        // create test dimension
        self::assertTrue($database->createDimension(self::DIM_YEAR));

        // reload internal data model
        $connection->reload();
        $database = $connection->getDatabase(self::DATABASE);
        $dimension = $database->getDimensionByName(self::DIM_YEAR);
        self::assertInstanceOf(Xodej\Olapi\Dimension::class, $dimension);

        // reload internal data model
        $connection->reload();
        self::assertTrue($connection->deleteDatabase(self::DATABASE));

        // reload internal data model
        $connection->reload();
        $this->expectException(\InvalidArgumentException::class);
        $connection->getDatabase(self::DATABASE);

        $connection->close();
    }

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testUser(): void
    {
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        $user = $connection->getUser();
        self::assertInstanceOf(User::class, $user);
        self::assertEquals('admin', $user->getName());
        $connection->close();
    }
}
