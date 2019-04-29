## Read values from cube

```php
<?php
declare(strict_types=1);

// init autoload
require_once __DIR__ . '/vendor/autoload.php';

// import Connection class
use Xodej\Olapi\Connection;

// connection parameters
$host = 'http://127.0.0.1:7777';
$user = 'admin';
$pass = 'admin';

// initialize a connection to OLAP and fetch
// cube balance from database biker
$conn = new Connection($host, $user, $pass);
$cube = $conn->getCube('Biker/Balance');

// get value w/o caching --> immediate API call (slow)
echo $cube->getValue(['Actual', '2015', 'Apr', '10 Best Bike Seller AG', 'Goodwill']);

// get value with caching --> bundled API calls (fast)
$cube->startCache();
    // collect OLAP calls
    $cube->getValueC(['Actual', '2014', 'Apr', '10 Best Bike Seller AG', 'Goodwill']);
    $cube->getValueC(['Actual', '2015', 'Apr', '10 Best Bike Seller AG', 'Goodwill']);
    // ..
$cube->endCache();

// works only if endCache() has been called, otherwise returns #NA
echo $cube->getValueC(['Actual', '2014', 'Apr', '10 Best Bike Seller AG', 'Goodwill']);
```