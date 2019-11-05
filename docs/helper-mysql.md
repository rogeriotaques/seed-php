# Seed-PHP Microframework

## MySQL

!>`Deprecated`, will be removed from `v1.5.0`. <br >Use `SeedPHP\Helper\Database` instead.

>Package: `Helper\MySQL` <br >
>Namespace: `SeedPHP\Helper\MySQL`

A simple, but powerful, wrapper to work with database (MySQL) queries.

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


---

### <span style="color: #42b983;">#</span> setHost( host [, port, charset] )

Set the database host address and returns itself. 

##### Arguments

- `{String} host: required`
- `{String} port: optional`
- `{String} charset: optional`

##### Return

- `SeedPHP\Helper\MySQL`


---

### <span style="color: #42b983;">#</span> setPort( port )

Set the database access port and returns itself. Default is 3306.

##### Arguments

- `{String} port: required`

##### Return

- `SeedPHP\Helper\MySQL`


---

### <span style="color: #42b983;">#</span> setCredential( user, pass )

Set the database creedentials (user and password) and returns itself.

##### Arguments

- `{String} user: required`
- `{String} password: optional`

##### Return

- `SeedPHP\Helper\MySQL`


---

### <span style="color: #42b983;">#</span> setDatabase( database_name )

Set the database name and returns itself. Default is test.

##### Arguments

- `{String} database_name: required`

##### Return

- `SeedPHP\Helper\MySQL`


---

### <span style="color: #42b983;">#</span> setCharset( charset )

Set the database charset and returns itself. Default is utf8.

##### Arguments

- `{String} charset: required`

##### Return

- `SeedPHP\Helper\MySQL`


---

### <span style="color: #42b983;">#</span> connect()

Connects with the server and returns itself. If a connection already exist, will increase the connections count and do not attempt to connect again.

##### Return

- `SeedPHP\Helper\MySQL`


---

### <span style="color: #42b983;">#</span> disconnect()

Closes a connection to the database. If multiple connexions have been attempted, then this will decrease the connections counter and only disconnect when the counter reaches zero.

##### Return

- `SeedPHP\Helper\MySQL`


---

### <span style="color: #42b983;">#</span> exec( query )

Execute a query statement and either the number of affcted rows (when inserts or updated) or a record set (when selects are performed).

##### Arguments

- `{String} query: required`

##### Return

- `{ Array | Integer }`


---

### <span style="color: #42b983;">#</span> insert( table_name, data )

A shorthand for inserting records into any table of the connected database. Returns the number os affected records.

##### Arguments

- `{String} table_name: required`
- `{Array} data: required`

##### Return

- `{ Integer }`


---

### <span style="color: #42b983;">#</span> update( table_name, data [, where] )

A shorthand for updating records into any table of the connected database. Returns the number os affected records.

##### Arguments

- `{String} table_name: required`
- `{Array} data: required`
- `{Array} where: optional`

##### Return

- `{ Integer }`


---

### <span style="color: #42b983;">#</span> delete( table_name, where )

A shorthand for deleting records from any table of the connected database. Returns the number os affected records.

##### Arguments

- `{String} table_name: required`
- `{Array} where: required`

##### Return

- `{ Integer }`


---

### <span style="color: #42b983;">#</span> insertedId()

Returns the last inserted ID.

##### Return

- `{ Integer }`


---

### <span style="color: #42b983;">#</span> resultCount()

Returns the last result count. If ran after any statement other than a `select`, it will return zero.

##### Return

- `{ Integer }`


---

### <span style="color: #42b983;">#</span> getLink()

Returns the existing `\SeedPHP\Helper\MySQL` Connection Object (`\MySQLConnectionObject`) or `NULL` when there's no connection.

##### Arguments

- `{String} host: required`
- `{String} port: optional`
- `{String} charset: optional`

##### Return

- `\MySQLConnectionObject`
