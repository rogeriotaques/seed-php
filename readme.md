# Seed-PHP Microframework

> The stupidly easy to learn and use PHP microframework!

A microframework is a term used to refer to minimalistic web
application frameworks. It lacks most of the functionality which
is common to expect in a full-fledged web application framework.

Seed-PHP is a microframework that offers you a really simple way
to implement powerfull `RESTfull APIs` that could support pretty
much all needed methods and responses in `JSON` or `XML` formats.

## Get started

You can either fork/clone this repository or install this package using
**composer**. If you are using composer and has already a project folder, use:

```sh
$ php composer require abtzco/seed-php
```

or, to start a project from scratch, use:

```sh
$ php composer create-project abtzco/seed-php
```

After having create a project with the above command, go to the
recently created **seed-php** folder and create a new `index.php`.

Example:

```php
<?php

// include composer autoloader
include './vendor/autoload.php';

// initilise the Seed-PHP App class
$app = \Seed\App::getInstance();

// Define a initial route
$app->route('GET /', function () {
  echo 'Hello Word!';
});

// Let's rock ...
$app->run();
```

If you have forked or cloned the repository from Github, then instead of
include the 'autoload.php' from composer, include the package loader:

```php
// include composer autoloader
//include './vendor/autoload.php';

// include package loader
include './seed-php/loader.php';
```

Now, as a final step, we must create the `.htaccess` file (in the same folder of
the index.php) which will contain the necessary settings for Apache properly use
the `mod_rewrite` and route all the traffic thru our `index.php`.

Example:

```sh
 # --------------------------------------------------------
 # Seed-PHP Microframework.
 # @see http://github.com/abtzco/seed-php
 # --------------------------------------------------------

<IfModule mod_rewrite.c>
    RewriteEngine On
    #RewriteBase /

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

Done! Now, do access your project on any browser. ;)

## Get involved

This is an open-source project, which means you can also freely
contribute to improve it and make it better. To do your contributions,
fork this repository, do your changes or improvements and create a pull
request.

## Get support

Found an issue or would like to suggest something? <br>
Just go to [this page](https://github.com/AbtzCo/seed-php/issues) and
open a ticket.

## Documentation

Check out the available methods in Seed-PHP microframework.

### Methods

#### `\Seed\App::getInstance()`

The most basic method, this should be the first called method
after loading the package resources. The core engine app is a
singleton. All you need to do is get its instance to be able
to use it.

#### `\Seed\App()->run()`

This is the last method you are gonna call.

It is reponsible for do the 'magic' and make the engine start.
After define all routes and settings you need for your tiny app,
just call this method (it will be done pretty much at the bottom
of your index file).

#### `\Seed\App()->onFail( function:required )`

Hand over the error response. If this handler is not given,
then Seed-PHP will return an error message in the chosen return format.

#### `\Seed\App()->header( string:optional )`

Retrieve all headers from request.

If a string is provided as parameter, then only the header
that key matches the given string will be returned, or false,
when not found.

#### `\Seed\App()->post( string:optional )`

Retrieve all posted data when requests uses a POST method.

If a string is provided as parameter, then only the post data that
key matches the given string will be returned, or false,
when not found.

#### `\Seed\App()->get( string:optional )`

Retrieve all data passed as query-string in a request.

If a string is provided as parameter, then only the data that
key matches the given string will be returned, or false,
when not found.

#### `\Seed\App()->file( string:optional )`

Retrieve all information about uploaded files.

If a string is provided as parameter, then only the file information
that key matches the given string will be returned, or false,
when not found.

#### `\Seed\App()->cookie( string:optional )`

Retrieve all cookies from a request.

If a string is provided as parameter, then only the cookie that
key matches the given string will be returned, or false,
when not found.

#### `\Seed\App()->put( string:optional )`

Retrieve all data posted when request uses PUT method.

If a string is provided as parameter, then only the data that
key matches the given string will be returned, or false,
when not found.

#### `\Seed\App()->request()`

Returns an object which contains relevant information about
the current request. E.g:

`GET http://dev.seed-php/sample/anything/foo/bar`

```json
{
  "base": "http://dev.seed-php/",
  "method": "GET",
  "endpoint": "sample",
  "verb": "anything",
  "id": null,
  "args": { "foo": "foo" }
}
```

#### `\Seed\App()->load( string:required, array:optional, string:optional)`

Loads a helper and makes it available from the instance of `\Seed\App()`.

```php
$app->load('mysql', [ 'base' => 'test' ], 'db');
```

| Param       | Type   | Required | Default | Remark                                                                                          |
| ----------- | ------ | -------- | ------- | ----------------------------------------------------------------------------------------------- |
| Helper name | string | Yes      |         | Available helpers: <br >`mysql`, `curl`, `logger` and `http`                                    |
| Config      | array  | No       | []      | Some helper use this, such as <br >`mysql` or `logger`                                          |
| Alias       | string | No       | empty   | When given helper will be attached to `\Seed\App()` with this name, instead of the helper name. |

> More details about helper are at the end of this doc.

#### `\Seed\App()->route( string:required, function:optional )`

Creates the routing rules for the API. E.g:

```php
$app->route('GET /', function () {});
```

All following methods can be used by default, otherwise,
the allowed methods list can be customized (see below):

`GET, POST, PUT, DELETE, PATCH, OPTIONS`

You can define multiples methods at once, such as:

```php
$app->route('GET|POST /', function () {});
```

In case you don't provide a method, `GET` will be assumed.

```php
// for this route, GET is assumed as default
$app->route('/', function () {});
```

#### `\Seed\App()->response( code:optional, data:optional )`

Completes the routing process and returns the result to client's browser. The chosen format (`json` or `xml`) will be respected.
The output format can be defined thru `::setOutputType`, as later in this docs.

If code is not given, then `200` is assumed as default.

```php
$app->route('/', function () use ($app) {
  $app->response(200, ['data' => ['foo' => 'bar]]);
});
```

Some properties from the returning object can be customized by the enduser.
In order to customize those properties, use the following meta parameters as
a query-string:

`GET http://dev.seed-php/sample/anything/foo/bar?_router_status=code&_router_message=str&_router_data=obj`

| Param            | Type   | Remark                                       |
| ---------------- | ------ | -------------------------------------------- |
| \_router_status  | string | Changes the name of property `status`        |
| \_router_message | string | Changes the name of property `message`       |
| \_router_data    | string | Changes the name of property `data` (if any) |

#### `\Seed\App()->setAllowedMethod( string:required, boolean:optional )`

Allow custom methods to consume the API. When provinding the second
parameter, the whole method list will be reseted.

```php
// allow the COPY method alongside the existing list
$app->setAllowedMethod( 'COPY' );

// allow ONLY the GET method
$app->setAllowedMethod( 'GET', true );
```

#### `\Seed\App()->setAllowedHeader( string:required, boolean:optional )`

Allow a custom header to your API. When provinding the second
parameter, the whole header list will be reseted.

```php
// allow the 'X-My-Custom-Header' header alongside the existing list
$app->setAllowedHeader( 'X-My-Custom-Header' );

// allow ONLY the 'X-My-Custom-Header' header
$app->setAllowedHeader( 'X-My-Custom-Header', true );
```

#### `\Seed\App()->setAllowedOrigin( string:required )`

Define the allowed origin to your API.<br>
By default, Seed-PHP allows your API receive cross origin calls.

```php
// allow ONLY domain.tld calls
$app->setAllowedOrigin( 'domain.tld' );
```

#### `\Seed\App()->setCache( boolean:required, int:optional )`

Enables or disables the page caching meta tags. The second parameter refers to the `max-cache-age`.
Cache is enabled by default with a max-age of 1 hour (3600 ms).

```php
// disable cache
$app->setCache( false );

// enable cache for 1 day (60 * 60 * 24) ms
$app->setCache( true, 86400 );
```

#### `\Seed\App()->setLanguage( string:required )`

Changes the metatag content for page language. Default language is English (en).

```php
// change language for Japanese.
$app->setLanguage( 'ja' );
```

#### `\Seed\App()->setCharset( string:required )`

Changes the metatag content for page charset encoding. Default language is UTF-8.

```php
// change charset.
$app->setCharset( 'utf8' );
```

#### `\Seed\App()->setOutputType( string:required )`

Customize the output format from the API. The default format is `JSON`, but you can
also choose `XML` instead. Other formats are (yet) not supported.

```php
// set output format as json
$app->setOutputType( 'json' );

// set output format as xml
$app->setOutputType( 'xml' );
```

---

## Available Helpers

| Helper Name | Remark                                                 |
| ----------- | ------------------------------------------------------ |
| curl        | A full featured cCurl class.                           |
| logger      | A simple log class. Log data in a mysql database.      |
| mysql       | A simple mysql wrapper.                                |
| http        | A http class helper for work with http response codes. |

### Http

```php
  $app->load('http');
  $base = $app->http->getBaseUrl();
```

Gives you some useful methods to standardise the manipulation of HTTP methods.

#### `Http()->getHTTPStatus( integer:required ) : array`

Returns an array containing the protocol, response code and response text.

#### `Http()->getBaseUrl() : string`

Returns the project base URL.

#### `Http()->getClientIP() : string`

Returns the most probably client IP address.<br>
Since this information depends on data provided by browser, it may not be precise.

### Logger

```php
  $config = ['base' => 'test', 'user' => 'your-user', 'pass' => 'your-pass'];
  $app->load('logger', $config);
  $app->logger->log();
```

Gives you a simple solution to write down logs in the database.

#### `Logger()->table( string:required ) : Logger`

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

#### `Logger()->endpoint( string:required ) : Logger`

Set values for `endpoint` field.

#### `Logger()->resource( string:required ) : Logger`

Set values for `resource` field.

#### `Logger()->requestIP( string:required ) : Logger`

Set values for `requestIP` field.

#### `Logger()->requestHeader( array:required ) : Logger`

Set values for `requestHeader` field.

#### `Logger()->requestData( array:required ) : Logger`

Set values for `requestData` field.

#### `Logger()->responseData( array:required ) : Logger`

Set values for `responseData` field.

#### `Logger()->responseCode( string:required ) : Logger`

Set values for `responseCode` field.

#### `Logger()->log() : Boolean`

Writes the log in the database table.

### MySQL

```php
  $config = ['base' => 'test', 'user' => 'your-user', 'pass' => 'your-pass'];
  $app->load('mysql', $config, 'db');
  $res = $app->db->query( '...' );
```

Gives you a simple, but powerful, wrapper to work with database (mysql) queries.

#### `MySQL()->setHost( string<host>:required, string<port>:optional, string<charset>:optional ) : MySQL`

Set the database host address and returns itself. Default is localhost.

#### `MySQL()->setPort( string:required ) : MySQL`

Set the database access port and returns itself. Default is 3306.

#### `MySQL()->setCredential( string<user>:required, string<pass>:required ) : MySQL`

Set the database creedentials (user and password) and returns itself.

#### `MySQL()->setDatabase( string:required ) : MySQL`

Set the database name and returns itself. Default is test.

#### `MySQL()->setCharset( string:required ) : MySQL`

Set the database charset and returns itself. Default is utf8.

#### `MySQL()->connect() : MySQL`

Connects the page with the MySQL server and returns itself.

#### `MySQL()->disconnect() : MySQL`

Closes a connection to the database.

#### `MySQL()->exec( string<query>:required ) : MySQL`

Execute a query statement and returns itself.

#### `MySQL()->insert( string<table>:required, array:required ) : Variant`

A short call for insert records into any table from connected database. Returns the number os affected records.

#### `MySQL()->update( string<table>:required, array<data>:required, array<where>:required ) : Variant`

A short call for update records into any table from connected database. Returns the number os affected records.

#### `MySQL()->delete( string<table>:required, array<where>:required ) : Variant`

A short call for delete records from any table from connected database. Returns the number os affected records.

#### `MySQL()->insertedId( void ) : integer`

Returns the last inserted ID.

#### `MySQL()->resultCount( void ) : integer`

Returns the last result count. If ran after any statement other than a `select`, it will return zero.

#### `MySQL()->getLink( void ) : MySQLConnectionObject`

Returns the existing MySQL Connection Object or NULL when there's no connection.

### Curl

```php
  $app->load('curl');
  $res = $app->curl->get( 'http://...' );
```

Gives you a simple wrapper to work with curl calls. <br>
An usefull way to make your API consumes third party APIs on background.

#### `Curl()->create( string<url>:required, array<options>:optional, string<returnType>:optional ) : Curl`

Resets and points the Curl to a new URL.

#### `Curl()->data( array<options>:required ) : Curl`

Appends a data object to the call.

#### `Curl()->option( string<code>:required, string<value>:required ) : Curl`

Allows to add new custom options.

#### `Curl()->proxy( string<url>:required, string<username>:required, string<password>:required ) : Curl`

Whenever your network goes thru proxy, you should provide it here.

#### `Curl()->cookies( array<params>:required ) : Curl`

#### `Curl()->credential( string<username>:required, string<password>:required ) : Curl`

Set credentials for your endpoint authentication

#### `Curl()->header( string:required ) : Curl`

Allows you to add additional headers to the call.

#### `Curl()->get( string<url>:optional, array<options>:optional ) : Curl`

Performs a GET call

#### `Curl()->post( string<url>:optional, array<options>:optional ) : Curl`

Performs a POST call.

#### `Curl()->put( string<url>:optional, array<options>:optional ) : Curl`

Performs a PUT call.

#### `Curl()->update( string<url>:optional, array<options>:optional ) : Curl`

Performs an UPDATE call.

#### `Curl()->delete( string<url>:optional, array<options>:optional ) : Curl`

Performs a DELETE call.

#### `Curl()->run( string<method>:required, string<url>:required, array<options>:optional ) : Curl`

Performs a general call.
