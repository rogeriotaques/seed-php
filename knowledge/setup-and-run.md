# Setup and run

- `composer install` works on PHP 8.1+ since `twig/twig` moved to `^3.0` (locked 3.30.0).
  On PHP 8.0 it fails, because the latest Twig 3 requires PHP >=8.1.
- `composer.json` still declares `php >=5.6`, which is stale. The PHP 8 plan (Workstream 1)
  tracks the platform fix. The code uses PHP 7.1+ features (nullable types, `\Throwable`,
  scalar type hints). Do not write 5.6-compatible code.
- `index.php` is a demo/sample app. It has hardcoded DB credentials and test routes.
  Do not treat it as a production entrypoint.
- Run the test suite with `just test` or `php tests.php`. See `knowledge/testing.md`.
- There is no CI and no lint/format script. Do not invent `composer test`.
