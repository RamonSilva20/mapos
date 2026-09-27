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

## Coding Standards & Guidelines

1. **Code Formatting:**
   - Always format PHP code using project standards: `composer format` (which invokes `application/vendor/bin/php-cs-fixer fix`).
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
| `esc_js($v)` | JS string / value inside `<script>` | `var x = <?= esc_js($v) ?>;` |
| `esc_json($v)` | Arrays and structs inside `<script>` | `var cfg = <?= esc_json($arr) ?>;` |
| `esc_url($v)` | `href` / `src`; rejects `javascript:`, `data:` | `href="<?= esc_url($link) ?>"` |
| `esc_css($v)` | Values inside a `style` attribute | `style="color: <?= esc_css($c) ?>"` |
| `esc_msg($v)` | Flashdata messages shown in a SweetAlert2 popup | `Swal.fire({ text: <?= esc_msg($m) ?> })` |
| `printSafeHtml($v)` | **Only** rich text from a WYSIWYG field (HTMLPurifier) | `<?= printSafeHtml($os->defeito) ?>` |
| *none* | A value that already holds finished markup | `<?= $modalGerarPagamento ?>` |

Note on `esc_url()` and `src`: it rejects `data:`, so an `<img src>` holding a
data URI must use `esc()`, not `esc_url()`. The QR codes rendered by
`getQrCode()` are data URIs, and passing them through `esc_url()` returns an
empty string and silently drops the image.

Additional rules:

- Escape inside string concatenation part by part: `<?= esc($a->rua) . ', ' . esc($a->numero) ?>`.
- In `<script>`, prefer `Swal.fire({ text: ... })` over `title:`/`html:` — those are parsed as HTML.
- `esc()` inside `value="…"` is transparent to JavaScript, because the browser decodes entities before `.val()` returns the value.
- `esc_js()` and `esc_json()` already include the surrounding quotes for scalars, so never add manual quotes: `"<?= esc_js($v) ?>"` emits `"\"value\""`. Write `<?= esc_js($v) ?>` bare.
- For the same reason, never pass them to `JSON.parse()` — assign the value directly: `var cfg = <?= esc_json($arr) ?>;`.
- `esc_json()` casts scalars to string, so an int or float arrives as a JS string, not a number. Only `bool` and `null` keep their own type (`true`/`false`/`null`). Arrays and objects are emitted as a JSON literal.
- Values that intentionally carry pre-rendered markup (`$topo`, `$custom_error`, `$modalGerarPagamento`, and anything from `printSafeHtml()`) must not be escaped again — escape them where they are built instead. These come from `$this->load->view($name, $data, true)` or from markup the controller assembled, so they are already finished HTML. Escaping one is not redundant, it is destructive: `htmlspecialchars()` turns the markup into visible text **and neutralises any `<script>` nested inside it**, which silently disables whatever that view was included for.
- Build URLs with `rawurlencode()` on each query value, then pass the result through `esc_url()`.
- Run `composer xss:check` after editing views. It runs three checks: values that reach the page without an escaper, values whose type the escaper changes (such as a `JSON.parse()` fed an escaper), and pre-rendered markup wrongly wrapped in an escaper. If an omission is deliberate, record it with `composer xss:baseline` and explain it in `tools/xss-baseline.txt`.

Note: `global_xss_filtering` in `application/config/config.php` is an input
filter, not output encoding. It does not protect values read from the database
and it rewrites legitimate input, so it is not a substitute for the escapers
above.


## Security & Integrity Mandates

- **Secrets:** Never log, print, or commit `.env` files, credentials, API keys, or database dumps.
- **Vulnerabilities:** Security issues must be handled following `SECURITY.md` (report privately to `contato@mapos.com.br`).
