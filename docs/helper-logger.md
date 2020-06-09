# Seed-PHP Microframework

## Logger

>Package: `Helper\Logger` <br >
>Namespace: `SeedPHP\Helper`

A simple (yet versatile) solution to write down logs to the database.

```php
  $config = [
    'base' => 'test',
    'user' => 'your-user',
    'pass' => 'your-pass'
  ];

  $app->load('logger', $config);
  $app->logger->log();
```

---

### <span style="color: #42b983;">#</span> table( table_name )

Defines the table to write the logs. Default table name is `log_usage`.

##### Arguments

- `{String} table_name: required`

##### Return

- `\SeedPHP\Helper\Logger`

##### Table Structure

```sql
CREATE TABLE `log_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endpoint` varchar(120) NOT NULL,
  `resource` varchar(60) NOT NULL,
  `request_ip` varchar(60) NOT NULL,
  `request_header` text NOT NULL,
  `request_data` text,
  `response_code` int(11) NOT NULL,
  `response_data` text,
  PRIMARY KEY (`id`,`timestamp`,`endpoint`,`resource`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```

---

### <span style="color: #42b983;">#</span> endpoint( path )

Set values for `endpoint` field.

##### Arguments

- `{String} path: required`

##### Return

- `\SeedPHP\Helper\Logger`

---

### <span style="color: #42b983;">#</span> resource( value )

Set values for `resource` field.

##### Arguments

- `{String} value: required`

##### Return

- `\SeedPHP\Helper\Logger`

---

### <span style="color: #42b983;">#</span> requestIP( ip )

Set values for `requestIP` field.

##### Arguments

- `{String} ip: required`

##### Return

- `\SeedPHP\Helper\Logger`

---

### <span style="color: #42b983;">#</span> requestHeader( header )

Set values for `requestHeader` field.

##### Arguments

- `{Array} header: required`

##### Return

- `\SeedPHP\Helper\Logger`

---

### <span style="color: #42b983;">#</span> requestData( data )

Set values for `requestData` field.

##### Arguments

- `{Array} data: required`

##### Return

- `\SeedPHP\Helper\Logger`

---

### <span style="color: #42b983;">#</span> responseData( data )

Set values for `responseData` field.

##### Arguments

- `{Array} data: required`

##### Return

- `\SeedPHP\Helper\Logger`

---

### <span style="color: #42b983;">#</span> responseCode( code )

Set values for `responseCode` field.

##### Arguments

- `{String} code: required`

##### Return

- `\SeedPHP\Helper\Logger`

---

### <span style="color: #42b983;">#</span> log()

Writes the log in the database table.

##### Return

- `{ Boolean }`
