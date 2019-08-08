# Seed-PHP Microframework Documentation

## MySQL

`Deprecated`, will be removed from `v1.5.0`. Use `SeedPHP\Helper\Database` instead. <br >
Package: `Helper\MySQL` <br >
Namespace: `SeedPHP\Helper\MySQL`

```php
  $config = [
    'base' => 'test',
    'user' => 'your-user',
    'pass' => 'your-pass'
  ];

  $app->load('mysql', $config, 'db');
  $res = $app->db->connect();
  $res = $app->db->exec( '...' );
  $res = $app->db->disconnect();
```

Gives you a simple, but powerful, wrapper to work with database (MySQL) queries.

#### `setHost( string<host> : required, string<port> : optional, string<charset> : optional ) : \SeedPHP\Helper\MySQL`

Set the database host address and returns itself. Default is localhost.

#### `setPort( string : required ) : \SeedPHP\Helper\MySQL`

Set the database access port and returns itself. Default is 3306.

#### `setCredential( string<user> : required, string<pass> : required ) : \SeedPHP\Helper\MySQL`

Set the database creedentials (user and password) and returns itself.

#### `setDatabase( string<database> : required ) : \SeedPHP\Helper\MySQL`

Set the database name and returns itself. Default is test.

#### `setCharset( string<charset> : required ) : \SeedPHP\Helper\MySQL`

Set the database charset and returns itself. Default is utf8.

#### `connect() : \SeedPHP\Helper\MySQL`

Connects the page with the \SeedPHP\Helper\MySQL server and returns itself. If a connection already exist, will increase the connections count and do not attempt to connect again.

#### `disconnect() : \SeedPHP\Helper\MySQL`

Closes a connection to the database. If multiple connexions have been attempted, then this will decrease the connections counter and only disconnect when the counter reaches zero.

#### `exec( string<query> : required ) : \SeedPHP\Helper\MySQL`

Execute a query statement and returns itself.

#### `insert( string<table> : required, array<data> : required ) : Variant`

A short call for insert records into any table from connected database. Returns the number os affected records.

#### `update( string<table> : required, array<data> : required, array<where> : required ) : Variant`

A short call for update records into any table from connected database. Returns the number os affected records.

#### `delete( string<table> : required, array<where> : required ) : Variant`

A short call for delete records from any table from connected database. Returns the number os affected records.

#### `insertedId() : integer`

Returns the last inserted ID.

#### `resultCount() : integer`

Returns the last result count. If ran after any statement other than a `select`, it will return zero.

#### `getLink() : \SeedPHP\Helper\MySQLConnectionObject`

Returns the existing \SeedPHP\Helper\MySQL Connection Object or NULL when there's no connection.
