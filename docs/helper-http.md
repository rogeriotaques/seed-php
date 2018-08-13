# Seed-PHP Microframework Documentation

## Http

Package: `Helper\Http` <br >
Namespace: `SeedPHP\Helper\Http`

```php
  $app->load('http');
  $base = $app->http->getBaseUrl();
```

Gives you some useful methods to standardise the manipulation of HTTP methods.

#### `getHTTPStatus( integer<code> : required ) : array`

Returns an array containing the protocol, response code and response text.

#### `getBaseUrl() : string`

Returns the project base URL.

#### `getClientIP() : string`

Returns the most probably client IP address.

Since this information depends on data provided by browser, it may not be precise.
