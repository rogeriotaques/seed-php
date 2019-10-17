# Seed-PHP Microframework

## Database (aka PDO)

>Package: `Helper\Database` <br >
>Namespace: `SeedPHP\Helper\Database`

```php
  $config = [
    'base' => 'test',
    'user' => 'your-user',
    'pass' => 'your-pass'
  ];

  $app->load('database', $config, 'db');
  $res = $app->db->connect();
  $res = $app->db->exec( '...' );
  $res = $app->db->disconnect();
```

Gives you a simple, but powerful, wrapper to work with database queries.

#### `setHost( string<host> : required, string<port> : optional, string<charset> : optional ) : \SeedPHP\Helper\Database`

Set the database host address and returns itself. Default is `localhost`.

#### `setPort( string : required ) : \SeedPHP\Helper\Database`

Set the database access port and returns itself. Default is `3306`.

#### `setCredential( string<user> : required, string<pass> : required ) : \SeedPHP\Helper\Database`

Set the database creedentials (user and password) and returns itself.

#### `setDatabase( string<database> : required ) : \SeedPHP\Helper\Database`

Set the database name and returns itself. Default is `test`.

#### `setCharset( string<charset> : required ) : \SeedPHP\Helper\Database`

Set the database charset and returns itself. Default is `utf8`.

#### `connect() : \SeedPHP\Helper\Database`

Connects the page with the \SeedPHP\Helper\Database server and returns itself. If a connection already exist, will increase the connections count and do not attempt to connect again.

#### `disconnect() : \SeedPHP\Helper\Database`

Closes a connection to the database. If multiple connexions have been attempted, then this will decrease the connections counter and only disconnect when the counter reaches zero.

#### `exec( string<query> : required, array<values> : optional ) : \SeedPHP\Helper\Database`

Execute a query statement and returns itself.

When the second argument (`values`) is given, the SQL string should use placeholders (either `?` or named are supported) and the query will be prepared before executed.

#### `insert( string<table> : required, array<data> : required ) : Variant`

A shorthand for insert records into any table from connected database. Returns the number os affected records.

#### `update( string<table> : required, array<data> : required, array<where> : optional ) : Variant`

A shorthand for update records into any table from connected database. Returns the number os affected records.

#### `delete( string<table> : required, array<where> : optional ) : Variant`

A shorthand for delete records from any table from connected database. Returns the number os affected records.

#### `insertedId() : integer`

Returns the last inserted ID.

#### `resultCount() : integer`

Returns the last result count. If ran after any statement other than a `select`, it will return zero.

#### `getLink() : \PDOConnectionObject`

Returns the existing \PDOConnectionObject or NULL when there's no connection.

#### `transaction( string<status> : required ) : \SeedPHP\Helper\Database`

Manage transactions within the connected databases. The expectes values to be given as `status` are: `begin`, `commit` or `rollback`. Default to begin.

Chained transactions are supported.

Code example:

```php
// ...
// Assuming database helper is loaded to 'db' constant

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
