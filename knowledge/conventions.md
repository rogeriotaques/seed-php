# Conventions

- 4-space indent, UTF-8, final newline. See `.editorconfig`.
- `.php-cs-fixer.php` uses the old v2 API (`Config::create()`) and only the rules
  `braces` and `array_indentation`. The fixer is not installed. Do not run it blindly.
- Docs are docsify under `docs/`.
  - A new helper page needs an entry in `docs/_sidebar.md`.
  - It also needs a row in the helpers table in `docs/core.md`.
- Keep the existing PHPDoc blocks and `@since` version tags.
