<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

include_once __DIR__.'/OlapiTestCase.php';

use Xodej\Olapi\Util;

/**
 * Class UtilTest.
 *
 * @internal
 * @coversNothing
 */
class UtilTest extends OlapiTestCase
{
    public function testStrputcsv(): void
    {
        $csv_string = Util::strputcsv(['a', 'b,c', 'd;e', '"f"'], ';', '"', '"');
        // self::assertSame('a;b,c;"d;e";"""f"""', $csv_string);
        self::assertSame('a;b,c;"d;e";""f""', $csv_string);

        $csv_string = Util::strputcsv(['a', 'b,c', 'd;e', '"f"']);
        // self::assertSame('a;b,c;"d;e";"""f"""', $csv_string);
        self::assertSame('a,"b,c",d;e,""f""', $csv_string);
    }
}
