<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

include_once __DIR__.'/OlapiTestCase.php';

use Xodej\Olapi\Connection;
use Xodej\Olapi\Element;
use Xodej\Olapi\User;
use Xodej\Olapi\Role;
use Xodej\Olapi\Group;

/**
 * Class ElementTest.
 *
 * @internal
 * @coversNothing
 */
class ElementTest extends OlapiTestCase
{
    private static ?Connection $connection;

    public static function setUpBeforeClass(): void
    {
        self::$connection = new Connection(self::OLAP_HOST_WITH_PORT, self::OLAP_USER, self::OLAP_PASS);
    }

    /**
     * @throws \Exception
     */
    public static function tearDownAfterClass(): void
    {
        if (null !== self::$connection) {
            self::$connection->close();
        }
    }

    /**
     * @throws \ErrorException
     * @throws \Exception
     */
    public function testElementExist(): void
    {
        $sys_db = self::$connection->getSystemDatabase();
        $user_dim = $sys_db->getUserDimension();

        self::assertTrue($user_dim->hasElementByName('admin'));

        // check with implicit User from Dimension::getElementById()
        $admin_elem = $user_dim->getElementByName('admin');
        self::assertInstanceOf(Element::class, $admin_elem);
        self::assertInstanceOf(User::class, $admin_elem);

        // check explicit User from SystemDatabase::getUser()
        $admin_elem = $sys_db->getUser('admin');
        self::assertInstanceOf(Element::class, $admin_elem);
        self::assertInstanceOf(User::class, $admin_elem);

        self::assertTrue($user_dim->hasElementById($admin_elem->getOlapObjectId()));
        $admin_elem_by_id = $user_dim->getElementById($admin_elem->getOlapObjectId());

        self::assertInstanceOf(Element::class, $admin_elem_by_id);
        self::assertInstanceOf(User::class, $admin_elem_by_id);

        $admin_instance = Element::getInstance($user_dim, 'admin');
        self::assertInstanceOf(User::class, $admin_instance);
        self::assertSame('admin', $admin_instance->getName());

        $role_dim = $sys_db->getRoleDimension();
        // check with implicit Role from Dimension::getElementById()
        $admin_role = $role_dim->getElementByName('admin');
        self::assertInstanceOf(Element::class, $admin_role);
        self::assertInstanceOf(Role::class, $admin_role);

        // check explicit Role from SystemDatabase::getRole()
        $admin_role = $sys_db->getRole('admin');
        self::assertInstanceOf(Element::class, $admin_role);
        self::assertInstanceOf(Role::class, $admin_role);

        $group_dim = $sys_db->getGroupDimension();
        // check with implicit Group from Dimension::getElementById()
        $admin_group = $group_dim->getElementByName('admin');
        self::assertInstanceOf(Element::class, $admin_group);
        self::assertInstanceOf(Group::class, $admin_group);

        // check explicit Group from SystemDatabase::getGroup()
        $admin_group = $sys_db->getGroup('admin');
        self::assertInstanceOf(Element::class, $admin_group);
        self::assertInstanceOf(Group::class, $admin_group);

        self::assertTrue(self::$connection->getSystemDatabase()->resetLicenseAssociation());
    }
}
