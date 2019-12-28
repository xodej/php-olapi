<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

include_once __DIR__.'/OlapiTestCase.php';

use Xodej;
use Xodej\Olapi\Connection;

/**
 * Class UserTest.
 *
 * @internal
 * @coversNothing
 */
class UserTest extends OlapiTestCase
{
    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testSystemDatabaseExists(): void
    {
        $connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
        $database = $connection->getSystemDatabase();
        $user_obj = $database->getUser('admin');
        self::assertInstanceOf(Xodej\Olapi\User::class, $user_obj);

        $pw_hash = $user_obj->getPasswordHash();
        self::assertIsString($pw_hash);

        $groups = $user_obj->getGroups();
        self::assertContains('admin', $groups);

        $connection->close();
    }
}
