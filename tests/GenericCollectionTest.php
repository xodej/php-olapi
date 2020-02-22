<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

use Xodej\Olapi\GenericCollection;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class GenericCollectionTest.
 *
 * @internal
 * @coversNothing
 */
class GenericCollectionTest extends OlapiTestCase
{
    public function testHash()
    {
        $store = new GenericCollection(['a', 'b']);
        self::assertIsString($store->getHash());

        $new_order = $store->array_reverse();
        self::assertSame('b', $new_order[0]);
        self::assertSame('a', $new_order[1]);

        $this->expectException(\BadMethodCallException::class);
        $store->array_bad_emthod_name();
    }
}
