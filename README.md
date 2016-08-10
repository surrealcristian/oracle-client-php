# OracleClient

Small Oracle client.


## API

```php
public OracleClient::__construct ( string $username, string $password [, $connectionString = null [, $characterSet = null ]] )

public array OracleClient::fetchAll ( string $sql [, array $bindings = null ] )

public Generator OracleClient::yieldAll ( string $sql [, array $bindings = null ] )

public integer OracleClient::execute ( string $sql [, array $bindings = null ] )
```


## Usage

```php
<?php

use SurrealCristian\OracleClient;

$client = new OracleClient('user', 'password', 'tnsnames_key', 'utf-8');

$sql = <<<EOQ
select column_a, column_b
from table_a
where column_a = :column_a or column_b = :column_b'
EOQ;

$bindings = [
    'column_a' => 'value_a',
    'column_b' => 'value_b',
];

$rows = $client->all($sql, $bindings);
var_dump($rows);

foreach ($client->yieldAll($sql, $bindings) as $row) {
    var_dump($row);
}

$sql = <<<EOQ
update table_a
set column_a = :column_a
where column_b = :column_b
EOQ;

$bindings = [
    'column_a' => 'value_c',
    'column_b' => 'value_b',
];

$nRowsAffected = $client->execute($sql, $bindings);

var_dump($nRowsAffected);

$client->commit();
```


## License

MIT
