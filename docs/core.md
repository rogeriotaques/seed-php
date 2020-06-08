# Seed-PHP Microframework

## Core

>Package: `Core` <br >
>Namespace: `SeedPHP\Core`

## Methods

---

### <span style="color: #42b983;">#</span> static : getInstance()

> Keep in mind that this is a `static` method.

The most basic method, this should be the first called method after loading the package resources. 
The core engine app is a singleton. All you need to do is get its instance to be able to use it.

##### Return

- `SeedPHP\App`

##### Example

```php

$app = \SeedPHP\App::getInstance();

```

---

### <span style="color: #42b983;">#</span> run()

This is the last method you are gonna call.

It is reponsible for doing the 'magic' and starting the engine. After define all routes 
and settings you need for your tiny app, just call this method (it will be done pretty 
much at the bottom of your index file).

##### Return

- `SeedPHP\App`

##### Example

```php

$app = \SeedPHP\App::getInstance();
$app->run();

```

---

### <span style="color: #42b983;">#</span> onFail( callback )

Hands over any error responses. If this handler is not given, then Seed-PHP will return 
an error message in the chosen return format (JSON or XML). When rendering pages in the 
server-side, then, this can be used for define the `404` or general error pages.

##### Arguments

- `{Function} callback: required`

##### Return

- `void`

--- 

### <span style="color: #42b983;">#</span> header( [header] )

Retrieve all headers from request when argument is not given.

If a string is provided as the argument, then only the header
that the key matches the given string will be returned, or false 
when not found.

##### Arguments

- `{String} header: optional`

##### Return

- `{String | Array | Boolean}`

--- 

### <span style="color: #42b983;">#</span> post( [key] )

Retrieve all posted data when requests uses the `POST` method.

If a string is provided as argument, then only the post data that
the key matches the given string will be returned, or false when not found.

##### Arguments

- `{String} key: optional`

##### Return

- `{String | Array | Boolean}`

--- 

### <span style="color: #42b983;">#</span> get( [key] )

Retrieve all data passed as query-string in a request.

If a string is provided as argument, then only the data that
the key matches the given string will be returned, or false 
when not found.

##### Arguments

- `{String} key: optional`

##### Return

- `{String | Array | Boolean}`

--- 

### <span style="color: #42b983;">#</span> file( [key] )

Retrieve all information about uploaded files.

If a string is provided as parameter, then only the file information
that key matches the given string will be returned, or false,
when not found.

##### Arguments

- `{String} key: optional`

##### Return

- `{Array | Boolean}`

--- 

### <span style="color: #42b983;">#</span> files( [key] )

An alias for `\SeedPHP\App()->file([key])`.

--- 

### <span style="color: #42b983;">#</span> cookie( [key] )

Retrieve all cookies from a request.

If a string is provided as parameter, then only the cookie that
key matches the given string will be returned, or false,
when not found.

##### Arguments

- `{String} key: optional`

##### Return

- `{String | Boolean}`

--- 

### <span style="color: #42b983;">#</span> put( [key] )

Retrieve all data posted when request uses `PUT` method.

If a string is provided as parameter, then only the data that
key matches the given string will be returned, or false,
when not found.

##### Arguments

- `{String} key: optional`

##### Return

- `{String | Array | Boolean}`

--- 

### <span style="color: #42b983;">#</span> request()

Returns an object which contains relevant information about
the current request. 

##### Return

- `{Object}`

##### Example:

Call 

`GET http://dev.seed-php/sample/anything/foo/bar`

Response
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

--- 

### <span style="color: #42b983;">#</span> load( helper, [options, alias])

Loads a helper and makes it available from the instance of `\SeedPHP\App()`. If an alias is given, 
the module will be accessed by the access name then.

##### Arguments

- `{String} helper: required`
- `{Array} options: optional`
- `{String} alias: optional`

##### Return

- `void`

##### Example

```php
global $config;

$app->load('database', $config);

$app->database->connect();
$app->database->exec('select 1');
$app->database->disconnect();

# OR WITH AN ALIAS

$app->load('database', $config, 'db');

$app->db->connect();
$app->db->exec('select 1');
$app->db->disconnect();
```

--- 

### <span style="color: #42b983;">#</span> route( route [, callback] )

Creates the routing rules for the API, app or website.

Routes can be writen in specific `php` files, hosted on a directory of your preference 
within the app root folder and loaded all at once with:

`$app->load('router', [ 'path' => __DIR__ . '/path/to/router/files' ])`

##### Arguments

- `{String} route: required`
- `{Function} callback: optional`

##### Return

- `void`

##### Example

```php

# GET is assumed as default

$app->route('/', function () {
  // do something here ...
});

# OR

$app->route('GET /', function () {
  // do something here ...
});

# OR

$app->route('GET|POST /', function () {
  // do something here ...
});

```

##### Supported HTTP methods

- `GET`
- `POST`
- `PUT`
- `DELETE`
- `PATCH`
- `OPTIONS`

--- 

### <span style="color: #42b983;">#</span> response( [code, data] )

Completes the routing process and returns the result to client's browser. The chosen format (`JSON` or `XML`) will be respected. The output format can be defined thru `::setOutputType`.

If code is not given, then `200` is assumed as default.

##### Arguments

- `{Integer} code: optional`
- `{Array} data: optional`

##### Return

- `{ JSON | XML | HTML }`

##### Example

```php
$app->route('/', function () use ($app) {
  return $app->response(Http::_OK, ['data' => ['foo' => 'bar']]);
});
```

Some properties from the returning object can be customized directly in the request call. 
This is a particularly useful feature for integrations where the returning property needs 
to have an specific name, originally not supported by Seed-PHP.

In order to customize those properties, use the following meta parameters as a query-string:

`GET http://dev.seed-php/sample/anything/foo/bar?_router_status=code&_router_message=str&_router_data=obj`

| Param            | Type   | Remark                                       |
| ---------------- | ------ | -------------------------------------------- |
| \_router_status  | string | Changes the name of property `status`        |
| \_router_message | string | Changes the name of property `message`       |
| \_router_data    | string | Changes the name of property `data` (if any) |

--- 

### <span style="color: #42b983;">#</span> setAllowedMethod( method [, flag] )

Allow custom methods to consume the API. When provinding the second
parameter, the whole method list will be reseted.

##### Arguments

- `{String|Array<String>} method: required`
- `{Boolean} exclusive: optional`

##### Return

- `void`

##### Example

```php
// allow the COPY method alongside the existing list
$app->setAllowedMethod( 'COPY' );

// allow ONLY the GET method
$app->setAllowedMethod( 'GET', true );
```

--- 

### <span style="color: #42b983;">#</span> setAllowedHeader( header [, reset] )

Allow a custom header to your API. When provinding the second
parameter, the whole header list will be reset.

##### Arguments

- `{String|Array<String>} header: required`
- `{Boolean} reset: optional`

##### Return

- `void`

##### Example

```php
// allow the 'X-My-Custom-Header' header alongside the existing list
$app->setAllowedHeader( 'X-My-Custom-Header' );

// allow ONLY the 'X-My-Custom-Header' header
$app->setAllowedHeader( 'X-My-Custom-Header', true );
```

--- 

### <span style="color: #42b983;">#</span> setAllowedOrigin( domain )

Define the allowed origin to your API.<br>
By default, Seed-PHP allows your API receive cross origin calls.

##### Arguments

- `{String} domain: required`

##### Return

- `void`

##### Example

```php

// allow ONLY domain.tld calls
$app->setAllowedOrigin( 'domain.tld' );

```

--- 

### <span style="color: #42b983;">#</span> setCache( flag [, timestamp] )

Enables or disables the page caching meta tags. The second parameter refers to the `max-cache-age`. Cache is enabled by default with a max-age of 1 hour (3600 ms).

##### Arguments

- `{Boolean} flag: required`
- `{Integer} timestamp: optional`

##### Return

- `void`

##### Example

```php
// disable cache
$app->setCache( false );

// enable cache for 1 day (60 * 60 * 24) ms
$app->setCache( true, 86400 );

```

--- 

### <span style="color: #42b983;">#</span> setLanguage( lang )

Changes the metatag content for page language. Default language is English (`en`).

##### Arguments

- `{String} lang: required`

##### Return

- `void`

##### Example

```php

// change language for Japanese.
$app->setLanguage( 'ja' );

```

--- 

### <span style="color: #42b983;">#</span> setCharset( charset )

Changes the metatag content for page charset encoding. Default language is `UTF-8`.

##### Arguments

- `{String} charset: required`

##### Return

- `void`

##### Example

```php

// change charset.
$app->setCharset( 'utf8' );

```

--- 

### <span style="color: #42b983;">#</span> setOutputType( type )

Customize the output format from the API. The default format is `JSON`, but you can also choose `XML` instead. Other formats are (yet) not supported.

##### Arguments

- `{String} type: required`

##### Return

- `void`

##### Example

```php

// set output format as json
$app->setOutputType( 'json' );

// set output format as xml
$app->setOutputType( 'xml' );

```

--- 

### <span style="color: #42b983;">#</span> setCustomPropertyStatus( name )

Defines a custom property name to return the response http code.

##### Arguments

- `{String} name: required`

##### Return

- `void`

##### Example

```php

// change from status to code
$app->setCustomPropertyStatus( 'code' );

```

--- 

### <span style="color: #42b983;">#</span> setCustomPropertyMessage( name )

Defines a custom property name to return the response message.

##### Arguments

- `{String} name: required`

##### Return

- `void`

##### Example

```php

// change from message to foo
$app->setCustomPropertyMessage( 'foo' );

```

--- 

### <span style="color: #42b983;">#</span> setCustomPropertyData( name )

Defines a custom property name to return the error status.

##### Arguments

- `{String} name: required`

##### Return

- `void`

##### Example

```php

// change from data to bar
$app->setCustomPropertyData( 'bar' );

```

--- 

### <span style="color: #42b983;">#</span> setCustomPropertyError( name )

Defines a custom property name to return the error status.

##### Arguments

- `{String} name: required`

##### Return

- `void`

##### Example

```php

// change from error to fail
$app->setCustomPropertyError( 'fail' );

```

--- 

### <span style="color: #42b983;">#</span> setAdditionalResponseProperty( key [, value, reset] )

Append global additional properties to be returned alongside any response by default. Always the `reset` argument is true, it resets the existing list of additional properties.

##### Arguments

- `{String} key: required`
- `{String|Integer|Boolean|Function} value: optional`
- `{Boolean} reset: optional`

##### Return

- `void`

##### Example

```php

$app->setAdditionalResponseProperty( 'version', '1.0.0', true );

// it also supports chained definitions 
$app
    ->setAdditionalResponseProperty( 'version', '1.0.0' )
    ->setAdditionalResponseProperty( 'phase', 'beta' );

```

--- 

## Available Helpers

Helpers are built-in add-on libraries that supports specific features and/ or third-party services directly from Seed-PHP. Some of the helpers (such as `Mailgun`) requires you to have an account with the vendor.

| Helper Name | Namespace      | Remark
| ----------- | ----------- | -----------
| [Curl](./helper-curl.md)          | `\SeedPHP\Helper\Curl` | A full featured cCurl class.                        |
| [Database](./helper-database.md)  | `\SeedPHP\Helper\Database` | A PDO wrapper.                                      |
| [Http](./helper-http.md)          | `\SeedPHP\Helper\Http` | A helper for working with http responses codes.     |
| [Logger](./helper-logger.md)      | `\SeedPHP\Helper\Logger` | A simple log class. Log data into a mysql database. |
| [Mysql](./helper-mysql.md)        | `\SeedPHP\Helper\Mysql` | A MySQL wrapper. ( `Deprecated since v.1.5.0` )         |
