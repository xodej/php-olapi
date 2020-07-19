<?php

declare(strict_types=1);

namespace Xodej\Olapi\Test;

include __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

/**
 * Class OlapiTestCase.
 *
 * @internal
 * @coversNothing
 */
abstract class OlapiTestCase extends TestCase
{
    public const OLAP_HOST_WITH_PORT = 'http://localhost:7777';

    public const OLAP_USER = 'admin';
    public const OLAP_PASS = 'admin';

    public const DATABASE = 'XODEJ_OLAPI_UNIT_TEST';

    public const DIM_YEAR = 'Year';
    public const DIM_PERIOD = 'Period';
    public const DIM_UNIT = 'Unit';
    public const DIM_MEASURE = 'Measure';
}
