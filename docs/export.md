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
use Xodej\Olapi\ApiRequestParams\ApiCellExport;

// .. code ..

// fetch cube from database
$cube = $conn->getCube('Biker/Balance');

// export only Actual and Budget
$area = $cube->createArea([
    'Versions' => ['Actual', 'Budget']
]);

// create parameters for export API call
$export_params = new ApiCellExport();
$export_params->area = $area;

// export cube data into export.csv (base elements only)
file_put_contents('export.csv', $cube->export($export_params));
```

## Export as array

```php
use Xodej\Olapi\ApiRequestParams\ApiCellExport;

// .. code ..

// fetch cube from database
$cube = $conn->getCube('Biker/Balance');

// export only Actual and Budget
$area = $cube->createArea([
    'Versions' => ['Actual', 'Budget']
]);

// create parameters for export API call
$export_params = new ApiCellExport();
$export_params->area = $area;

// export cube data as array (base elements only)
$data = $cube->arrayExport($export_params);
```

## Export specials

```php
use Xodej\Olapi\ApiRequestParams\ApiCellExport;

// .. code ..

// fetch cube from database
$cube = $conn->getCube('Biker/Balance');

// export only Actual and Budget into a csv file
$area = $cube->createArea([
    'Versions' => ['Actual', 'Budget']
]);

// create parameters for export API call
$export_params = new ApiCellExport();
$export_params->area = $area;
$export_params->base_only = false;
$export_params->use_rules = true;

// export cube data into export.csv
file_put_contents('export.csv', $cube->export($export_params));
```

```php
// fetch dimension from database
$dim = $conn->getDatabase('Biker')->getDimension('Profit_Loss');
```