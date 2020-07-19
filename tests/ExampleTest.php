<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

use Xodej\Olapi\Connection;

include_once __DIR__.'/OlapiTestCase.php';

/**
 * Class ExampleTest.
 *
 * @internal
 * @coversNothing
 */
class ExampleTest extends OlapiTestCase
{
    /**
     * @throws \ErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testExampleCode(): void
    {
        // connection parameters
        $host = self::OLAP_HOST_WITH_PORT;
        $user = self::OLAP_USER;
        $pass = self::OLAP_PASS;

        // initialize a connection to Jedox OLAP
        $conn = new Connection($host, $user, $pass);

        // fetch cube Balance from database Biker
        $cube = $conn->getCube('Biker/Balance');

        // read and print value to screen
        self::assertEquals(553806.108333333, $cube->getValue(['Actual', '2015', 'Apr', '10 Best Bike Seller AG', 'Goodwill']), 'Values do not match');
    }
}
