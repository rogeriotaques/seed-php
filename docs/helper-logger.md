# Seed-PHP Microframework Documentation

## Logger

Package: `Helper\Logger` <br >
Namespace: `SeedPHP\Helper\Logger`

```php
  $config = [
    'base' => 'test',
    'user' => 'your-user',
    'pass' => 'your-pass'
  ];

  $app->load('logger', $config);
  $app->logger->log();
```

Gives you a simple solution to write down logs in the database.

#### `Logger()->table( string<table> : required ) : Logger`

Defines the table name to write the logs. Default table name is `log_usage`.

Table should have this structure:

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

#### `Logger()->endpoint( string<path> : required ) : Logger`

Set values for `endpoint` field.

#### `Logger()->resource( string<value> : required ) : Logger`

Set values for `resource` field.

#### `Logger()->requestIP( string<ip> : required ) : Logger`

Set values for `requestIP` field.

#### `Logger()->requestHeader( array<header> : required ) : Logger`

Set values for `requestHeader` field.

#### `Logger()->requestData( array<data> : required ) : Logger`

Set values for `requestData` field.

#### `Logger()->responseData( array<data> : required ) : Logger`

Set values for `responseData` field.

#### `Logger()->responseCode( string<code> : required ) : Logger`

Set values for `responseCode` field.

#### `Logger()->log() : Boolean`

Writes the log in the database table.
