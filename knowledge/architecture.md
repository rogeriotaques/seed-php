# Architecture

## Autoloader

- `loader.php` is a custom autoloader. It is registered through composer `autoload.files`.
  There is no PSR-4.
- It maps a namespace to a file: `SeedPHP\Helper\Http` -> `helper/Http.php`,
  `SeedPHP\Core` -> `Core.php`.
- The base dir is `dirname(__DIR__)`. The package folder must be named `seed-php`.
- It tries CamelCase, then dashed, then all-lowercase file names.
  It throws `Exception` if none exist.

## Helpers

- Add a helper as `helper/<Name>.php` with namespace `SeedPHP\Helper`.
- Load it with `$app->load('name', $config, $alias)`.
- `load()` camelfies the name. A 3rd arg registers an alias.

## Routing

- Routes are raw regex, anchored `@^{uri}$@` against the request URI.
- Order matters. Put longer patterns last.
- Set methods with `'GET|POST /path'`.
- Hooks: `route($route, $callback, $before, $after)`.

## Request and response

- `Core::buildRequest()` does non-obvious URI work:
  it strips the base path and trailing slash, splits args, pairs even args into an
  assoc array, and swaps numeric verb/id. Change it with care.
- `Router::response($code, $data, $output)`: default `json`, also `xml`.
  `$output = false` returns the result without echoing.
- HTTP status constants live in `SeedPHP\Helper\Http` (e.g. `Http::_OK`).

## Helpers status

- `Mysql` helper is deprecated since 1.5.0. Prefer `Database` (PDO wrapper).
- `Mailgun` needs Twig.
