<?php
declare(strict_types=1);
namespace Xodej\Olapi\Test;

use Xodej\Olapi\Store;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class StoreTest.
 *
 * @internal
 * @coversNothing
 */
class StoreTest extends OlapiTestCase
{
    public function testHash()
    {
        $store = new Store(['a', 'b']);
        self::assertIsString($store->getHash());

        $new_order = $store->array_reverse();
        self::assertEquals('b', $new_order[0]);
        self::assertEquals('a', $new_order[1]);

        $this->expectException(\BadMethodCallException::class);
        $store->array_bad_emthod_name();
    }
}
