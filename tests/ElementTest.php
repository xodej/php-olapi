<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

include_once __DIR__.'/OlapiTestCase.php';

use Xodej\Olapi\Connection;
use Xodej\Olapi\Element;
use Xodej\Olapi\User;

/**
 * Class ElementTest.
 *
 * @internal
 * @coversNothing
 */
class ElementTest extends OlapiTestCase
{
    /**
     * @var Connection
     */
    private static $connection;

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testElementExist(): void
    {
        $sys_db = self::$connection->getSystemDatabase();
        $user_dim = $sys_db->getUserDimension();

        self::assertTrue($user_dim->hasElementByName('admin'));

        $admin_elem = $user_dim->getElementByName('admin');
        self::assertInstanceOf(Element::class, $admin_elem);
        self::assertInstanceOf(User::class, $admin_elem);

        self::assertTrue($user_dim->hasElementById($admin_elem->getOlapObjectId()));
        $admin_elem_by_id = $user_dim->getElementById($admin_elem->getOlapObjectId());

        self::assertInstanceOf(Element::class, $admin_elem_by_id);
        self::assertInstanceOf(User::class, $admin_elem_by_id);

        $admin_instance = Element::getInstance($user_dim, 'admin');
        self::assertInstanceOf(Element::class, $admin_instance);
        self::assertInstanceOf(User::class, $admin_instance);
        self::assertEquals('admin', $admin_instance->getName());

        self::assertTrue(self::$connection->getSystemDatabase()->resetLicenseAssociation());
    }
}
