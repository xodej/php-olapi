<?php
declare(strict_types=1);
namespace Xodej\Olapi\Test;

include_once __DIR__.'/OlapiTestCase.php';

use Xodej\Olapi\Connection;

/**
 * Class SystemDatabaseTest.
 *
 * @internal
 * @coversNothing
 */
class SystemDatabaseTest extends OlapiTestCase
{
    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        if ($connection->getSystemDatabase()->hasUser('olapi')) {
            $connection->getSystemDatabase()->deleteUser('olapi');
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
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        if ($connection->getSystemDatabase()->hasUser('olapi')) {
            $connection->getSystemDatabase()->deleteUser('olapi');
        }
        $connection->close();
    }

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testSystemDatabaseExists(): void
    {
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        self::assertTrue($connection->hasDatabase('System'));

        $database = $connection->getSystemDatabase();

        self::assertTrue($database->hasUser('admin'));

        $database->getUser('admin');

        self::assertTrue($database->hasGroup('admin'));

        $database->getGroup('admin');

        self::assertInstanceOf(Connection::class, $connection->getConnection());
        self::assertSame($connection, $connection->getConnection());

        self::assertTrue($connection->close());
        self::assertTrue($connection->close());

        $this->expectException(\InvalidArgumentException::class);
        $connection->getSystemDatabase();

        $connection->close();
    }

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testCreateUser(): void
    {
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        $database = $connection->getSystemDatabase();

        $user_olapi = $database->createUser('olapi_test', ['admin']);
        self::assertEquals('olapi_test', $user_olapi->getName());

        self::assertTrue($user_olapi->setPassword('olapi_password'));

        // reset caches
        $connection->reload();
        $database = $database->reload();

        self::assertTrue($database->hasUser('olapi_test'));
        $connection->close();

        /*
        // does not work since 2018.4 in trial mode --> login with new users is not allowed anymore
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, 'olapi_test', 'olapi_password');
        self::assertInstanceOf(Connection::class, $connection);
        $connection->close();
        */

        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        $database = $connection->getSystemDatabase();
        self::assertTrue($database->deleteUser('olapi_test'));

        $connection->reload();
        $database = $database->reload();

        self::assertFalse($database->hasUser('olapi_test'));

        $this->expectException(\InvalidArgumentException::class);
        $database->createUser('admin', ['admin']);

        $connection->close();
    }

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testExceptionClosed(): void
    {
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        $connection->close();

        $this->expectException(\DomainException::class);
        self::assertInstanceOf(Connection::class, $connection->getConnection());

        $connection->close();
    }
}
