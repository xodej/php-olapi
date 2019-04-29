<?php
declare(strict_types=1);
namespace Xodej\Test;

include_once __DIR__ . '/OlapiTestCase.php';

use Xodej\Olapi\Util;

/**
 * Class UtilTest
 */
class UtilTest extends OlapiTestCase
{
    public function testStrputcsv(): void
    {
        $csv_string = Util::strputcsv(['a', 'b,c', 'd;e', '"f"'], ';', '"', '"');
        // self::assertEquals('a;b,c;"d;e";"""f"""', $csv_string);
        self::assertEquals('a;b,c;"d;e";""f""', $csv_string);

        $csv_string = Util::strputcsv(['a', 'b,c', 'd;e', '"f"']);
        // self::assertEquals('a;b,c;"d;e";"""f"""', $csv_string);
        self::assertEquals('a,"b,c",d;e,""f""', $csv_string);
    }
}
