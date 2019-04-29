<?php
declare(strict_types=1);
namespace Xodej\Test;

use Xodej\Olapi\Connection;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class ConnectionTest
 */
class ConnectionTest extends OlapiTestCase
{
    /**
     * @var Connection
     */
    private static $connection;

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
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
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testCloseOnNothing(): void
    {
        self::assertTrue(self::$connection->close());
        self::assertTrue(self::$connection->close());

        self::$connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
    }

    /**
     * @throws \ErrorException
     */
    public function testLicenseInfos(): void
    {
        self::assertEquals('THISI-SATRI-ALLIC-ENSEY', self::$connection->getLicenseInfos()[1][0]);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteDatabaseByIdNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        self::$connection->deleteDatabase(self::DATABASE . '_NE');

        $this->expectException(\InvalidArgumentException::class);
        self::$connection->deleteDatabaseById(99999999);
    }

    /**
     * @throws \Exception
     */
    public function testDatabaseListRecordByIdFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        self::$connection->getDatabaseListRecordById(99999999);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteDatabaseByIdFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        self::$connection->deleteDatabaseById(99999999);
    }

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testGetConnection(): void
    {
        self::$connection->getConnection();

        self::$connection->close();

        $this->expectException(\DomainException::class);
        self::$connection->getConnection();

        self::$connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
    }

    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testGetInstance(): void
    {
        $conn = Connection::getInstance(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        self::assertInstanceOf(Connection::class, $conn);
        $conn->close();
    }
}
