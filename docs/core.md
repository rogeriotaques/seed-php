# Seed-PHP Microframework

## Core

>Package: `Core` <br >
>Namespace: `SeedPHP\Core`

## Methods

#### `static : getInstance() : \SeedPHP\App`

The most basic method, this should be the first called method
after loading the package resources. The core engine app is a
singleton. All you need to do is get its instance to be able
to use it.

#### `run() : \SeedPHP\App`

This is the last method you are gonna call.

It is reponsible for do the 'magic' and make the engine start.
After define all routes and settings you need for your tiny app,
just call this method (it will be done pretty much at the bottom
of your index file).

#### `onFail( function : required ) : void`

Hand over the error response. If this handler is not given,
then Seed-PHP will return an error message in the chosen return format.

#### `header( string<header> : optional ) : string|boolean`

Retrieve all headers from request.

If a string is provided as parameter, then only the header
that key matches the given string will be returned, or false,
when not found.

#### `post( string<key> : optional ) : string|array|boolean`

Retrieve all posted data when requests uses a POST method.

If a string is provided as parameter, then only the post data that
key matches the given string will be returned, or false,
when not found.

#### `get( string<key> : optional ) : string|array|boolean`

Retrieve all data passed as query-string in a request.

If a string is provided as parameter, then only the data that
key matches the given string will be returned, or false,
when not found.

#### `file( string<key> : optional ) array|boolean`

Retrieve all information about uploaded files.

If a string is provided as parameter, then only the file information
that key matches the given string will be returned, or false,
when not found.

#### `files( string<key> : optional ) : array|boolean`

An alias for `\SeedPHP\App()->file()`.

#### `cookie( string<key> : optional ) : string|boolean`

Retrieve all cookies from a request.

If a string is provided as parameter, then only the cookie that
key matches the given string will be returned, or false,
when not found.

#### `put( string<key> : optional ) : string|array|boolean`

Retrieve all data posted when request uses PUT method.

If a string is provided as parameter, then only the data that
key matches the given string will be returned, or false,
when not found.

#### `request() : object`

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

#### `load( string<helper> : required, array<options> : optional, string<alias> : optional) : void`

Loads a helper and makes it available from the instance of `\SeedPHP\App()`. If an alias is given, the module will be accessed by the access name then.

```php
$app->load('pdo', $config);

$app->pdo->connect();
$app->pdo->exec('select 1');
$app->pdo->disconnect();

# OR

$app->load('pdo', $config, 'db');

$app->db->connect();
$app->db->exec('select 1');
$app->db->disconnect();
```

#### `route( string<route> : required, function<callback> : optional ) : void`

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

#### `response( integer<code> : optional, array<data> : optional ) : json|xml|html`

Completes the routing process and returns the result to client's browser. The chosen format (`json` or `xml`) will be respected.
The output format can be defined thru `::setOutputType`.

If code is not given, then `200` is assumed as default.

```php
$app->route('/', function () use ($app) {
  $app->response(200, ['data' => ['foo' => 'bar']]);
});
```

Some properties from the returning object can be customized by the enduser. In order to customize those properties, use the following meta parameters as a query-string:

`GET http://dev.seed-php/sample/anything/foo/bar?_router_status=code&_router_message=str&_router_data=obj`

| Param            | Type   | Remark                                       |
| ---------------- | ------ | -------------------------------------------- |
| \_router_status  | string | Changes the name of property `status`        |
| \_router_message | string | Changes the name of property `message`       |
| \_router_data    | string | Changes the name of property `data` (if any) |

#### `setAllowedMethod( string : required, boolean : optional ) : void`

Allow custom methods to consume the API. When provinding the second
parameter, the whole method list will be reseted.

```php
// allow the COPY method alongside the existing list
$app->setAllowedMethod( 'COPY' );

// allow ONLY the GET method
$app->setAllowedMethod( 'GET', true );
```

#### `setAllowedHeader( string<header> : required, boolean<reset> : optional ) : void`

Allow a custom header to your API. When provinding the second
parameter, the whole header list will be reseted.

```php
// allow the 'X-My-Custom-Header' header alongside the existing list
$app->setAllowedHeader( 'X-My-Custom-Header' );

// allow ONLY the 'X-My-Custom-Header' header
$app->setAllowedHeader( 'X-My-Custom-Header', true );
```

#### `setAllowedOrigin( string : required ) : void`

Define the allowed origin to your API.<br>
By default, Seed-PHP allows your API receive cross origin calls.

```php
// allow ONLY domain.tld calls
$app->setAllowedOrigin( 'domain.tld' );
```

#### `setCache( boolean<flag> : required, integer<timestamp> : optional ) : void`

Enables or disables the page caching meta tags. The second parameter refers to the `max-cache-age`. Cache is enabled by default with a max-age of 1 hour (3600 ms).

```php
// disable cache
$app->setCache( false );

// enable cache for 1 day (60 * 60 * 24) ms
$app->setCache( true, 86400 );
```

#### `setLanguage( string<lang> : required ) : void`

Changes the metatag content for page language. Default language is English (en).

```php
// change language for Japanese.
$app->setLanguage( 'ja' );
```

#### `setCharset( string<charset> : required ) : void`

Changes the metatag content for page charset encoding. Default language is UTF-8.

```php
// change charset.
$app->setCharset( 'utf8' );
```

#### `setOutputType( string<type> : required ) : void`

Customize the output format from the API. The default format is `JSON`, but you can also choose `XML` instead. Other formats are (yet) not supported.

```php
// set output format as json
$app->setOutputType( 'json' );

// set output format as xml
$app->setOutputType( 'xml' );
```

## Available Helpers

| Helper Name | Remark                                                 |
| ----------- | ------------------------------------------------------ |
| curl        | A full featured cCurl class.                           |
| logger      | A simple log class. Log data in a mysql database.      |
| mysql       | A simple mysql wrapper. (Deprecated since v.1.0.0)     |
| database    | A simple PDO wrapper.                                  |
| http        | A http class helper for work with http response codes. |
