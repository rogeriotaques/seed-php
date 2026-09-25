# Testing

## Run

- `just test`, or `php tests.php`.
- Requires PHP with `curl`, `pdo_sqlite`, and `session`. No Composer, no vendor, no
  external test framework.
- Exit code is `1` when any test fails, `0` otherwise.

## Files

- `tests.php` - the whole suite. It holds the harness (colored `PASS`/`FAIL`/`SKIP`,
  `assert_*` helpers, `group()`) plus the unit tests. It runs in-process for unit tests.
- `tests/app.php` - a dependency-free router fixture. `tests.php` starts a temporary
  `php -S` server on port 8090 with it to run the HTTP integration tests, then stops it.
- `test/test.twig` - sample template used by the demo.

## Output format

- Groups print as `### <group> ###`.
- Each case prints `PASS`, `FAIL`, or `SKIP`.
- The summary prints `X PASSED. Y FAILED. Z SKIP.` and `TOTAL: N TEST CASES.`.

## TDD is required

Every change follows: write a failing test in `tests.php`, then change the code, then make
the suite green. Do not change behavior without a test that covers it.

- Add new unit cases to the matching `group()`.
- Add new HTTP routes and assertions to `tests/app.php` and the HTTP group.
- Keep the suite green before finishing a task.

## Notes

- The suite sets `error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING)`. Deprecations are
  tracked by the PHP 8 plan, not by this suite.
- Network calls are skipped (`Curl::execute()`, `Mailgun::send()`). Mailgun `parse()` tests
  are skipped when Twig is not installed.
- The server port is 8090. The suite fails the HTTP group if the port is busy.
- Test artifacts live in the system temp dir. The suite leaves no files in the repo.
