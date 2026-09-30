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
- `application/tests/`: PHPUnit suite (`Controllers/`, `Support/`, `bootstrap.php`, `bin/setup-db.php`, `bin/check-schema-parity.php`). One test class per controller, named `<Controller>ControllerTest`; several controllers in `application/controllers/api/` map to `<Controller>ControllerTest` too. It lives inside the document root and `bin/setup-db.php` rebuilds a database, so access is blocked in two places: `application/tests/.htaccess` (Apache) and the `location ^~ /application/ { return 404; }` rule in `docker/etc/nginx/default.conf` and `default.template.conf` (nginx). Nginx does **not** read `.htaccess`, so changing one without the other silently reopens the folder. The `^~` is required, otherwise the `~* \.php$` location is matched first and the files are executed. `ServedPathsTest` enforces the pairing, because the two nginx files are near-copies with no link between them and `docker-compose.yml` renders `default.template.conf` over `default.conf` — the template is the one that is live.
- `tools/`: the XSS gate (`check_view_escaping.php` and `ViewEscaping/`). Blocked the same way, `tools/.htaccess` plus `location ^~ /tools/`. The script refuses to run outside CLI, so nothing here is executable over HTTP, but `tools/xss-baseline.txt` is data: it lists every value that reaches a page without an escaper, with file, expression and line. Serving it publishes a map of what is unescaped, and `ServedPathsTest` covers this folder along with `application/`.

## Testing

The suite runs with `composer test`, which calls `application/tests/bin/setup-db.php` and then
PHPUnit. The connection comes from the environment, and `application/.env` is
optional: `application/tests/bootstrap.php` and `application/tests/bin/setup-db.php` use
`Dotenv::safeLoad()`, so a missing file is not an error. A local `.env` is still
read, and its values win for anything the suite does not set itself.

- `MAPOS_TEST_DB_HOSTNAME`, `MAPOS_TEST_DB_PORT`, `MAPOS_TEST_DB_DATABASE`,
  `MAPOS_TEST_DB_USERNAME` and `MAPOS_TEST_DB_PASSWORD` select the server. They
  default to `127.0.0.1:8989` and `mapos_test`, and the bootstrap resolves them
  (falling back to the `.env` for the credentials) and then publishes all five
  onto the `DB_*` keys the app reads, so a `.env` cannot redirect the suite at the
  development database. All five are published, not just the first three: the
  suite opens two connections, the PDO here and the one the `database` autoloader
  opens while `index.php` boots, and `config/database.php` only knows `$_ENV`. It
  falls back to `enter_db_username` when the key is missing, which passes locally
  — the `.env` fills it — and fails in CI, where there is no `.env`. See
  `TestDatabase::fromEnvironment()` and `TestDatabaseTest`.
- `APP_ENCRYPTION_KEY` and `GLOBAL_XSS_FILTERING` are filled in by the bootstrap
  only when absent, because `config.php` reads both without a fallback and the
  missing key would otherwise log a warning.
- `phpunit.xml` sets `bootstrap="application/tests/bootstrap.php"` and `failOnWarning="true"`.
- The database name **must** end in `_test`; both scripts abort otherwise, so a
  typo cannot wipe the production database.
- `application/tests/bin/setup-db.php` **reuses the schema when it is current** and
  otherwise drops and recreates the database. On a fresh database it builds the
  schema by running the migration chain (`Tools::migrate()`) and loads reference
  data from the canonical seeds in `application/database/seeds/` (`Permissoes`,
  `Usuarios`, `Configuracoes`), the same ones `Tools::seed()` runs. This is
  deliberate: `banco.sql` is all `CREATE TABLE IF NOT EXISTS` with no `DROP`, so
  importing it never exercises a single line of migration, and an install built
  that way would never be caught before it reached a user.
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
- `FrontendBoundaryTest` is the exception to that rule, and on purpose: its child
  `php -S` runs as `production`, because that is the only way to get the real
  routing, so it logs to `application/logs/` like any production request. A case
  asserts the tree stays clean, since a log generated there is what makes
  `format:check` fail on a file nobody edited. Two things used to break that
  assertion, and both are worth knowing before adding a child request:
  `application/config/*.php` reads `APP_ENCRYPTION_KEY`, `GLOBAL_XSS_FILTERING`,
  `API_JWT_KEY` and `API_TOKEN_EXPIRE_TIME` from `$_ENV` **without a fallback**,
  so an absent key is a warning, and a warning is an ERROR-level log line at
  `log_threshold = 1`; and `WhoopsHook::extractEnvNames()` used to `file()` the
  `.env` unconditionally, warning on every install that has none. The child
  therefore gets all five keys from `childEnvironment()` — they come from the
  `$_ENV` that `TestDatabase::fromEnvironment()` published, so a developer's
  `.env` still wins and CI's absence is not a behaviour difference.
  Note `application/.env` is **gitignored**: a test that reads config the app
  only gets from that file passes on a developer machine and fails on every
  runner, which is exactly how `API_ENABLED` (it gates `routes_api.php` in
  `routes.php`) made the API case measure a 404 instead of its 401.
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
  `_test` name guard, the PDO lifecycle and the schema currency check, and
  `TestApplication` owns booting `index.php`, the reentrancy fixups and
  `Tools::migrate()`. The order is fixed: the database must exist before the boot,
  because the `database` autoloader connects while `index.php` boots.
  `TestApplication::migrate()` exists so the
  `resetSharedState()` + `ob_start()` + `migrate()` + `error_string()` sequence
  is written once instead of once per script.

### Maintained schema, per-test data

`setup-db.php` used to drop and recreate the database on every run, which cost
~9.3s of the ~10s a run took: the migration chain is the expensive part, and
running `Tools::migrate()` against an up-to-date schema takes 2ms. The split now
is **keep the schema, clean the data**, and knowing which half a change belongs to
is the point:

- The **schema** is reused when the fingerprint says it is current, which needs
  three things: the database exists, `migrations.version` equals the newest
  migration timestamp, and the fingerprint matches. On a reuse, `setup-db.php` runs
  no seeds at all — the `Usuarios` seed writes an explicit `idUsuarios`, so
  replaying it aborts with 1062 — and instead re-checks the three fixture users.
  `Tools::migrate()` still runs on both paths: it is a 2ms no-op when the schema
  is current, and it is what makes a half-built database self-heal.
- The fingerprint covers every migration, the seeds and `TestFixtures.php`, and it
  is what catches an **edit** to a file that already ran: editing a migration or a
  seed does not change its filename, so the version stays current and a stale
  database would otherwise be approved. It lives in
  `sys_get_temp_dir()/mapos-test-schema/<database>.hash`, not in a table, because
  `check-schema-parity.php` compares every `BASE TABLE` except `migrations` and a
  new table would read as drift.
- `composer test:fresh` (that is, `setup-db.php --fresh`) forces a full rebuild.
  Reach for it when the reuse path is not what you want, and when a test failure
  says the schema is short of something.
- The **data** is cleaned per test by `TransactsDatabase`, which wraps each case in
  a transaction and rolls it back. A class opts into the baseline reinstall by
  overriding `resetsBaselineData()` to return `true`; before each case, inside the
  transaction it just opened, it then deletes `usuarios` and reinstalls the three
  fixture accounts via `TestFixtures::installUsers()`. `DELETE`, never `TRUNCATE`,
  because TRUNCATE is DDL and would implicitly commit the transaction the trait
  just opened. It is opt-in, not automatic, and `TransactsDatabaseTest` must not
  opt in: its two-case pair is the proof that the trait rolls back rather than
  commits, and cleaning `logs` between them would make that proof a tautology.
- `resetBaselineData()` **fails loudly** if `logs` is not empty instead of cleaning
  it, naming `composer test:fresh`. A row there is indistinguishable from a
  committed transaction, and that is the whole point of the trait.
- Do not extend the reinstall to `configuracoes`: 13 of its 14 rows come from the
  seed, but `email_automatico` comes from a migration and no seed recreates it, so
  deleting the table and replaying the seed would drop that row for good.
- CI gains nothing in wall-clock time from this — the runner is clean and pays the
  full migration either way. `quality.yml` runs the setup a second time and
  **requires** the reuse message, because the only way this degrades is silently:
  it would just rebuild everything and pass, slower.

### Running the suite in parallel

`composer test` is single-process and stays that way: it is what CI runs and what
you get by default. `composer test:parallel` is the opt-in ParaTest path, and it
exists because of one hard fact about this suite — `TransactsDatabase` rolls each
case back, and `LoginControllerTest` and `BaselineDataResetTest` both `DELETE` and
re-`INSERT` from `usuarios`, so on a **shared** database they take an X-lock on
InnoDB rows and serialize against each other. Two processes that were meant to run
at once stop running at once.

ParaTest does not isolate databases, so `application/tests/Support/Clone/TestSchemaClone.php`
does. It runs before the app boots, in `bootstrap.php`, and only when `TEST_TOKEN`
is set:

- The **model** is `mapos_test`, the one `setup-db.php` builds. Each worker gets
  its own database named from the token *before* the `_test` suffix —
  `mapos_1_test` — because `assertDatabaseNameIsSafe()` requires that suffix and
  is not negotiable. `TestDatabase::workerDatabaseName()` owns that rule and is
  pure, so it can be tested without a database.
- The clone is `CREATE TABLE ... LIKE` for all 28 tables, `INSERT ... SELECT` to
  copy the data, then a **replay of the 26 foreign keys**. `CREATE TABLE ... LIKE`
  does not copy foreign keys, and the inline form fails because the dependency
  order is wrong (`anexos` references `os`), so the constraints go on afterwards
  in dependency order. The reference is qualified with the worker's own database,
  which is what keeps the constraint inside the worker.
- Reuse is by schema fingerprint, exactly as for the model. The first run of a
  token costs ~3.7s; every later run of that token costs ~2ms. That asymmetry is
  the whole reason the tool is opt-in rather than the default.

**Measure before you raise the worker count.** The cold cost is linear in workers,
because each worker pays its own clone and MySQL serializes DDL. A snapshot on a
253-test suite, same machine, `--processes=4` and serial:

| run | warm | cold |
| --- | --- | --- |
| serial | 2.32s | ~2.0s (not re-measured) |
| 4 workers | 1.37s | 9.88s |

Only the two rows above are current; the earlier 104-test table also had 2, 8 and
28 workers, and those three were **not** re-measured, so treat the shape — linear
in workers when cold, flat when warm — as what they showed and not as a number you
can quote. Re-measure the whole table when the suite changes shape again, and in
particular before changing the pinned count.

Warm is flat because the fingerprints persist, so the number that decides whether
this is worth anything is the *cold* one, and today cold parallel loses to serial
outright: 9.88s against 2.32s, to save 0.95s once the workers exist.
`test:parallel` therefore pins `--processes=4` and **CI does not run it**: a clean
runner would pay four clones to save a fraction of one second. The honest summary
is that this is infrastructure for a suite that has not grown into it yet. Revisit
when the suite is long enough that the test time dominates the clone, and
re-measure the table above when you do.

- Anything that creates a database in a test must put the token in the name.
  `workerDatabaseName()` in `DatabaseGuard` is the one place that derives it, and
  `DatabaseGuardTest` is what covers it — along with
  `TestSchemaCloneForeignKeysTest` and `TestSchemaCloneReproductionTest`, which build
  a worker database. A hardcoded name is a race: every worker drops and
  recreates the same schema, one reads the origin mid-build, and the parity check
  reports a difference that is not there. That bug cost 4 workers 7.1s instead of
  1.5s before it was found.
- `composer test:clean` drops the worker databases the ParaTest runs left behind.
  It never touches the model, and it re-derives each candidate name with
  `workerDatabaseName()` instead of matching a pattern written in the script, so
  it cannot delete a name the clone code could not have produced.
- Credentials are read through `TestDatabase::env()`, which consults `$_ENV`,
  `$_SERVER` and `getenv()` in turn. This is not defensive padding: Dotenv does
  not wire the putenv adapter on every setup, and `$_ENV` follows
  `variables_order`, which arrives **empty in a ParaTest worker** while
  `$_SERVER` is full. Reading `$_ENV` directly worked in the serial suite and
  failed in the parallel one, with the worker opening the database as `root` with
  no password.

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
- The Loader's "view snapshot" must be wiped. `Loader::_ci_load()` gives a view its
  `$this` by copying the controller's object vars onto the Loader, but only the
  vars it does not already have — so the first view of the process freezes a
  snapshot of the first controller, and later controllers never refresh it. A
  library loaded lazily mid-method (e.g. `pagination` in `Auditoria::index()`)
  works on the controller but its render reads the stale snapshot, and the second
  test to render the layout sees the first test's `total_rows`. This only shows
  once a test renders `tema/*`, which is why it was caught by
  `AuditoriaControllerTest`; the fix is `Ci3Introspection::resetLoaderViewAliases()`,
  which unsets every non-`_ci_*` property on the Loader.
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
5. **Language:**
   - Code identifiers (classes, methods, variables, constants, parameters) in English; comments in Portuguese. User-facing strings stay in Portuguese.

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
- Run `composer xss:check` after editing views. It runs four checks: values that reach the page without an escaper, values whose type the escaper changes (such as a `JSON.parse()` fed an escaper), pre-rendered markup wrongly wrapped in an escaper, and output tokens it could not read at all. The last one fails closed: a value the gate could not read is not a value the gate approved. Exit `1` means a finding the baseline does not cover, exit `2` means it read no view at all (wrong path, empty directory, unmounted volume) and wrote nothing, and only then is `0` a gate in order.
- To record a deliberate omission, the order is: run `composer xss:baseline` **first**, then explain the entries it just wrote in `tools/xss-baseline.txt`. Each new entry arrives with a placeholder comment holding the offending snippet; replace it with the reason. The command preserves the header and the explanations of entries that still exist, so a re-run after a legitimate schema change does not destroy the reasoning of the ones you are not touching. What it does overwrite is a comment whose entry no longer exists — that is intentional, since a justification for a finding that is gone is a justification for nothing.

Note: `global_xss_filtering` in `application/config/config.php` is an input
filter, not output encoding. It does not protect values read from the database
and it rewrites legitimate input, so it is not a substitute for the escapers
above.


## Security & Integrity Mandates

- **Secrets:** Never log, print, or commit `.env` files, credentials, API keys, or database dumps.
- **Vulnerabilities:** Security issues must be handled following `SECURITY.md` (report privately to `contato@mapos.com.br`).
