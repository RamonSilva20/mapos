# Guidelines for AI Agents (Map-OS)

This document provides instructions and technical guidelines for AI agents working in the Map-OS repository.

## Project Overview

Map-OS is an open-source Service Order and Business Management system built in PHP using the CodeIgniter 3 framework.

## Tech Stack & Requirements

- **Language:** PHP >= 8.5
- **Framework:** CodeIgniter 3 (`application/`)
- **Database:** MySQL / MariaDB (managed via CodeIgniter Query Builder and Migrations in `application/database/migrations/`)
- **Dependency Manager:** Composer (vendor directory configured at `application/vendor`)
- **Front-end Build:** Tailwind CSS v4 and the JS libraries (Alpine.js, Tom Select, flatpickr, IMask, SweetAlert2, Chart.js 4, FullCalendar 6) via npm (`package.json`, Node >= 22). Only needed to change views, CSS or JS libraries; installing Map-OS never requires Node.

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
   - Always escape output in views with `e()` (`application/helpers/escape_helper.php`): `<?= e($cliente->nome) ?>`. Trusted HTML goes through `printSafeHtml()` (HTMLPurifier), never raw.
   - CI runs `php scripts/check-escape.php`, which fails on any new unescaped `<?= $x ?>`/`echo $x` in `application/views/`. Existing occurrences are counted per file in `escape-baseline.json`; never raise those counts to make CI pass — escape the output instead.
   - Never use raw SQL string concatenation; use CodeIgniter Query Builder or query bindings (`?` or `$this->db->where()`) to prevent SQL injection.
   - Never expose sensitive data (e.g., password hashes) in public models or API responses.
   - Every login entry point goes through `Limite_login` (`application/libraries/Limite_login.php`): check `bloqueado()` before verifying the password, call `registrarFalha()` on a wrong e-mail or password and `registrarSucesso()` on success, and answer with the same generic message for an unknown e-mail and a wrong password.
3. **Database Changes:**
   - Schema modifications must be implemented via migrations (`application/database/migrations/`), never by editing `banco.sql` directly.
4. **Commit Messages:**
   - Follow [Conventional Commits](https://www.conventionalcommits.org/): `feat`, `fix`, `docs`, `refactor`, `chore`, etc.

## Design System

- **`DESIGN.md` is the source of truth for the v5 UI** — colors, typography, components, states, dark mode, and responsive behavior. Read it before touching any view, component, or CSS, and reference its token and component names (`{colors.primary}`, `button-primary`, `pill-status`, ...).
- When existing code diverges from `DESIGN.md`, the document wins. The foundation is being aligned in epic #2911 (done: tokens #2912, no accent picker #2913, local fonts #2914, Lucide icons #2915, components #2916, sidebar/topbar #2917, logins #2918, sticker illustrations #2919); don't build new screens on the parts it replaces.
- Non-negotiables:
  - One action color: orange `#F37338` with a dark ink label (`{colors.on-primary}`), never white text on it. One filled `button-primary` per screen; secondary actions use `button-outline`/`button-ghost`.
  - Orange means "act here" only. States use the semantic status palette (success, warning, danger, info, progress, neutral) as a `pill-status` with its word; red is only for errors and destructive actions (`button-danger` inside `modal-confirm`).
  - A thin orange indicator (active sidebar item, tab, pagination) always has a second cue: weight 600 text and the `{colors.primary-tint}` background.
  - Data (table cells, values, typed input text) is weight 400; labels and names are 500 (`{typography.label-md}`).
  - Form controls use the `{colors.hairline-input}` border and implement the focused, error, and disabled states from the spec.
  - Dark mode always switches the whole screen (Panel Dark Mode tokens), never a single band.
  - Only design tokens — no fixed colors, sizes, or fonts outside `assets/src/tokens.css`. Icons are Lucide; fonts (Rubik, Space Grotesk) are served locally.
- Changing the design system is a PR that edits `DESIGN.md` first — with recalculated WCAG contrast ratios — and then the code.

## Front-end Build

- Source CSS lives in `assets/src/` (`app.css` for new screens, `layout.css` for the admin shell, shared `tokens.css`); the compiled output in `assets/dist/` **is committed**.
- After changing any view or CSS, run `npm ci && npm run build` and commit the `assets/dist` changes. CI rebuilds and fails if the committed output is stale.
- Tailwind scans `application/views/**/*.php`: write full class names in PHP, never build them by string concatenation.
- JS libraries are copied by `npm run build:vendor` (`scripts/build-vendor.mjs`, part of `npm run build`) into `assets/vendor/<lib>/`, which **is committed** and fully generated: never edit it by hand. To add or update a library, pin it with `npm install --save-exact` and list its files in `scripts/build-vendor.mjs`. Do not add new jQuery-dependent libraries.
- Never commit `node_modules/`.
- New views use only the design tokens from `assets/src/tokens.css` (`bg-surface`, `bg-surface-subtle`, `text-text`, `text-muted`, `border-border`, `border-input`, `bg-primary` + `text-on-primary`, `text-danger-ink` on `bg-danger-soft`, ...; the file header lists them). Tailwind's default palette is disabled, so fixed colors such as `bg-white` don't exist. The only theme setting is `app_tema_modo` (claro/escuro/sistema); the layout prints it with `temaAtributosHtml()` (`tema_helper.php`). There is no accent color: the brand has a single primary color (#2913).
- No inline `<script>` code in views. Page JS lives in ES modules under `assets/js/modules/<folder>/<name>.js` (no bundler); the view attaches it with `<?= js_module('folder/name') ?>` on an element and passes data via `data-*` attributes or `<?= page_data('id', $data) ?>` (`application/helpers/js_helper.php`). `assets/js/app.js` loads the modules. AJAX goes through `get()`/`post()` in `assets/js/lib/http.js` (CSRF token, `X-Requested-With`, 403 handling).
- `php scripts/check-inline-script.php` (run in CI through PHPUnit) fails on new inline `<script>` blocks; legacy ones are counted in `inline-script-baseline.json`. Never raise those counts. JS utilities are tested with `npm run test:js`.
- New views build UI from the component library instead of hand-written markup: `<?= component('button', ['label' => 'Salvar', 'type' => 'submit']) ?>` (partials in `application/views/components/`, props and validation in `application/helpers/componente_helper.php`). Components escape every value; slots (`body`, `footer`, `actions`, `message`, table cells) only accept HTML as an `HtmlSeguro` (output of `component()` or `html_purificado()`), never as a string. Never build an `HtmlSeguro` by hand from user data. Extra attributes go in the `attrs` prop; `on*` handlers are rejected. The catalog is at `/index.php/componentes` in `development` only.
- The component library follows the `DESIGN.md` specs: `button` (variants primary, outline, ghost, danger, inverted, ghost-on-dark, link — one primary per screen; inverted/ghost-on-dark only on dark entry surfaces), `input`/`select`/`textarea`/`checkbox`/`radio`/`switch` (with help, error and disabled states), `pill-status` for every OS/sale/charge state (success, warning, danger, info, progress, neutral — never `badge`, which is only for counters), `alert`, `toast`, `modal`, `modal-confirm` (destructive confirmation with `button-danger`), `data-table`, `kpi-card`, `tabs`, `pagination`, `card`, `empty-state` and `breadcrumb`.
- Icons are Lucide, from an SVG sprite with only the names in `assets/src/icones.json`: `<?= icon('wrench', ['class' => 'size-4']) ?>` in views (`application/helpers/icone_helper.php`; components take the name in the `icon` prop) and `criarIcone('wrench', 'size-4')` from `assets/js/lib/icone.js` in JS. A new icon goes into `icones.json` followed by `npm run build:vendor`; `IconeTest` fails on names outside the list. Never add Boxicons (`<i class="bx ...">`) to new screens: it is only loaded in legacy mode.
- New listings follow the pattern of `Clientes::gerenciar()` + `views/clientes/clientes.php` (#2852): filters in the query string read with `listagemFiltros()` (allow-list) and counted with the same filters; `component('pagination', $this->paginacao($baseUrl, $total, $offset, null, $filtros))` keeps them in the links; distinct empty states for "nothing yet" and "no match"; row actions as ghost icon buttons gated by `$this->permite('xPerm')` (UI only — routes are protected by `permissions_map.php`); deletion through one shared `modal-confirm` filled by `data-valor-*` on the row button.
- New forms follow `Clientes::formulario()` + `views/clientes/formulario.php` (#2851): plain POST with CSRF; `<form novalidate <?= js_module('formulario/padrao') ?>>` validates with the HTML constraints and shows errors in the field (`assets/js/lib/formulario.js`), applies `data-mascara` and puts the submit button in a loading state; the server always revalidates with `$this->validarFormulario('grupo')` (pt-br messages, errors per field passed to each component's `error`), re-renders with the posted values or redirects with a success flash (toast). The edited id comes from the URL, never from the POST, and the saved data comes from a function that only reads the form fields. Paginate with `component('pagination', $this->paginacao($baseUrl, $total, $offset))` (`MY_Controller::paginacao()`, same URL offset as `CI_Pagination`). The legacy Bootstrap 2 pagination markup lives in `application/config/pagination.php` for the not-yet-migrated screens; do not add markup back to `MY_Controller`.
- Screen parts that change without a reload follow the OS screen (#2842: `Os::visualizar()`, `views/os/partes/`, `assets/js/modules/os/tela.js`): each part is a partial view used on first render inside `data-os-parte="<part>"`; forms with `data-os-acao` are posted through `post()` (lib/http.js); the endpoint answers JSON `{result, message, erros?, html?}` with the re-rendered parts and per-field errors (403 no permission or closed record, 404 item of another record, 422 field errors). The record id comes from the POST but is checked in the database (exists, editable) and every item must belong to it; totals, discounts and stock are always computed server-side.
- Admin screens render through `MY_Controller::layout()` (views in `application/views/tema/`, data from `application/helpers/layout_helper.php`). The menu comes from `layoutMenu()`, split into sidebar sections by each top-level item's `grupo` (Operação, Financeiro, Sistema), and each item is shown only if `permissions_map.php` allows its target route. A screen's primary action (its single `button-primary`) goes in the topbar as button props, with an icon (phones show it icon-only): `$this->data['topbar_acao'] = ['label' => 'Nova OS', 'icon' => 'plus', 'href' => site_url('os/adicionar')]`.
- Entry surfaces (the panel and client-area logins) are always dark, whatever the panel color mode: they include `application/views/entrada/inicio.php` and `fim.php` (dark canvas with the starfield image, top nav with the secondary action as `button-ghost-on-dark`, lime squiggle footer) and put the form card in `.superficie-entrada`, a token scope in `tokens.css` that applies the dark palette to the card while keeping form fields light.
- Sticker illustrations live in `assets/src/stickers/` (readable source) and are optimized by `npm run build:stickers` (svgo, part of `npm run build`) into `assets/img/stickers/`, which is committed. Only on entry surfaces and dark highlights, never inside cards or on operational screens; never orange; used as `<img alt="" aria-hidden="true">` hidden on small phones. `StickerTest` checks size (< 4 KB), palette and usage. `$this->data['legacy_assets']` defaults to `true` (legacy mode: Bootstrap 2, jQuery, matrix-style and `tema-*.css` are loaded and the screen renders inside `#content`); a screen migrated to the components sets it to `false` and gets `assets/dist/app.css` instead. Shell markup goes inside a `.v5-shell` wrapper, whose Tailwind utilities and reset (`assets/dist/layout.css`) are scoped there and `!important` so legacy CSS and the shell never affect each other; never put screen content inside `.v5-shell`.

## Security & Integrity Mandates

- **Secrets:** Never log, print, or commit `.env` files, credentials, API keys, or database dumps.
- **Vulnerabilities:** Security issues must be handled following `SECURITY.md` (report privately to `contato@mapos.com.br`).
