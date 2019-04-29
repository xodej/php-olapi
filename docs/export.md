# Export cube data

## Export whole cube

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
$conn = new Connection($host, $user, $pass);

// fetch cube from database
$cube = $conn->getCube('Biker/Balance');

// export cube data into export.csv (base elements only)
file_put_contents('export.csv', $cube->export());
```

## Export only parts of the cube

```php
// fetch cube from database
$cube = $conn->getCube('Biker/Balance');

// export only Actual and Budget
$area = $cube->createArea([
    'Versions' => ['Actual', 'Budget']
]);

// export cube data into export.csv (base elements only)
file_put_contents('export.csv', $cube->export(['area' => $area]));
```

## Export as array

```php
// fetch cube from database
$cube = $conn->getCube('Biker/Balance');

// export only Actual and Budget
$area = $cube->createArea([
    'Versions' => ['Actual', 'Budget']
]);

// export cube data as array (base elements only)
$data = $cube->exportAsArray(['area' => $area]);
```

## Export specials

```php
// fetch cube from database
$cube = $conn->getCube('Biker/Balance');

// export only Actual and Budget into a csv file
$area = $cube->createArea([
    'Versions' => ['Actual', 'Budget']
]);

// export cube data into export.csv
file_put_contents('export.csv', $cube->export([
    'area'      => $area,
    'base_only' => false,
    'use_rules' => true
]));
```

```php
// fetch dimension from database
$dim = $db->getDimension('Profit_Loss');
```