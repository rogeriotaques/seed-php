# Memory

Checkpoint memory for this repository. Read it at session start. Update it at session end.

## Status

- State: ready
- Last updated: 2026-09-26

## Durable facts

- Namespace is `SeedPHP`. Entry point is `SeedPHP\App::getInstance()`.
- Test suite: `just test` or `php tests.php` (vanilla PHP, no framework). TDD is required.
- No CI, no lint script.
- Topic knowledge lives in `knowledge/`. `AGENTS.md` is the index.
- Plans live in `knowledge/plans/`, named `yyyy-mm-dd_<topic>.md`.
- PHP 8 migration is not done. Plan: `knowledge/plans/2026-09-26_php8-compatibility.md`.
- `composer install` works on PHP 8.1+ (`twig/twig ^3.0`, locked 3.30.0). `vendor/` is
  installed. Twig 3 is required because all Twig 2.x releases have security advisories.
- `helper/Mysql.php` was removed (deprecated since 1.0.0, constructor threw).

## Sessions

### 2026-09-26 - Twig installed, Mailgun tests un-skipped

- `composer update` moved `twig/twig` to `^3.0` (locked 3.30.0) and `erusev/parsedown` to
  1.8.0. Twig 2.x is blocked by security advisories. `vendor/` now exists.
- `tests.php` loads `vendor/autoload.php` when present. Mailgun `parse()` (Twig + Markdown)
  and `Mailgun::send()` (loopback via reflection on `_apiBase`) now run.
- Suite: 78 pass, 0 fail, 0 skip. No skips left.
- This starts the PHP 8 plan Workstream 1 (twig part); `php >=5.6` and the platform pin
  remain pending.

### 2026-09-26 - Curl tested against loopback

- Removed the `Curl::execute()` skip. Added a live-server group that runs `Curl::execute()`,
  `Curl::get()`, and `Curl::post()` against the existing `php -S` test server.
- No stub and no external network. Only `Mailgun::send()` and Mailgun `parse()` (no Twig)
  remain skipped.
- Suite: 74 pass, 0 fail, 2 skip (76 cases).

### 2026-09-26 - Logger test now runs (sqlite)

- Un-skipped `Logger::log()`. The test creates a temporary SQLite file, creates the
  `log_usage` table, inserts an entry through `Logger`, verifies it, and deletes the file.
- Teardown is guaranteed via `register_shutdown_function` plus an explicit unlink.
- Suite: 70 pass, 0 fail, 3 skip (73 cases).

### 2026-09-26 - Removed Mysql helper (TDD)

- Deleted `helper/Mysql.php` and `docs/helper-mysql.md`.
- Updated references: `tests.php`, `index.php`, `docs/core.md`, `docs/_sidebar.md`,
  `docs/index.html`, `knowledge/architecture.md`, and the PHP 8 plan (Workstream 6).
- TDD: failing test first (`tests.php` asserts the class is gone), then removal, then green.
- Suite: 67 pass, 0 fail, 4 skip (71 cases).

### 2026-09-26 - Test suite added (TDD)

- Replicated the `tasssks` vanilla-PHP test style: `tests.php` + `tests/app.php` fixture
  + `Justfile` `test` recipe. Run with `just test` or `php tests.php`.
- Baseline: 68 pass, 0 fail, 4 skip (72 cases). No Composer/vendor needed.
- HTTP tests spin a `php -S` server on port 8090 with `tests/app.php`.
- TDD is now mandatory. Added `knowledge/testing.md` and updated `AGENTS.md`.
- Quirk found: `App::getInstance()` returns a `Core` instance, not `App` (`instanceof App`
  is false). Tests assert `instanceof Core`.

### 2026-09-26 - Plan review by subagents

- 4 reviewers checked `knowledge/plans/2026-09-26_php8-compatibility.md`:
  fact-check, completeness, PHP version facts, plan quality.
- Verdict: approve with changes. Plan updated in place.
- Key additions: `Database.php:646,655`, `Http.php:194`/`Core.php:342`, `Curl.php:124`
  is a PHP 8 `ValueError` (not cosmetic), `Mailgun.php:378`, `parsedown` vendor deprecation,
  resolved decisions (dynamic-property attribute, PHP floor + platform pin). Mysql was kept
  then and removed in the next session.

### 2026-09-26 - PHP 8 compatibility review

- Reviewed the whole codebase for PHP 8.0-8.5 issues with subagents and runtime checks.
- Wrote plan `knowledge/plans/2026-09-26_php8-compatibility.md` (status: pending).
- Verified with PHP 8.5.6: implicit nullable deprecations, `Core::load('')` TypeError,
  static access to non-static property, `strpos(null)` deprecation.

### 2026-09-26 - Knowledge base split

- Split `AGENTS.md` into topic files under `knowledge/`.
- Added `knowledge/memory.md` as checkpoint memory.
- `AGENTS.md` is now a table of contents.
