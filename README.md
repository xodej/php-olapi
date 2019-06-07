# php-olapi

## a pure PHP lib for communicating with a Jedox OLAP (using HTTP API)

This repository is **unstable**. Please be careful when updating your app.
Some APIs/methods might break or change. If you use the library for
professional work you should either fork the version you develop with or
refer to a specific commit.

## Installation

Requires PHP 7.3+

```cli
composer require xodej/php-olapi:dev-master
```

## Example
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

// import Connection class
use Xodej\Olapi\Connection;

// connection parameters
$host = 'http://127.0.0.1:7777';
$user = 'admin';
$pass = 'admin';

// initialize a connection to Jedox OLAP
$conn = new Connection($host, $user, $pass);

// fetch cube Balance from database Biker
$cube = $conn->getCube('Biker/Balance');

// read and print value to screen
echo $cube->getValue(['Actual', '2015', 'Apr', '10 Best Bike Seller AG', 'Goodwill']);
```

## Documentation

For more examples please look [here](./docs/index.md).

## Token mechanism

The API token mechanism is currently not supported. All changes on the server during operations are ignored.

## License

Licensed under the [MIT](./LICENSE) License.