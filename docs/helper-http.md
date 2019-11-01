# Seed-PHP Microframework

## Http

>Package: `Helper\Http` <br >
>Namespace: `SeedPHP\Helper\Http`

Gives you some useful methods to standardise the manipulation of HTTP methods.

```php
  use SeedPHP\Helper\Http;
  $base = Http::getBaseUrl();
```

---

### <span style="color: #42b983;">#</span> getHTTPStatus( code )

> Static method.

Returns an array containing the protocol, response code and response text.

##### Arguments

- `{Integer} code: required`

##### Return

- `{ Array }`

##### Example:

```php
  use SeedPHP\Helper\Http;
  $status = Http::getHTTPStatus(200); // OK
```

---

### <span style="color: #42b983;">#</span> getBaseUrl()

> Static method.

Returns the website/ webapp base URL (e.g https://example.com).

##### Return

- `{ String }`

##### Example:

```php
  use SeedPHP\Helper\Http;
  $base = Http::getBaseUrl(); // E.g https://example.com
```

---

### <span style="color: #42b983;">#</span> getClientIP()

> Static method.

Returns the client IP address.

!> Since this information depends on data provided by the browsers, it may not be precise.

##### Return

- `{ String }`

##### Example:

```php
  use SeedPHP\Helper\Http;
  $ip = Http::getClientIP(); // E.g 127.0.0.1
```
