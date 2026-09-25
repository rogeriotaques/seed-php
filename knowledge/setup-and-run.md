# Setup and run

- `composer install` fails on PHP 8. Reason: `composer.lock` pins `twig/twig v2.12.5`,
  which requires PHP `^7.0`.
  - Use `composer update`, or `composer install --ignore-platform-reqs`.
- `composer.json` declares `php >=5.6`, but the code uses PHP 7.1+ features
  (nullable types, `\Throwable`, scalar type hints). Do not write 5.6-compatible code.
- `index.php` is a demo/sample app. It has hardcoded DB credentials and test routes.
  Do not treat it as a production entrypoint.
- Run the test suite with `just test` or `php tests.php`. See `knowledge/testing.md`.
- There is no CI and no lint/format script. Do not invent `composer test`.
