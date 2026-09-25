# PHP 8 Compatibility Plan

- status: pending
- date: 2026-09-26
- scope: `seed-php` library (root classes + `helper/`)
- target: PHP 8.0 - 8.5, no fatal errors and no first-party deprecations under `E_ALL`
- reviewed: 2026-09-26 by 4 subagent reviewers (fact-check, completeness, version facts, plan quality)

## Goal

Make the library install and run on PHP 8.x. Today `composer install` fails and several
code paths emit PHP 8 deprecations or fatal errors. Local PHP is 8.5.6.

## Decisions (resolved)

- Dynamic helper properties: add `#[\AllowDynamicProperties]` to `Core`. It is inherited by
  `App`, ignored on PHP 8.0/8.1, and preserves the documented `$app-><helper>` API. This is
  the sanctioned PHP 9 migration path, not a stopgap.
- `helper/Mysql.php`: removed on 2026-09-26 (deprecated since 1.0.0, constructor threw).
  Its PHP 8.1 `mysqli` behavior is no longer relevant.
- PHP floor: support PHP 8.0. Declare `"php": ">=8.0"` and add
  `"config": { "platform": { "php": "8.0.0" } }` so the lock stays 8.0-resolvable (Twig 3
  latest requires >=8.1). If PHP 8.0 is not required, use `"php": "^8.1"` and drop the
  platform pin.
- Twig: `"twig/twig": "^3.0"` (done 2026-09-26; locked 3.30.0). Migration risk is low.

## Verification model

A baseline suite now exists (`php tests.php`): 68 pass, 0 fail, 4 skip. Follow TDD for every
fix: add a failing test in `tests.php` first, then fix, then make the suite green. Deprecations
are not asserted yet; add a `php -l` deprecation gate as the first red test.

No CI and no `vendor/`. Verify with:

```sh
php -v && composer validate --strict
composer update --no-interaction --prefer-dist   # regenerates lock and installs

# compile-time: any implicit-nullable deprecation is printed by php -l
for f in App.php Core.php Router.php loader.php index.php helper/*.php; do
  out=$(php -l "$f" 2>&1); echo "$out" | grep -qi deprecated && echo "DEPRECATED $f"
done

# runtime: boot + load helpers under E_ALL (CLI: no getallheaders(), no session)
php -d error_reporting=E_ALL -d display_errors=1 -r '
require "vendor/autoload.php"; require "loader.php";
$app = SeedPHP\App::getInstance();
$app->load("database", ["driver"=>"sqlite","base"=>":memory:"], "db");
echo "runtime OK\n";'

# HTTP smoke (port must be free; /database needs MySQL, /ratelimit starts a session)
php -S 127.0.0.1:8000 index.php & srv=$!
for p in /welcome /sample/foo /xml /hooks /ratelimit /mailgun-twig; do
  curl -sS -o /dev/null -w "%{http_code} $p\n" "http://127.0.0.1:8000$p"; done
kill $srv
```

Notes: `php -l` catches compile-time deprecations only (implicit nullable). Runtime
warnings need execution. `getallheaders()` (`Core.php:101`) is undefined in the plain CLI
SAPI, so boot `App` over `php -S` for anything touching headers. `composer update` also
installs; a separate `composer install` is redundant.

## Workstream 1 - Dependencies and platform

| # | File | Problem | Fix |
| --- | --- | --- | --- |
| 1.1 | `composer.json:31` | Declares `php: >=5.6.0`, but code needs 7.1+ and target is 8.x. | Set `"php": ">=8.0"` (plus platform pin; see Decisions). |
| 1.2 | `composer.json:33` | `twig/twig: ^2.0`. Lock pins `v2.12.5`, which requires `php ^7.0` and blocks install on PHP 8. | Set `"twig/twig": "^3.0"`. |
| 1.3 | `composer.lock` | Stale lock. | `composer update` to regenerate (unlocks twig + symfony polyfills). |
| 1.4 | `composer.json` | No `ext-*` requirements despite hard use of `curl`, `pdo`/`pdo_mysql`, `json`, `mbstring`, `ctype`. | Add `ext-curl`, `ext-pdo`, `ext-json`, `ext-mbstring`, `ext-ctype`. |

Twig 2 -> 3 risk is low. `helper/Mailgun.php` already uses the namespaced API
(`\Twig\Loader\FilesystemLoader` at `:373`, `\Twig\Environment` at `:374`,
`\Twig\Extension\StringLoaderExtension` at `:377`). `helper/mailgun.twig` and
`test/test.twig` use only `{{ }}`, `{% if %}`, `{% for %}`.

Minimal alternative (not recommended, Twig 2 is EOL): keep `^2.0` and `composer update`
to `2.16.1`, the last 2.x, which requires `php >=7.1.3` (so PHP 8 is allowed).

Note: package `rogeriotaques/seed-php` is published (1.9.x). Narrowing the platform to
8.x drops PHP 7 support and is a breaking change. Update the changelog/README accordingly.

## Workstream 2 - Fatal and type errors

| # | File:line | Problem | Version | Fix |
| --- | --- | --- | --- | --- |
| 2.1 | `Router.php:82` | Implicitly nullable `callable $after = null` (verified by `php -l`). | 8.4 dep | `?callable $after = null`. |
| 2.2 | `Core.php:252` | `return false;` inside `load(...): Core`. Throws `TypeError` (verified). | latent, 7.0+ | Return `$this`. (If changing to `: ?Core`, also change `return false;` to `return null;`.) |
| 2.3 | `Database.php:388` | `self::$_transactions` reads a non-static property (`private $_transactions`, `:52`). Throws `Error: Access to undeclared static property`. Also `PDO::execute()` does not exist. | latent, 7.0+ | Use `$this->_transactions`; replace `$this->_resource->execute(...)` with a valid PDO call (`exec()`). |
| 2.4 | `Database.php:60,298,428,474,528` | Implicitly nullable `array $x = null` (verified by `php -l`). | 8.4 dep | Prefix each type with `?` (`?array`). |
| 2.5 | `Database.php:646` | `getLink(): PDO` returns `null` before `connect()` -> `TypeError`. `docs/helper-database.md` documents NULL when not connected. | 7.1+ | Return type `?PDO`, or throw when not connected. |
| 2.6 | `Database.php:655` | `insertedId(): int` calls `lastInsertId()` on a null resource -> `Error`. | 8 fatal | Guard with `is_object($this->_resource)`. |
| 2.7 | `Http.php:181` | `new \ErrorException(..., $code)` with a non-int `$code` (public `getHTTPStatus("abc")`) -> `TypeError`. | 8 fatal | Cast: `(int) $code`. |

## Workstream 3 - Dynamic properties

`Core.php:263` (`$this->$alias = new $class($config);`) and `Core.php:266`
(`$this->$component = $alias;`) create dynamic properties. Deprecated in PHP 8.2
(verified at runtime: "Creation of dynamic property SeedPHP\Core::$curl is deprecated").

- Add `#[\AllowDynamicProperties]` to `class Core` (`Core.php:15`).
- `$app-><helper>` access is public API (`index.php:108,223`; `docs/core.md`), so the
  attribute is preferred. The `$helpers` array + `__get`/`__set` refactor is not a drop-in:
  `load('database', $cfg, 'mysql')` sets both `$this->mysql = <object>` and
  `$this->database = 'mysql'`. Defer that refactor.
- Add a one-line `@property` note so the implicit contract is visible.

## Workstream 4 - Null and undefined-key issues (PHP 8.0/8.1)

| # | File:line | Problem | Version | Fix |
| --- | --- | --- | --- | --- |
| 4.1 | `Http.php:225` | `strpos($_http_x_forwarded_for, ",")` where the var is `null` (`:220`). Verified. | 8.1 dep | Guard with `!empty(...)` or cast `(string)`. |
| 4.2 | `Http.php:181` | `htmlentities($code)` when `getHTTPStatus(null)`. | 8.1 dep | `htmlentities((string) $code)` (see also 2.7 for the `int $code` arg). |
| 4.3 | `Http.php:198` | `$_SERVER['SERVER_PORT']` may be unset. | 8.0 warning | `($_SERVER['SERVER_PORT'] ?? null) == 443`. |
| 4.4 | `Http.php:206` | `$_SERVER['SERVER_NAME']` may be unset. | 8.0 warning | `$_SERVER['SERVER_NAME'] ?? ''`. |
| 4.5 | `Router.php:589` | `htmlspecialchars($dv)`: `null` -> deprecation; nested object -> `TypeError`. | 8.1 / 8.0 | Cast/guard: `htmlspecialchars(is_scalar($dv) ? (string) $dv : json_encode($dv))`. |
| 4.6 | `Router.php:576,577` | `$prop[1]` undefined when an attribute has no `=`; then `addAttribute` gets null. | 8.0 warning | Guard with `isset($prop[1])` before `:576` and `:577`. |
| 4.7 | `Mailgun.php:342` | `str_replace(..., $value, ...)` with `null` value. | 8.1 dep | `(string) $value`. |
| 4.8 | `Mailgun.php:378` | `$vars['seed_php_mailgun_template'] = $temp;` before the `is_array($vars)` check -> `TypeError` for scalar `$vars`. | 8 fatal | Guard: only assign when `is_array($vars)`. |
| 4.9 | `RateLimit.php:71,74,103,132,138` | Reads `last-call` / `ban-count` / counters from the session shape; may be undefined if the shape is partial. | 8.0 warning | Initialize the full per-IP shape in `__construct` (or when absent). |
| 4.10 | `Http.php:194`, `Core.php:342` | `dirname($_SERVER['SCRIPT_NAME'])` with no guard: CLI -> undefined key + `dirname(null)` deprecation. Web SAPI sets it, so this hits CLI/test paths. | 8.0 / 8.1 | Guard: `dirname($_SERVER['SCRIPT_NAME'] ?? '')`. |

Note: `Mailgun.php:406` `strip_tags($this->_message)` was in the first draft; `$_message`
is initialized to `''` and only assigned non-null values, so the null case is unreachable.
Keep as defensive-only.

## Workstream 5 - PHP 8.5 cURL

`curl_close()` is deprecated in PHP 8.5 (a no-op since 8.0).

| # | File:line | Fix |
| --- | --- | --- |
| 5.1 | `Curl.php:287` | Remove the call; `:288` already sets `$this->_ch = null;`. |
| 5.2 | `Mailgun.php:467` | Remove the call. |
| 5.3 | `Curl.php:252,255` | Guard `curl_setopt_array()` / `curl_exec()`: `$this->_ch` is `null` if `create()` was never called -> `TypeError`. Return early if `!$this->_ch instanceof \CurlHandle`. |
| 5.4 | `Curl.php:124` | `CURLOPT_HTTPPROXYTUNNEL . true` produces invalid option key `611`. On PHP 8 `curl_setopt_array()` throws `ValueError` (fatal), so `proxy()` breaks the next call. Fix the concatenation to a comma: `$this->option(CURLOPT_HTTPPROXYTUNNEL, true);`. |

Note: `CURLOPT_FOLLOWLOCATION` (`Curl.php:215`) still behaves as before. Passing `true`
maps to `CURLFOLLOW_ALL`; the new `CURLFOLLOW_*` modes (PHP 8.5) only change behavior if
explicitly passed. No change needed.

## Workstream 6 - Mysql helper (removed)

`helper/Mysql.php` was removed on 2026-09-26. Its PHP 8.1 `mysqli` exception changes are no
longer relevant. `helper/Database.php` (PDO, `ERRMODE_EXCEPTION` at `:250`) is unaffected.

## Known external deprecation (vendor)

`erusev/parsedown 1.7.4` (locked; latest 1.x) declares implicit-nullable params
(`array $Block = null`) and emits PHP 8.4 deprecations when loaded. `composer update`
keeps 1.7.4, so the "no deprecations" goal cannot fully hold without patching/forking.
Mitigation: treat vendor deprecations as out of scope, or silence them via a small
`set_error_handler` in the demo. Confirm after install.

## Adjacent bugs (not PHP 8 blockers, optional)

| File:line | Problem |
| --- | --- |
| `RateLimit.php:125` | `header($str, Http::_TOO_MANY_REQUESTS)` passes `429` as the bool `$replace` arg. Use `header($str, true, 429)`. |
| `Database.php:678` | `return false;` in `_args2string(...): string` (coerced to `""` in weak mode; fatal under `strict_types`). |
| `Http.php:194` | `str_replace(array('\\',''), array('/','%2'), ...)` has an empty-string needle; the `%2` replacement never runs. |
| `.htaccess:5-17` | `Order allow,deny` / `Deny from all` are Apache 2.2 directives, removed in Apache 2.4 (needs `mod_access_compat`). |
| `.php-cs-fixer.php:3` | `PhpCsFixer\Config::create()` was removed in php-cs-fixer 3.x. |

## Suggested order

1. Workstream 1 (composer + Twig + platform), so the app can boot.
2. Workstream 2 (fatal and type errors).
3. Workstream 3 (dynamic properties).
4. Workstream 4 (null and undefined-key issues).
5. Workstream 5 (PHP 8.5 cURL).
6. Vendor deprecation note + optional adjacent bugs.

## Out of scope

- Adding a test suite or CI.
- Rewriting routing, request parsing, the custom autoloader, or the helper-loading contract.
- PHP 5.6 / 7.x support.

## Evidence

Verified with `php -l` and targeted runtime scripts on PHP 8.5.6:
implicit-nullable deprecations in `Router.php:82` and `Database.php` (5 methods);
`TypeError` from `Core::load('')`; `Error` from static access to a non-static property;
`strpos(null)` deprecation in `Http.php:225`; `curl_close()` deprecation; dynamic-property
deprecation in `Core.php:263`; `TypeError` from `curl_setopt_array(null)`.

## Review

Four independent reviewers checked this plan on 2026-09-26:

- Fact-check: all `file:line` claims confirmed except wording (Twig 2.16.1 constraint).
- Completeness: added 2.5-2.7, 4.8, 4.10, 5.3-5.4, 1.4, the vendor note, and adjacent bugs.
- Version facts: PHP 8.2/8.4/8.1/8.5 attributions confirmed against PHP RFCs/manual;
  `#[\AllowDynamicProperties]` confirmed safe on 8.0/8.1.
- Plan quality: verdict "approve with changes"; corrections applied here.
