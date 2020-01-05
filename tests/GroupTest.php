<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

include_once __DIR__.'/OlapiTestCase.php';

use Xodej\Olapi\Connection;

/**
 * Class GroupTest.
 *
 * @internal
 * @coversNothing
 */
class GroupTest extends OlapiTestCase
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
        $group_obj = $database->getGroup('admin');
        // self::assertInstanceOf(Xodej\Olapi\Group::class, $group_obj);

        $users = $group_obj->getUsers(true);
        self::assertContains('admin', $users);

        $roles = $group_obj->getRoles();
        self::assertContains('admin', $roles);

        $connection->close();
    }
}
