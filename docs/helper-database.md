# Seed-PHP Microframework

## Database (aka PDO)

>Package: `Helper\Database` <br >
>Namespace: `SeedPHP\Helper`

A simple, yet powerful and intuitive wrapper to work with database queries, with built in methods for `fetch`, `insert`, `update` and `delete`, also with support to chained `transactions`.

#### Example

```php
  $config = [
    'base' => 'test',
    'user' => 'REPLACE-WITH-YOUR-USER',
    'pass' => 'REPLACE-WITH-YOUR-PASSWORD'
  ];

  $app->load('database', $config, 'db');

  $app->db->connect();

  $app->db->transaction('begin');
  
  try {
    $res = $app->db->exec( '...' );
    // ...

    $app->db->transaction('commit');
  } catch (Exception $PDOE) {
    $app->db->transaction('rollback');
  }
  
  $app->db->disconnect();
```

---

### <span style="color: #42b983;">#</span> setDNS( dns )

Set the connection string (a.k.a. DNS) and returns itself. Default is `empty`. This is very useful for cases when a connection needs to be done with a driver that this helper does not support (yet).

##### Arguments

- `{String} dns: required`

##### Return

- `\SeedPHP\Helper\Database`

---

### <span style="color: #42b983;">#</span> setDriver( driverName )

Set the driver that PDO will use for connecting to the database. Defaults to `mysql`.

##### Arguments

- `{String} driverName: required`

##### Return

- `\SeedPHP\Helper\Database`

---

### <span style="color: #42b983;">#</span> setHost( host [, port, charset] )

Set the database host address and returns itself. Default is `localhost`.

##### Arguments

- `{String} host: required`
- `{String} port: optional`
- `{String} charset: optional`

##### Return

- `\SeedPHP\Helper\Database`

---

### <span style="color: #42b983;">#</span> setPort( port )

Set the database access port and returns itself. Default is `3306`.

##### Arguments

- `{String} port: optional`

##### Return

- `\SeedPHP\Helper\Database`

---

### <span style="color: #42b983;">#</span> setCredential( user, password )

Set the database creedentials (user and password) and returns itself.

##### Arguments

- `{String} user: required`
- `{String} password: required`

##### Return

- `\SeedPHP\Helper\Database`

---

### <span style="color: #42b983;">#</span> setDatabase( database )

Set the database name and returns itself. Default is `test`.

##### Arguments

- `{String} database: required`

##### Return

- `\SeedPHP\Helper\Database`

---

### <span style="color: #42b983;">#</span> setCharset( charset )

Set the database charset and returns itself. Default is `utf8`.

##### Arguments

- `{String} charset: required`

##### Return

- `\SeedPHP\Helper\Database`

---

### <span style="color: #42b983;">#</span> connect()

Connects the app with the Database server and returns itself. If a connection already exist, will increase the connections count and do not attempt to connect again.

##### Return

- `\SeedPHP\Helper\Database`

##### Throws

- `\PDOException`

---

### <span style="color: #42b983;">#</span> disconnect()

Closes a connection to the database. If multiple connexions have been attempted, then this will decrease the connections counter and only disconnect when the counter reaches zero.

##### Return

- `\SeedPHP\Helper\Database`

---

### <span style="color: #42b983;">#</span> exec( query [, values] )

Execute a query statement and returns itself.

When the second argument (`values`) is given, the SQL string should use placeholders (either `?` or named are supported) and the query will be prepared before executed.

##### Arguments

- `{String} query: required`
- `{Array} values: optional`

##### Return

- `\SeedPHP\Helper\Database`

##### Throws

- `\ErrorException`
- `\PDOException`

##### Example

```php
global $config;

$app->load('database', $config, 'db');
$app->db->connect();

// Using question-mark (?) placeholders
$res = $app->db->exec("select * from `test` where `id` = ?", [ 10 ]);

// Or using named placeholders
$res = $app->db->exec("select * from `test` where `id` = :id", [ 'id' => 10 ]);

$app->db->disconnect();

```

---

### <span style="color: #42b983;">#</span> insert( table_name, data )

A shorthand for inserting records into any table of a connected database. It returns the number of affected records.

##### Arguments

- `{String} table_name: required`
- `{Array} data: required`

##### Return

- `{ Integer | False }`

##### Throws

- `\ErrorException`
- `\PDOException`

##### Example

```php
global $config;

$app->load('database', $config, 'db');
$app->db->connect();
$app->db->insert("test", [ "foo" => "bar" ]);
$app->db->disconnect();

```

---

### <span style="color: #42b983;">#</span> update( table_name, data [, where] )

A shorthand for updating records from any table of a connected database. It returns the number of affected records.

##### Arguments

- `{String} table_name: required`
- `{Array} data: required`
- `{Array} where: optional`

##### Return

- `{ Integer | False }`

##### Throws

- `\ErrorException`
- `\PDOException`

##### Example

```php
global $config;

$app->load('database', $config, 'db');
$app->db->connect();
$app->db->update("test", [ "foo" => "rocket" ], [ "id" => 10 ]);
$app->db->disconnect();

```

---

### <span style="color: #42b983;">#</span> delete( table_name [, where] )

A shorthand for deleting records from any table of a connected database. Returns the number of affected records.

##### Arguments

- `{String} table_name: required`
- `{Array} where: optional`

##### Return

- `{ Integer | False }`

##### Throws

- `\ErrorException`
- `\PDOException`

##### Example

```php

$app->db->connect();
$app->db->delete("test", [ "id" => 10 ]);
$app->db->disconnect();

```

---

### <span style="color: #42b983;">#</span> fetch( table_name [, cols [, ...] ] )

A shorthand for fetching records from any table of a connected database. 

##### Arguments

- `{String} table_name: required`
- `{Array} cols: optional`. E.g `['col1', ...]` or `['col1 as A', ...]`
- `{Array} where: optional`. E.g `['id' => 1, ...]` or `['id > 35' => null, ...]`
- `{Integer} limit: optional` Default is `1000`, `0` (zero) makes it unlimited.
- `{Integer} offset: optional`
- `{Array} order: optional` E.g `['id' => 'DESC']`
- `{Array} joins: optional` E.g `['tb1', ...]` or `['tb1 as A', ...]`

##### Return

- `{ Array }`

##### Throws

- `\ErrorException`
- `\PDOException`

##### Example

```php
global $config;

$app->load('database', $config, 'db');
$app->db->connect();

$cols  = [ "id", "name", "timestamp AS date" ];
$where = [ "id" => 10 ];
$app->db->fetch("test", $cols , $where);

// Or a complete call, with join
// $app->db->fetch("test", $cols, [ "test.id = test2.id" => null, "id" => 10 ], 1, 0, ["name" => "ASC"], ["test2"]);

$app->db->disconnect();

```

---

### <span style="color: #42b983;">#</span> insertedId()

Returns the last inserted ID.

##### Return

- `{ Integer }`

##### Example

```php
global $config;

$app->load('database', $config, 'db');
$app->db->connect();
$affected_rows = $app->db->insert("test", [ "foo" => "bar" ]);
$last_inserted_id = $app->db->insertedId();
$app->db->disconnect();

```

---

### <span style="color: #42b983;">#</span> resultCount()

Returns the last result count. 

> If called after any statement other than a `select`, it will return `zero`.

##### Return

- `{ Integer }`

##### Example

```php
global $config;

$app->load('database', $config, 'db');
$app->db->connect();

$res = $app->db->exec("insert into `test` (`id`) values ('10')");
$counter = $app->db->resultCount(); // Returns 0

$res = $app->db->exec("select * from `test` where `id` = ?", [ 10 ]);
$counter = $app->db->resultCount(); // Returns 1 ...

$app->db->disconnect();

```

---

### <span style="color: #42b983;">#</span> getLink() : \PDOConnectionObject`

Returns the existing `PDOConnectionObject` or `NULL` when there's no stablished connection.

##### Return

- `\PDOConnectionObject`

---

### <span style="color: #42b983;">#</span> transaction( status )

Start, commit and rollback transactions within the connected database. 

> Chained transactions are supported.

##### Arguments

- `{String} status: required` ( either `begin`, `commit` or `rollback`)

##### Return

- `\SeedPHP\Helper\Database`

##### Example:

```php
global $config;

$app->load('database', $config, 'db');
$app->db->connect();

$app->db->transaction('begin');

try {
  // run your queries here ...
  $app->db->transaction('commit');
} catch (Exception $PDOE) {
  // if something goes wrong, rollback
  $app->db->transaction('rollback');
  echo 'Oops! Rolled back. ', $PDOE->getMessage();
}

$app->db->disconnect();

```
