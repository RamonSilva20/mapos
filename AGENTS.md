# Guidelines for AI Agents (Map-OS)

This document provides instructions and technical guidelines for AI agents working in the Map-OS repository.

## Project Overview

Map-OS is an open-source Service Order and Business Management system built in PHP using the CodeIgniter 3 framework.

## Tech Stack & Requirements

- **Language:** PHP >= 8.4
- **Framework:** CodeIgniter 3 (`application/`)
- **Database:** MySQL / MariaDB (managed via CodeIgniter Query Builder and Migrations in `application/database/migrations/`)
- **Dependency Manager:** Composer (vendor directory configured at `application/vendor`)

## Architecture & Directory Structure

- `application/controllers/`: Request handlers and business logic entry points.
- `application/models/`: Data access layer and database operations.
- `application/views/`: Frontend view templates (Bootstrap / Matrix Admin based).
- `application/libraries/`: Custom libraries, REST controllers, and payment gateway integrations.
- `application/config/`: Configuration files (`config.php`, `database.php`, `routes.php`, etc.).
- `application/database/migrations/`: Database schema migration files.
- `docker/`: Docker Compose configuration for local development.
- `application/tests/`: PHPUnit suite (`Controllers/`, `Support/`, `bootstrap.php`, `bin/setup-db.php`, `bin/check-schema-parity.php`). One test class per controller, named `<Controller>ControllerTest`; several controllers in `application/controllers/api/` map to `<Controller>ControllerTest` too. It lives inside the document root and `bin/setup-db.php` rebuilds a database, so access is blocked in two places: `application/tests/.htaccess` (Apache) and the `location ^~ /application/ { return 404; }` rule in `docker/etc/nginx/default.conf` and `default.template.conf` (nginx). Nginx does **not** read `.htaccess`, so changing one without the other silently reopens the folder. The `^~` is required, otherwise the `~* \.php$` location is matched first and the files are executed.

## Testing

The suite runs with `composer test`, which calls `application/tests/bin/setup-db.php` and then
PHPUnit. The connection comes from the environment, and `application/.env` is
optional: `application/tests/bootstrap.php` and `application/tests/bin/setup-db.php` use
`Dotenv::safeLoad()`, so a missing file is not an error. A local `.env` is still
read, and its values win for anything the suite does not set itself.

- `MAPOS_TEST_DB_HOSTNAME`, `MAPOS_TEST_DB_PORT`, `MAPOS_TEST_DB_DATABASE`,
  `MAPOS_TEST_DB_USERNAME` and `MAPOS_TEST_DB_PASSWORD` select the server. They
  default to `127.0.0.1:8989` and `mapos_test`, and the bootstrap maps them onto
  the `DB_*` keys the app reads, before the immutable Dotenv runs, so a `.env`
  cannot redirect the suite at the development database.
- `APP_ENCRYPTION_KEY` and `GLOBAL_XSS_FILTERING` are filled in by the bootstrap
  only when absent, because `config.php` reads both without a fallback and the
  missing key would otherwise log a warning.
- `phpunit.xml` sets `bootstrap="application/tests/bootstrap.php"` and `failOnWarning="true"`.
- The database name **must** end in `_test`; both scripts abort otherwise, so a
  typo cannot wipe the production database.
- `application/tests/bin/setup-db.php` **drops and recreates** the database, then
  builds the schema by running the migration chain (`Tools::migrate()`) and loads
  reference data from the canonical seeds in `application/database/seeds/`
  (`Permissoes`, `Usuarios`, `Configuracoes`), the same ones `Tools::seed()` runs.
  This is deliberate: `banco.sql` is all `CREATE TABLE IF NOT EXISTS` with no
  `DROP`, so importing it never exercises a single line of migration, and an
  install built that way would never be caught before it reached a user.
- It adds two users on top of the seeded reference data: `inativo@admin.com`
  (`situacao` 0) and `expirado@admin.com` (`dataExpiracao` in the past), so the
  two rejection paths in `Login` are coverable. Their password is copied from the
  row the `Usuarios` seed just wrote, not repeated here, so the two cannot drift.
  All three accounts use the password `123456`.
- `application/tests/bin/check-schema-parity.php` builds a second database from
  `banco.sql` and compares it against the migration-built one, table by table and
  column by column, failing on any difference. It is the barrier that keeps the
  installer's dump and the migration chain from drifting apart, since nothing
  else exercises both. `composer check:parity` runs it; CI runs it too. Note it
  compares table and column **types** only, not indexes, foreign keys, charset or
  collation, and it excludes the `migrations` control table.
- The suite writes its CI3 logs to a temporary directory, not to `application/logs`
  (`application/config/testing/config.php`). The logs are runtime output, and
  leaving them in the source tree made `format:check` fail on a generated file.
  The PHPUnit result cache lives in `.phpunit.cache/`. Nothing about the suite
  needs an exception in `.php-cs-fixer.php` anymore.
- `application/config/testing/config.php` and `.../routes.php` are loaded because
  `ENVIRONMENT` is `testing`, and CI3 includes those two files *after* their
  production counterparts. `routes.php` is the one to remember: it points
  `default_controller` and `404_override` at the inert `Phpunit` controller, so the
  boot request neither touches data nor lets a `show_404()` `exit()` kill the
  PHPUnit process.
- `failOnDeprecation` stays `false`. The suite would otherwise fail on deprecations
  raised inside CI3 and its dependencies, which this project does not control.
  Deprecations in *our* code are still fixed at the source, as with the null-safe
  `Login::chk_date()`.
- Two support classes, split by lifecycle: `TestDatabase` owns credentials, the
  `_test` name guard and the PDO lifecycle, and `TestApplication` owns booting
  `index.php`, the reentrancy fixups and `Tools::migrate()`. The order is fixed:
  the database must exist before the boot, because the `database` autoloader
  connects while `index.php` boots. `TestApplication::migrate()` exists so the
  `resetSharedState()` + `ob_start()` + `migrate()` + `error_string()` sequence
  is written once instead of once per script.

Tests are **in-process**: the app boots once inside `application/tests/bootstrap.php` and each
test instantiates the controller directly, because restarting the CI3 lifecycle per
test is not possible. That exposes reentrancy bugs in the framework which
`ControllerTestCase` works around, and any new test touching controllers must keep
using it:

- `TestApplication::resetSharedState()` must run before each controller is
  constructed. `CI_Controller::__construct()` walks `is_loaded()` and calls
  `load_class()` with the bare class name, and `load_class()` only looks for a
  lowercase `APPPATH/libraries/<name>.php` / `BASEPATH/libraries/<name>.php`. That
  misses every class the Loader instantiated some other way: the core `Session`
  (really `system/libraries/Session/Session.php`, class `CI_Session`), the
  app's `Permission` (no `CI_` prefix) and `Form_validation` (the Loader
  injects `$this->CI`, so via `load_class()` it arrives null).
- `Loader::_ci_models` must be cleared too. `Loader::model()` returns early on
  `in_array($name, $this->_ci_models, TRUE)` *before* attaching the model, so from
  the second controller onwards `$this->Some_model` is null.
- `ignoringCliHeaderWarnings()` wraps the one call left that hits `setcookie()`:
  `csrf_verify()` inside the CI3 `Security` library. It warns in CLI and Whoops
  turns that into an exception. It must restore with `restore_error_handler()`,
  never `set_error_handler($previous)`: the latter pushes another entry onto the
  stack and PHPUnit reports a leaked handler, flagging every test as risky. No
  app code needs this path any more — `Login::verificarLogin()` sends its CORS
  headers through `CI_Output::set_header()`, which only emits at the end of the
  request.

Known limits of the in-process approach:

- The `CI_Session` library aborts under CLI, so session assertions read `$_SESSION`
  only; nothing is persisted to `ci_sessions`.
- An **invalid** CSRF token cannot be tested: `Security::csrf_verify()` calls
  `show_error(403)`, which ends the process. Only the accepting path is covered.
- A controller must be refactored away from `echo`/`exit` to be testable, so it builds
  the payload and returns it through `$this->output` (see `Login::verificarLogin()`).
- A view must not declare a function or class at the top level. The CI3 `include`s the
  view on every render, so a second render in the same process dies with "Cannot
  redeclare". Guard it with `function_exists()` (as `saudacao()` does in
  `views/mapos/login.php`) or move it to a helper. This never showed up in web
  requests, where the process ends with the response, but it breaks the suite and
  any queue worker that renders the same view.
- `callControllerRaw()` returns the body as-is, for pages. `callController()` wraps
  it and requires JSON.

## Coding Standards & Guidelines

1. **Code Formatting:**
   - Always format PHP code using project standards: `composer format` (which invokes `application/vendor/bin/php-cs-fixer fix`).
   - Run `composer test` after changing a controller, a model, or anything else that touches the database (see [Testing](#testing)).
2. **Security & Input/Output Handling:**
   - Always validate user input.
   - **Always escape output in views.** Use the helpers in `application/helpers/general_helper.php`, picking the one that matches the output context (see table below). Never `echo` a database row, `$_GET`/`$_POST` value, session value or config value raw.
   - Never use raw SQL string concatenation; use CodeIgniter Query Builder or query bindings (`?` or `$this->db->where()`) to prevent SQL injection.
   - Never expose sensitive data (e.g., password hashes) in public models or API responses.
3. **Database Changes:**
   - Schema modifications must be implemented via migrations (`application/database/migrations/`), never by editing `banco.sql` directly.
4. **Commit Messages:**
   - Follow [Conventional Commits](https://www.conventionalcommits.org/): `feat`, `fix`, `docs`, `refactor`, `chore`, etc.

## Output Escaping in Views

Pick the escaper by the context the value lands in. Using the wrong one is a
common source of XSS, so match the row, not just the habit.

| Helper | Use for | Example |
| --- | --- | --- |
| `esc($v)` | HTML text and quoted attributes | `<td><?= esc($row->nome) ?></td>`, `value="<?= esc($row->nome) ?>"` |
| `esc_json($v)` | Any value inside `<script>`: literals and structs alike | `var x = <?= esc_json($v) ?>;` |
| `esc_url($v)` | `href` only; rejects `javascript:`, `data:` | `href="<?= esc_url($link) ?>"` |
| `esc_img_src($v)` | `<img src>`; rejects `javascript:`, and `data:` unless it is `data:image/` | `src="<?= esc_img_src($logo) ?>"` |
| `esc_css($v)` | Values inside a `style` attribute | `style="color: <?= esc_css($c) ?>"` |
| `esc_msg($v)` | Flashdata messages shown in a SweetAlert2 popup | `Swal.fire({ text: <?= esc_msg($m) ?> })` |
| `printSafeHtml($v)` | **Only** rich text from a WYSIWYG field (HTMLPurifier) | `<?= printSafeHtml($os->defeito) ?>` |
| *none* | A value that already holds finished markup | `<?= $modalGerarPagamento ?>` |

`esc_scalar($v)` is not a row in that table: no view should call it. It is the
single question every escaper above asks first — "is this one renderable text
value?" — answering `null` for `null`, `bool`, `array` and `object`, after
which each escaper picks the empty form of its own medium (`''`, `""` or
`null`). Adding a seventh escaper means asking `esc_scalar()`, not writing
another copy of the type guard. A `bool` reaching a view is a bug at the call
site: choose the text in a ternary (`$row->ativo ? 'Sim' : 'Nao'`) rather than
letting `esc()` guess between `''` and `'1'`.

Note on `esc_url()` and `src`: `esc_url()` is for `href` and rejects `data:`,
which is the right call there — clicking a `data:text/html` link executes it.
It is the wrong call for an `<img src>`, because the QR codes rendered by
`getQrCode()` are data URIs: run through `esc_url()` they return an empty string
and the image silently disappears. That is why `esc_img_src()` exists as a
separate function rather than as a flag: the two contexts answer different
questions, and a single function would have to be wrong in one of them. Use
`esc_img_src()` for every `<img src>`; do not fall back to `esc()`, which lets
`javascript:` through.

Additional rules:

- Escape inside string concatenation part by part: `<?= esc($a->rua) . ', ' . esc($a->numero) ?>`.
- In `<script>`, prefer `Swal.fire({ text: ... })` over `title:`/`html:` — those are parsed as HTML.
- `esc()` inside `value="…"` is transparent to JavaScript, because the browser decodes entities before `.val()` returns the value.
- `esc_json()` already emits the surrounding quotes for a scalar, so never add manual quotes: `"<?= esc_json($v) ?>"` emits `"\"value\""`. Write `<?= esc_json($v) ?>` bare. It emits a bare JSON *value*, not a JS string, which is why the name says `json`.
- For the same reason, never pass it to `JSON.parse()` — assign the value directly: `var cfg = <?= esc_json($arr) ?>;`.
- `esc_json()` casts scalars to string, so an int or float arrives as a JS string, not a number. Only `bool` and `null` keep their own type (`true`/`false`/`null`). Arrays and objects are emitted as a JSON literal.
- Values that intentionally carry pre-rendered markup (`$topo`, `$custom_error`, `$modalGerarPagamento`, and anything from `printSafeHtml()`) must not be escaped again — escape them where they are built instead. These come from `$this->load->view($name, $data, true)` or from markup the controller assembled, so they are already finished HTML. Escaping one is not redundant, it is destructive: `htmlspecialchars()` turns the markup into visible text **and neutralises any `<script>` nested inside it**, which silently disables whatever that view was included for.
- Build URLs with `rawurlencode()` on each query value, then pass the result through `esc_url()` (or `esc_img_src()` when the URL lands in an `<img src>`).
- `clean_url()` and `url_scheme()` are the shared lower half of both URL escapers. Call them only when you need to decide something the escapers do not already decide; reaching for them in a view is usually a sign a third context wants its own function.
- Run `composer xss:check` after editing views. It runs three checks: values that reach the page without an escaper, values whose type the escaper changes (such as a `JSON.parse()` fed an escaper), and pre-rendered markup wrongly wrapped in an escaper. If an omission is deliberate, record it with `composer xss:baseline` and explain it in `tools/xss-baseline.txt`.

Note: `global_xss_filtering` in `application/config/config.php` is an input
filter, not output encoding. It does not protect values read from the database
and it rewrites legitimate input, so it is not a substitute for the escapers
above.


## Security & Integrity Mandates

- **Secrets:** Never log, print, or commit `.env` files, credentials, API keys, or database dumps.
- **Vulnerabilities:** Security issues must be handled following `SECURITY.md` (report privately to `contato@mapos.com.br`).
