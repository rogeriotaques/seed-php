# Seed-PHP Microframework Documentation

## Curl

Package: `Helper\Curl` <br >
Namespace: `SeedPHP\Helper\Curl`

```php
  $app->load('curl');
  $res = $app->curl->get( 'http://...' );
```

Gives you a simple wrapper to work with curl calls. <br>
An usefull way to make your API consumes third party APIs on background.

#### `create( string<url> : required, array<options> : optional, string<returnType> : optional ) : \SeedPHP\Helper\Curl`

Resets and points the Curl to a new URL.

#### `data( array<options> : required ) : \SeedPHP\Helper\Curl`

Appends a data object to the call.

#### `option( string<code> : required, string<value> : required ) : \SeedPHP\Helper\Curl`

Allows to add new custom options.

#### `proxy( string<url> : required, string<username> : required, string<password> : required ) : \SeedPHP\Helper\Curl`

Whenever your network goes thru proxy, you should provide it here.

#### `cookies( array<params> : required ) : \SeedPHP\Helper\Curl`

#### `credential( string<username> : required, string<password> : required ) : \SeedPHP\Helper\Curl`

Set credentials for your endpoint authentication

#### `header( string<header> : required ) : \SeedPHP\Helper\Curl`

Allows you to add additional headers to the call.

#### `get( string<url> : optional, array<options> : optional ) : \SeedPHP\Helper\Curl`

Performs a GET call

#### `post( string<url> : optional, array<options> : optional ) : \SeedPHP\Helper\Curl`

Performs a POST call.

#### `put( string<url> : optional, array<options> : optional ) : \SeedPHP\Helper\Curl`

Performs a PUT call.

#### `update( string<url> : optional, array<options> : optional ) : \SeedPHP\Helper\Curl`

Performs an UPDATE call.

#### `delete( string<url> : optional, array<options> : optional ) : \SeedPHP\Helper\Curl`

Performs a DELETE call.

#### `run( string<method> : required, string<url> : required, array<options> : optional ) : \SeedPHP\Helper\Curl`

Performs a general call.
