# Map-OS — Design System

> **Status:** source of truth for the v5 UI. Every view, component, and CSS change follows this document; when existing code diverges, this document wins and the fix belongs to epic #2911. Changes to the system itself start here (with recalculated contrast ratios), then reach the code.

## Overview

Map-OS's design language reads like a well-lit workshop bench: dense, organized, and built for people who open dozens of service orders a day, with just enough personality to keep an operational tool from feeling like a spreadsheet. Entry and highlight surfaces — login, the client-area welcome, empty states — sit on a near-black violet midnight (`{colors.surface-canvas-dark}` / `{colors.surface-night}`) with a faint starfield texture and occasional sticker-style illustrations (tools, devices, delivery boxes) that lighten the mood. Headlines run in a chunky display sans where the most important keywords can be wrapped in lime-green highlight chips (`{colors.accent-lime}`), as if the copy itself had been marked with a highlighter.

The palette is deliberately narrow: deep midnight as the dominant dark canvas, a warm signal orange (`{colors.primary}`) as the single action color, electric lime as the keyword highlighter, hot pink (`{colors.accent-pink}`) as illustration-only punctuation, and a violet-mid (`{colors.accent-violet-mid}`) for tag chips and hairline strokes. White appears in two roles — as text on dark (`{colors.on-dark}`), and as the canvas for the operational screens (service orders, sales, finance, reports) where users scan dense tables. The single primary CTA keeps the same orange fill on both canvases: the orange has enough luminance to stand out on midnight and enough saturation to stand out on white, so the button never needs to flip polarity. Its label always runs in dark ink (`{colors.on-primary}`), because white on this orange falls below the WCAG AA threshold. Operational screens default to the light canvas; users can switch the whole panel to a dark mode built from the same midnight palette (see Panel Dark Mode).

Typography splits cleanly between three families: a chunky display sans for hero and section openers (near-condensed, slightly playful), Rubik for every UI text role (body, captions, eyebrow caps, button labels), and Monaco for code. Buttons and eyebrows almost always run in uppercase with a 0.2px tracking lift to give them a crisp, label-maker snap.

**Key Characteristics:**
- Two-polarity canvas system: deep violet midnight (`{colors.surface-canvas-dark}`) for entry and highlight surfaces (login, client-area welcome, empty states), white (`{colors.surface-canvas-light}`) for the operational screens and dense reference content — the system never tries to blur the two. The optional dark panel mode switches the whole screen at once, never a single band.
- Lime keyword highlight (`{colors.accent-lime}`) treated as a typographic device, not a color swatch — it wraps single words inside the display headline like a highlighter stroke on the reading flow.
- Sticker illustration system: floating illustrations from the repair-shop world (tools, devices, packages) with hand-drawn outlines, appearing at section junctions on entry surfaces, never inside cards or operational screens — they create rhythm and personality between dense info blocks.
- Uppercase eyebrow + button caps in `{typography.button-cap}` and `{typography.eyebrow}`, with a consistent 0.2px tracking lift, give the brand its label-maker cadence.
- Single-primary CTA hierarchy: every view has one filled `{colors.primary}` orange button with a `{colors.on-primary}` ink label, on either canvas; inverted, outlined, and ghost variants are downgraded.
- Card surfaces follow the canvas: dark sections nest dark cards (`{colors.ink-deep}` with subtle hairline) and light sections nest white cards with `{colors.hairline-cloud}` borders — chrome stays consistent, only the polarity flips.
- A tier-card color rhythm (plans, comparisons) of cream-white cards with one dark inverted "featured" card (`{colors.surface-night}`), avoiding the typical accent-bordered featured pattern.
- Status never borrows the action color: service-order states use their own semantic palette (success, warning, danger, info, progress, neutral), always as a labeled pill, so orange keeps meaning "act here".

## Colors

> **Applies to:** login and client-area entry surfaces (dark canvas); the admin panel and operational screens — service orders, sales, finance, reports (light canvas).

### Brand & Accent
- **Signal Orange** (`{colors.primary}` — `#F37338`): The system's single action color. Fills primary buttons on both canvases, marks the active navigation item, and draws progress and selection states. Its luminance sits mid-scale — 2.87:1 against white, 6.00:1 against `{colors.ink-deep}` — so it always carries a dark ink label (`{colors.on-primary}`), never white.
- **Orange Pressed** (`{colors.primary-pressed}` — `#DB6230`): The pressed/active fill of `button-primary`. Same hue, lower value; keeps the ink label at 4.75:1.
- **Orange Strong** (`{colors.primary-strong}` — `#B8501F`): The same hue darkened for the two jobs the base orange can't do: orange text or links on light canvas, and the rare orange surface that must carry white text (4.99:1 against white).
- **Orange Tint** (`{colors.primary-tint}` — `#feeee7`): `{colors.primary}` at 12% over white. The background of the active item in the sidebar, tab bar, and autocomplete lists — the second cue that always accompanies a thin orange indicator. Ink on it reads at 15.2:1.
- **Ink Violet** (`{colors.ink-deep}` — `#1f1633`): Slightly lifted from `{colors.surface-night}`, this is the dark entry-surface canvas and the default body-text color on light surfaces — a single token doing double duty as background and ink.
- **Electric Lime** (`{colors.accent-lime}` — `#c2ef4e`): The signature highlight color. Wrapped around individual headline keywords as a syntax-highlight chip (`{rounded.xs}` corner, no padding-y, 12px padding-x). Also used as the squiggly footer divider stroke. Never a button background.
- **Hot Pink** (`{colors.accent-pink}` — `#fa7faa`): Secondary punctuation color used for sticker outlines, chart points, and supporting accents — never on buttons, never on type at body size.
- **Violet Link** (`{colors.accent-violet}` — `#6a5fc1`): Inline link color when emphasis is needed beyond underline.
- **Deep Violet** (`{colors.accent-violet-deep}` — `#422082`): The select-dropdown fill on contact forms; also used on spotlight cards inside dark sections.
- **Mid Violet** (`{colors.accent-violet-mid}` — `#79628c`): Tag-chip fill and faint accent on dark surfaces.

### Surface
- **Dark Canvas** (`{colors.surface-canvas-dark}` — `#1f1633`): Login, client-area welcome, and highlight-band background. Carries the deepest atmospheric weight.
- **Night** (`{colors.surface-night}` — `#150f23`): The deepest surface tone — cards on dark canvas, code blocks, and the "featured" tier card.
- **Light Canvas** (`{colors.surface-canvas-light}` — `#ffffff`): Admin panel, operational screens, and dense-reference page background.
- **Surface Press Light** (`{colors.surface-press-light}` — `#f0f0f0`): The pressed/active fill of inverted buttons on dark surfaces, and the empty track of progress bars.
- **Surface Subtle** (`{colors.surface-subtle}` — `#f6f5f9`): Table headers, row hover, disabled fields, and icon-button hover on light canvas.
- **Hairline Violet** (`{colors.hairline-violet}` — `#362d59`): 1px borders on dark cards.
- **Hairline Input** (`{colors.hairline-input}` — `#8a849c`): 1px borders on form controls — text inputs, selects, unchecked checkboxes and radios, the off-state switch track. At 3.58:1 against white it meets the WCAG 3:1 minimum for the boundary that identifies a control.
- **Hairline Cool** (`{colors.hairline-cool}` — `#cfcfdb`): 1px borders that don't identify a control on their own — outline buttons (their label already does) and dividers inside forms. Never the only boundary of an input (1.54:1).
- **Hairline Cloud** (`{colors.hairline-cloud}` — `#e5e7eb`): Card borders, table dividers, and tier-card borders on light canvas.

### Text
- **On Primary** (`{colors.on-primary}` — `#1f1633`): Labels on orange fills — `button-primary` and `button-primary-pressed`. Same hex as `{colors.ink}`; white on `{colors.primary}` fails WCAG AA, so orange always carries ink.
- **On Dark** (`{colors.on-dark}` — `#ffffff`): All text on dark canvas, labels on dark fills, and the fill of `button-inverted`.
- **Ink** (`{colors.ink}` — `#1f1633`): Body text on light canvas; identical hex to the dark canvas, repurposed as type.
- **Ink Muted** (`{colors.ink-muted}` — `#5b5470`): Secondary text on light canvas — captions, helper copy, table headers, inactive navigation items, equipment lines under a client name. 7.13:1 on white, 6.57:1 on `{colors.surface-subtle}`.
- **Text Disabled** (`{colors.text-disabled}` — `#8a849c`): Labels of disabled buttons and controls on light canvas. Disabled controls are exempt from WCAG contrast, but they must stay legible — never white on a light fill.
- **Ink Press** (`{colors.ink-press}` — `#1a1a1a`): Reserved for the pressed/active state of inverted buttons.
- **On Dark Muted** (`{colors.on-dark-muted}` — `rgba(255,255,255,0.72)`): Secondary text, captions, and table cell values on dark canvas.
- **On Dark Faint** (`{colors.on-dark-faint}` — `rgba(255,255,255,0.18)`): Translucent surface-on-dark — used for ghost button fills and dimmed nav items.

### Semantic
- **Focus Ring** (`{colors.ring-focus}` — `rgba(59,130,246,0.5)`): Translucent blue focus ring, 3px — the only blue in the system outside the info status, reserved for keyboard focus on fields and buttons.

**Status colors.** Each state has a base (icons, dots, fills such as `button-danger`), an `-ink` for text, and a `-soft` background (base at 12% over white). Pills and alerts always put `-ink` on `-soft`.

| State      | Base token                         | Ink token                          | Soft token                         | Ink on soft | Used for                                   |
| ---------- | ---------------------------------- | ---------------------------------- | ---------------------------------- | ----------- | ------------------------------------------ |
| Success    | `{colors.success}` `#15803d`       | `{colors.success-ink}` `#166534`   | `{colors.success-soft}` `#e3f0e8`  | 6.08:1      | Finalizada, pagamento confirmado           |
| Warning    | `{colors.warning}` `#a16207`       | `{colors.warning-ink}` `#854d0e`   | `{colors.warning-soft}` `#f4ece1`  | 5.85:1      | Aguardando peça/aprovação, garantia vencendo |
| Danger     | `{colors.danger}` `#dc2626`        | `{colors.danger-ink}` `#b91c1c`    | `{colors.danger-soft}` `#fbe5e5`   | 5.37:1      | Cancelada, erros, ações destrutivas        |
| Info       | `{colors.info}` `#0369a1`          | `{colors.info-ink}` `#075985`      | `{colors.info-soft}` `#e1edf4`     | 6.35:1      | Aberta, avisos do sistema                  |
| Progress   | `{colors.accent-violet}` `#6a5fc1` | `{colors.progress-ink}` `#4a3f9e`  | `{colors.progress-soft}` `#edecf8` | 7.19:1      | Em andamento                               |
| Neutral    | `{colors.accent-violet-mid}` `#79628c` | `{colors.neutral-ink}` `#4b4560` | `{colors.neutral-soft}` `#efecf1` | 7.73:1      | Orçamento enviado, rascunho, contadores    |

Warning is a yellow amber (hue 35°) on purpose: the more common `#b45309` sits at 26°, too close to `{colors.primary}` (19°) to be told apart. Status is never shown by color alone — every pill carries its label.

### Panel Dark Mode
The operational screens default to the light canvas. Users can switch the whole panel to dark (Claro / Escuro / Sistema); the switch always applies to the entire screen, so the two-canvas rule still holds.

| Role                     | Light                              | Dark                                                     |
| ------------------------ | ---------------------------------- | -------------------------------------------------------- |
| App background           | `{colors.surface-canvas-light}`    | `{colors.surface-canvas-dark}` `#1f1633`                 |
| Sidebar, topbar, cards   | `{colors.surface-canvas-light}`    | `{colors.surface-night}` `#150f23`, no shadow            |
| Table header, row hover  | `{colors.surface-subtle}`          | `{colors.surface-subtle-dark}` `#1b1430`                 |
| Borders                  | `{colors.hairline-cloud}`          | `{colors.hairline-violet}` `#362d59`                     |
| Input fill / border      | white / `{colors.hairline-input}`  | `{colors.surface-canvas-dark}` / `{colors.hairline-input-dark}` `#756d8a` (3.83:1 on cards, 3.53:1 on the app background) |
| Text / secondary text    | `{colors.ink}` / `{colors.ink-muted}` | `{colors.on-dark}` / `{colors.on-dark-muted}`         |
| Active item background   | `{colors.primary-tint}`            | `{colors.primary-tint-dark}` `#391f26` (orange 16% over night) |
| Disabled button          | `{colors.hairline-cloud}` + `{colors.text-disabled}` | `rgba(255,255,255,0.08)` + `rgba(255,255,255,0.45)` |
| Primary button           | unchanged                          | unchanged — `{colors.primary}` is 6.51:1 against night, label stays `{colors.on-primary}` |

Status in dark mode uses lighter bases that double as text, on a soft fill mixed over `{colors.surface-night}` (16%; 26% for progress and neutral):

| State    | Dark token                               | On card | Dark soft  | On soft |
| -------- | ---------------------------------------- | ------- | ---------- | ------- |
| Success  | `{colors.success-dark}` `#4ade80`        | 10.72:1 | `#1d3032`  | 7.92:1  |
| Warning  | `{colors.warning-dark}` `#facc15`        | 12.19:1 | `#3a2d21`  | 8.69:1  |
| Danger   | `{colors.danger-dark}` `#f87171`         | 6.75:1  | `#391f2f`  | 5.38:1  |
| Info     | `{colors.info-dark}` `#38bdf8`           | 8.72:1  | `#1b2b45`  | 6.63:1  |
| Progress | `{colors.progress-dark}` `#b8b0f0`       | 9.34:1  | `#2b244c`  | 7.20:1  |
| Neutral  | `{colors.neutral-dark}` `#d6cfe0`        | 12.33:1 | `#2f253e`  | 9.53:1  |

`button-danger` keeps `{colors.danger}` with white text in both modes.

## Typography

### Font Family

The display tier is **Space Grotesk** at heavy weights (600–700) — chunky, near-condensed proportions with a slightly playful personality. When unavailable, fall back to **Rubik** at heavier weights for visual continuity.

The UI tier is **Rubik** — an open-source Hebrew/Latin sans on Google Fonts — with system fallbacks (`-apple-system, system-ui, Segoe UI, Helvetica, Arial`). Rubik handles every body, caption, button, and eyebrow role.

The code tier is **Monaco** with Menlo and Ubuntu Mono fallbacks — used in code blocks, install snippets, and inline tokens.

### Hierarchy

| Token                           | Size | Weight | Line Height | Letter Spacing | Use                                                                                |
| ------------------------------- | ---- | ------ | ----------- | -------------- | ---------------------------------------------------------------------------------- |
| `{typography.display-hero}`     | 88px | 700    | 1.2         | 0              | Entry-surface hero headline (login, client-area welcome)                           |
| `{typography.display-large}`    | 60px | 500    | 1.1         | 0              | Section openers on dark surfaces                                                   |
| `{typography.heading-xl}`       | 30px | 500    | 1.2         | 0              | Page titles on light surfaces (e.g., "Ordens de Serviço")                          |
| `{typography.heading-lg}`       | 27px | 500    | 1.25        | 0              | Sub-section headings, large card titles                                            |
| `{typography.heading-md}`       | 24px | 500    | 1.25        | 0              | Card titles, in-page section headings                                              |
| `{typography.heading-sm}`       | 20px | 600    | 1.25        | 0              | Compact card title, list-group title                                               |
| `{typography.body-lg}`          | 16px | 400    | 2.0         | 0              | Entry-surface paragraph — the airy, two-line-leading variant used in hero subtext  |
| `{typography.body-strong}`      | 16px | 600    | 1.5         | 0              | Emphasized body run, lead sentence                                                 |
| `{typography.body-md}`          | 16px | 400    | 1.5         | 0              | Default UI body, table cells, text typed into fields                               |
| `{typography.label-md}`         | 16px | 500    | 1.5         | 0              | Form labels, the primary name in a table row (client), list-item titles            |
| `{typography.eyebrow}`          | 15px | 500    | 1.4         | 0              | Section eyebrow above large headings, all-caps                                     |
| `{typography.button-cap}`       | 14px | 700    | 1.14        | 0.2px          | Filled button labels (uppercase)                                                   |
| `{typography.button-cap-light}` | 14px | 500    | 1.29        | 0.2px          | Ghost / outline button labels (uppercase)                                          |
| `{typography.caption}`          | 14px | 400    | 1.43        | 0              | Footer text, fine print, helper copy                                               |
| `{typography.micro-cap}`        | 10px | 600    | 1.8         | 0.25px         | Status labels, badge text, micro-eyebrow                                           |
| `{typography.code}`             | 16px | 400    | 1.5         | 0              | Code block content                                                                 |
| `{typography.code-strong}`      | 16px | 700    | 1.5         | 0              | Highlighted code keyword                                                           |

### Principles
- **Two leading worlds.** Entry-surface copy uses 2.0 line-height on `{typography.body-lg}` — extremely airy, generous breathing room. Operational UI copy uses 1.5 line-height on `{typography.body-md}` — denser, closer to a work-order form. The choice is deliberate: entry surfaces read like prose, operational screens read like a checklist.
- **Weight marks structure, not data.** Data — table cells, values, dates, typed input text — runs at 400 in `{typography.body-md}`. Weight 500 (`{typography.label-md}`) is for what names things: labels, the primary name in a row, navigation items. Weight 600 marks the active or selected state. Setting dense tables in 500 makes the whole screen read as bold.
- **Caps with tracking.** All button labels and eyebrows are uppercase with 0.2px tracking. This is the brand's typographic signature — a label-maker cadence applied to UI affordances.
- **Headlines as highlights.** The hero display is structured so a single keyword can be wrapped in a `{colors.accent-lime}` highlight chip without disrupting the reading order. Treat the lime chip as a glyph-level decoration, not a separate component.

### Note on Font Substitutes
Rubik and Space Grotesk are both open-source on Google Fonts. If Space Grotesk doesn't fit a context, **Archivo** (semi-condensed weights) or **Hubot Sans** (optical-size axis at heavier ends) carry the same chunky, near-condensed silhouette. Adjust line-height down by 0.05 when substituting at large display sizes.

## Layout

### Spacing System
- **Base unit**: 8px
- **Tokens**: `{spacing.xxs}` 2px · `{spacing.xs}` 4px · `{spacing.sm}` 8px · `{spacing.md}` 12px · `{spacing.lg}` 16px · `{spacing.xl}` 24px · `{spacing.xxl}` 32px · `{spacing.section}` 96px
- **Section padding**: `{spacing.section}` 96px between major page bands on desktop, collapsing to `{spacing.xxl}` 32px–48px on mobile.
- **Card internal padding**: `{spacing.xxl}` 32px on pricing cards and large feature cards; `{spacing.lg}` 16px on compact tag/badge groups.
- **Form field padding**: `{spacing.sm}` 8px vertical, `{spacing.md}` 12px horizontal — matches the text-input token directly.

### Grid & Container
- Pages use a wide centered container with generous outer gutters; max width sits around 1152px (one of the extracted breakpoints), with content inside flexing across 12 conceptual columns.
- Pricing splits into a 4-tier card row at desktop, collapsing to 2-up at mid widths and 1-up on mobile.
- The contact form uses a 2-column field layout (first/last name side-by-side) inside a single light-canvas panel.
- Breakpoints stair-step at 1440 → 1152 → 992 → 768 → 640 → 576 — see Responsive Behavior.

### Whitespace Philosophy
The dark canvas absorbs whitespace differently from light. On dark surfaces the brand stretches `{spacing.section}` generously between bands so floating illustrations and starfield textures have room to breathe. On light surfaces (the operational screens) the whitespace tightens — content density takes priority because users are scanning, comparing, and acting. Rule of thumb: hero and feature surfaces are spacious, transactional surfaces are dense.

## Elevation & Depth

| Level | Treatment                                                                      | Use                                                                                                                                   |
| ----- | ------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------- |
| 0     | Flat on canvas, no shadow                                                      | Default surface, dark or light                                                                                                        |
| 1     | `box-shadow: rgba(0,0,0,0.08) 0 2px 8px 0`                                     | Inverted buttons on dark canvas (light fill lifting off dark surface)                                                                 |
| 2     | `box-shadow: rgba(0,0,0,0.1) 0 10px 15px -3px, rgba(0,0,0,0.1) 0 4px 6px -4px` | Floating cards on light canvas, modals                                                                                                |
| 3     | `box-shadow: rgb(21,15,35) 0 0 8px 6px`                                        | Halo around the orange CTA on dark entry surfaces — `{colors.surface-night}` itself becomes the shadow, a vignette that makes the orange read brighter |
| 4     | `box-shadow: rgba(0,0,0,0.18) 0 0.5rem 1.5rem`                                 | Pressed inverted button on dark canvas                                                                                                |

### Decorative Depth
On dark entry surfaces, depth doesn't come from drop shadows — it comes from the **starfield texture** on the hero canvas (subtle white-on-violet pinpricks at low opacity), the **floating sticker illustrations** (drawn with hand-rendered outlines and saturated fills, layered above the canvas with no shadow), and the **lime squiggly divider** above the footer. These illustrative elements do the work that shadow stacks do in flatter design systems — they tell the eye where one section ends and another begins. Operational screens on light canvas use the elevation levels above instead.

## Shapes

### Border Radius Scale

| Token            | Value  | Use                                                         |
| ---------------- | ------ | ----------------------------------------------------------- |
| `{rounded.xs}`   | 4px    | Badges, checkboxes, lime keyword highlight chips            |
| `{rounded.sm}`   | 6px    | Text inputs, search boxes                                   |
| `{rounded.md}`   | 8px    | Primary and inverted buttons, code blocks, select dropdowns |
| `{rounded.lg}`   | 10px   | Generic divs, container blocks                              |
| `{rounded.xl}`   | 12px   | Pricing cards, feature cards, navigation pill chrome        |
| `{rounded.xxl}`  | 18px   | Image containers, large hero illustrations                  |
| `{rounded.full}` | 9999px | Avatars, circular icon buttons, status pills, counters      |

### Photography Geometry
Map-OS doesn't use traditional photography — it uses **illustrated stickers and product UI screenshots** in roughly equivalent geometric roles. Product UI mocks sit inside `{rounded.xxl}` 18px containers, often tilted slightly off-axis, against the dark canvas with no border. Sticker illustrations have no container at all — they are layered directly on canvas, often overlapping section boundaries to break the grid. User avatars without a photo show initials on a `{rounded.full}` circle.

## Components

> **States.** Specs list Default and Pressed/Active, plus Focused, Error, and Disabled where the component has them; each variant is its own entry (`-pressed`, `-focused`, `-error`, `-disabled`). Hover is a light shift only: `button-primary` mixes 8% white into `{colors.primary}`, and neutral controls (outline buttons, icon buttons, menu items, table rows) hover to `{colors.surface-subtle}`.

### Buttons

**`button-primary`** — the dominant CTA on both canvases.
- Background `{colors.primary}`, text `{colors.on-primary}` (6.00:1), type `{typography.button-cap}` (uppercase, 14px / 700, 0.2px tracking), padding `{spacing.md} {spacing.lg}` (12px 16px), rounded `{rounded.md}`. On dark entry surfaces, add the level-3 halo for emphasis.
- Pressed state lives in `button-primary-pressed`: background darkens to `{colors.primary-pressed}`, text stays `{colors.on-primary}` (4.75:1). The hue never changes on press — only the value drops.

**`button-inverted`** — secondary CTA on dark canvas, placed beside `button-primary` when a view needs a second strong action.
- Background `{colors.on-dark}` (white), text `{colors.ink-deep}`, same `{typography.button-cap}`, rounded `{rounded.md}`.
- Pressed in `button-inverted-pressed`: background drops to `{colors.surface-press-light}`, text to `{colors.ink-press}`.

**`button-ghost-on-dark`** — low-emphasis action on dark canvas (e.g., "Área do cliente" beside "Entrar" on the login screen).
- Translucent fill `{colors.on-dark-faint}`, text `{colors.on-dark}`, type `{typography.button-cap}`, padding `{spacing.sm}` (8px), rounded `{rounded.xl}`. The translucent fill lets the canvas texture show through.

**`button-violet-token`** — pill-shaped tag/category button used inline in filters and section navs.
- Background `{colors.accent-violet-mid}`, text `{colors.on-dark}`, type `{typography.button-cap-light}`, padding `{spacing.sm} {spacing.lg}` (8px 16px), rounded `{rounded.xl}`, 1px hairline border in a slightly deeper violet.

**`button-outline`** — secondary action on light canvas (e.g., "Cancelar", "Filtrar").
- Transparent fill, text `{colors.ink-deep}`, 1px `{colors.hairline-cool}` border, type `{typography.button-cap-light}`, padding `{spacing.md} {spacing.lg}`, rounded `{rounded.md}`. Hover fills `{colors.surface-subtle}`. In dark panel mode the border becomes `{colors.hairline-input-dark}` and the text `{colors.on-dark}`.

**`button-danger`** — destructive actions only (excluir, cancelar OS), usually inside a confirmation modal.
- Background `{colors.danger}`, text white (4.83:1), type `{typography.button-cap}`, same geometry as `button-primary`. Pressed darkens to `{colors.danger-ink}`. Never orange: destructive and primary actions must not look alike.

**`button-disabled`**
- Background `{colors.hairline-cloud}`, text `{colors.text-disabled}`, otherwise identical to `button-primary`. In dark panel mode: `rgba(255,255,255,0.08)` fill with `rgba(255,255,255,0.45)` text. Prefer explaining why an action is unavailable (helper text or tooltip) over a silent disabled button.

### Cards & Containers

**`card-pricing`** — the standard tier card (plans, service packages).
- Background `{colors.surface-canvas-light}`, text `{colors.ink-deep}`, padding `{spacing.xxl}` 32px, rounded `{rounded.xl}` 12px, 1px `{colors.hairline-cloud}` border. Headline at top in `{typography.heading-md}`, price in `{typography.display-large}`, feature list in `{typography.body-md}`, primary CTA pinned to the bottom of the card.

**`card-pricing-featured`** — the dark inverted "featured" tier, reserved for the single option the view recommends.
- Background `{colors.surface-night}`, text `{colors.on-dark}`, otherwise identical structure to `card-pricing`. The inversion (rather than an accent-bordered light card) is the brand's distinctive choice — the featured tier reads as the brand's voice, not as decoration.

**`card-feature-dark`** — large feature-band card on dark surfaces, used to anchor feature explanations.
- Background `{colors.ink-deep}`, text `{colors.on-dark}`, padding `{spacing.xxl}` 32px, rounded `{rounded.xxl}` 18px. Often holds a UI mock plus a 27px headline plus 16px body.

**`card-spotlight-violet`** — accent feature card with deeper violet fill, used for highlight bands that call out a key capability.
- Background `{colors.accent-violet-deep}`, text `{colors.on-dark}`, padding `{spacing.xxl}`, rounded `{rounded.xxl}`. The deep violet reads as a feature highlight without breaking out of the brand's purple family — and leaves orange free to mean "act here".

**`code-block`** — code/install snippets.
- Background `{colors.surface-night}`, text `{colors.on-dark}` rendered in `{typography.code}`. Padding `{spacing.lg}` 16px, rounded `{rounded.md}`. On dark canvas the code block is barely lifted from canvas — only the slightly deeper fill differentiates it.

### Inputs & Forms

**`text-input`** — every text, number, date, and search field.
- Background `{colors.surface-canvas-light}`, text `{colors.ink-deep}`, type `{typography.body-md}` (400), padding `{spacing.sm} {spacing.md}` (8px 12px), rounded `{rounded.sm}` 6px, 1px `{colors.hairline-input}` border. Placeholder in `{colors.ink-muted}`. Label above in `{typography.label-md}`; helper text below in `{typography.caption}` `{colors.ink-muted}`.
- Focus state in `text-input-focused`: same fill, inset shadow `rgba(0,0,0,0.15) 0 2px 10px inset` to suggest depth pressed inward, plus a 3px `{colors.ring-focus}` ring.
- Error state in `text-input-error`: border `{colors.danger}`; below the field, an alert-circle icon and the message in `{typography.caption}` `{colors.danger-ink}`, linked with `aria-describedby`. The message says what to fix ("IMEI já cadastrado na OS #0981"), not just that something is wrong.
- Disabled state in `text-input-disabled`: fill `{colors.surface-subtle}`, text `{colors.ink-muted}`, not-allowed cursor; a helper line explains where the value comes from ("Calculado pelos produtos e serviços").

**`select`** — native or enhanced (autocomplete) dropdown on light canvas.
- Same box as `text-input`, with a 16px chevron in `{colors.ink-muted}` 12px from the right edge. The open list is a level-2 card; the highlighted option uses `{colors.primary-tint}`, and the typed match is set in weight 600 (lime stays reserved for headline chips). A footer row offers the create action ("Cadastrar novo cliente").

**`checkbox` / `radio` / `switch`**
- 20px box (`{rounded.xs}` for checkbox, circle for radio) with a 1.5px `{colors.hairline-input}` border. Checked: fill `{colors.primary}` with an `{colors.on-primary}` check mark or dot — never a white mark (2.87:1). Switch: 40×24px track, `{colors.hairline-input}` when off, `{colors.primary}` when on, knob white off and `{colors.on-primary}` on. The label sits to the right in `{typography.label-md}`.

**`select-violet`** — the dropdown variant used inside dark contact panels.
- Background `{colors.accent-violet-deep}`, text `{colors.on-dark}`, type `{typography.body-md}`, padding `{spacing.sm} {spacing.lg}`, rounded `{rounded.md}`. Distinctive because it doesn't mimic a plain text input — it reads as a deliberate brand surface. Reserved for entry surfaces; selects inside the dark panel mode use `select` with the dark-mode input tokens.

### Navigation

> **Second-cue rule.** A thin orange indicator (a bar or underline of 3px or less) reads at only 2.87:1 on white, so it never marks a state alone. The active item always pairs it with weight 600 text in ink and, where the item has a surface, a `{colors.primary-tint}` background.

**`sidebar`** — the primary navigation of the admin panel (the v5 layout).
- 252px wide, background `{colors.surface-canvas-light}`, 1px `{colors.hairline-cloud}` right border. Header (64px) holds the logo — a 34px `{colors.primary}` rounded square with an `{colors.on-primary}` wrench glyph, the "Map-OS" wordmark in the display face, a version badge — and the collapse button.
- Items are grouped (Operação / Financeiro / Sistema) under 11px / 600 uppercase group labels in `{colors.ink-muted}`. Each item is 40px tall, rounded `{rounded.md}`, a 20px stroke icon (2px) plus the label at 15px / 500 in `{colors.ink-muted}`; hover goes to `{colors.surface-subtle}` and ink.
- Active item: 3px `{colors.primary}` bar on the left edge, `{colors.primary-tint}` background, label and icon in ink at weight 600 — never orange text.
- Counters (e.g., open OS) use a neutral pill (`{colors.neutral-soft}` / `{colors.neutral-ink}`), not orange. The footer holds the user card (initials avatar in `{colors.accent-violet-mid}`, name, role).
- Collapsed variant `sidebar-collapsed`: a 76px icon rail; labels move into tooltips and `aria-label`s, the active bar stays.

**`topbar`** — the 64px bar above the content.
- Background `{colors.surface-canvas-light}`, 1px `{colors.hairline-cloud}` bottom border. Left: the global search as a `text-input` with a search icon and a `Ctrl K` hint. Right: theme toggle, notifications (unread dot in `{colors.danger}`), initials avatar, and — when the view has a creation action such as "Nova OS" — the screen's single `button-primary`.

**Top Nav (dark variant)** — used on dark entry surfaces and on the client area at desktop widths; logo on the left, links in `{colors.on-dark-muted}`, sitting on `{colors.surface-canvas-dark}`. The primary CTA stays `button-primary` orange; secondary actions become `button-inverted` or `button-ghost-on-dark`.

**`tabs`** — section tabs inside a page (e.g., Detalhes / Produtos / Serviços / Anexos on an OS).
- Labels at 15px / 500 in `{colors.ink-muted}` with optional neutral counters; a 1px `{colors.hairline-cloud}` baseline. Active tab: label in ink at weight 600 plus a 2px `{colors.primary}` underline.

**`tabbar-mobile`** — bottom navigation of the client area on phones.
- Four items (Início, Minhas OS, Compras, Conta), white background, 1px `{colors.hairline-cloud}` top border, 24px icon over a 12px label in `{colors.ink-muted}`. Active item: label and icon in ink at weight 600 with a 28×3px `{colors.primary}` indicator at the top edge.

**Mobile nav** — below 1024px (the `lg` breakpoint of the v5 layout) the sidebar becomes an off-canvas drawer opened by a menu button in the topbar; the topbar keeps search, notifications, and the primary action, which shrinks to its icon on small phones.

### Data Display

**`data-table`** — the list screens (OS, clientes, vendas, lançamentos).
- Sits inside a white card (`{rounded.xl}`, 1px `{colors.hairline-cloud}`). Header row on `{colors.surface-subtle}`: 12px / 600 uppercase, 0.35px tracking, `{colors.ink-muted}`. Cells in `{typography.body-md}` (400) with 14px / 16px padding and `{colors.hairline-cloud}` row dividers; the primary name in `{typography.label-md}` with a secondary line (equipment, document) in `{typography.caption}` `{colors.ink-muted}`.
- Numbers and currency are right-aligned with tabular figures; status, technician, and date columns don't wrap. Row actions are 36px icon buttons with `aria-label`s. Row hover uses `{colors.surface-subtle}`.
- Footer: record count on the left, pagination on the right — 36px items, the current page filled `{colors.primary}` with `{colors.on-primary}` at weight 700.

**`kpi-card`** — summary cards above lists.
- White card, 18px 20px padding, label at 14px / 500 `{colors.ink-muted}` with a 34px icon tile on `{colors.surface-subtle}`, value in the display face at 34px / 500, and a caption line that may carry a status-colored phrase (e.g., warning ink for "2 há mais de 5 dias").

### Feedback

**`pill-status`** — the state of an OS, sale, or charge.
- Fully rounded, 2px 10px padding, 13px / 600, a 6px dot in the same color before the label; text `-ink` on `-soft` from the status table (dark-mode tokens in Panel Dark Mode). Always shows the word, never just the color.

**`alert-success` / `alert-warning` / `alert-danger` / `alert-info`** — inline messages.
- `{rounded.lg}`, 14px 16px padding, 20px icon, title at weight 600 and body at 15px / 400, text in the state's `-ink` on its `-soft`, 1px border in the same ink at 28% opacity.

**`toast`** — confirmation after an action ("OS #1042 salva").
- A level-2 card at the top right of the content, 340px max, success icon, title at 600 and a `{colors.ink-muted}` line, close button. It disappears on its own; errors use `alert-danger` inline instead of a toast.

**`modal-confirm`** — destructive confirmation.
- Level-2 card up to 340px on a `rgba(21,15,35,0.55)` backdrop, a danger-soft icon circle, title at 18px / 600, consequences spelled out in the body, and `button-outline` "Cancelar" beside `button-danger`.

### Pills, Badges, and Highlight Chips

**`pill-neutral-dark`** — small category / version pill on dark entry surfaces (for states, use `pill-status`).
- Background `{colors.surface-night}`, text `{colors.on-dark}`, type `{typography.caption}` 12px, padding `{spacing.xs} {spacing.sm}` (4px 8px), rounded `{rounded.xs}` 4px.

**`chip-lime-keyword`** — the signature inline highlight wrapping single words inside the hero display headline.
- Background `{colors.accent-lime}`, text `{colors.ink-deep}`, type matches the surrounding `{typography.display-hero}`, rounded `{rounded.xs}` 4px, padding `0 {spacing.md}` (12px horizontal, 0 vertical so the chip hugs the cap-height).

### Signature Components

**Sticker Illustration Layer** — illustrated stickers from the repair-shop world (tools, phones and laptops, delivery boxes, a friendly technician) drawn with hand-rendered outlines and saturated `{colors.accent-pink}` / `{colors.accent-lime}` fills — never `{colors.primary}`, which stays reserved for actions. Stickers are placed at section junctions on entry surfaces, often overlapping section boundaries by 30–40% of their height, with no container or shadow. They function as decorative section markers and brand personality carriers — never inside cards, never as buttons, never on operational screens.

**Lime Squiggly Footer Divider** — a hand-drawn `{colors.accent-lime}` squiggle line, ~3px stroke, sitting above the footer at full container width. Replaces what would otherwise be a 1px hairline divider with a personality-laden flourish.

**Starfield Hero Texture** — a faint white-on-violet pinprick pattern overlaid on the hero canvas at very low opacity. Adds atmospheric depth to the dark canvas without visible decoration. Implemented as a background image, not as repeating CSS.

**Window-Chrome UI Mock** — product UI screenshots framed in `{rounded.xxl}` containers, often tilted ±2–3 degrees off axis, positioned overlapping section boundaries on the dark feature pages. The chrome itself is just a rounded image with a subtle hairline; the content is the actual product UI.

**`link-on-dark`** — inline links in body copy on dark surfaces. Default text is `{colors.on-dark}` rendered in `{typography.body-md}` with a persistent underline; the underline is the entire affordance, no color change. Sits flush in copy with no padding, no rounded corners beyond the inherited `{rounded.xs}`.

**`link-on-light`** — inline links in body copy on light surfaces. Same shape contract as `link-on-dark`, but text is `{colors.ink-deep}`. When a link needs color emphasis, use `{colors.primary-strong}` — never the base `{colors.primary}`, which fails contrast as text on white. Used across the operational screens.

**`footer-light`** — site-wide footer on the light-canvas template (admin panel and operational screens).
- Background `{colors.surface-canvas-light}`, text `{colors.ink-deep}`, type `{typography.caption}`, padding `{spacing.xxl} {spacing.xl}` (32px 24px). Topped by the lime squiggly divider — see Signature Components. Holds three to four columns of link groups, social icons in a horizontal strip at the bottom right, and a small legal/copyright row at the very bottom in `{typography.caption}`.

## Do's and Don'ts

### Do
- Reserve `{colors.accent-lime}` for keyword-highlight chips inside display headlines and the footer squiggle divider — never use it as a button background, never as body text.
- Keep `{colors.primary}` for actions and active states only — one filled orange button per view, so the eye always finds the next step.
- Pair every thin orange indicator (sidebar bar, tab underline, tab-bar mark) with weight 600 text and, where there is a surface, `{colors.primary-tint}` — the state must survive without color.
- Show every status as a `pill-status` with its word, in the semantic palette; keep the table cells around it at weight 400.
- Pair every `button-primary` with `{typography.button-cap}` in uppercase with 0.2px tracking — the cadence is part of the brand, not a stylistic option.
- Treat the dark canvas (`{colors.surface-canvas-dark}`) and light canvas (`{colors.surface-canvas-light}`) as two complete worlds — let one own entry and highlight surfaces and the other own the operational screens, with no half-measures.
- Use sticker illustrations to break section boundaries on entry surfaces — let them overlap, tilt, and float; constraining them inside cards drains their personality.
- Use `card-pricing-featured` (dark inverted tier) instead of an accent-bordered light tier for the featured column.
- Default body line-height to 1.5 on operational screens and 2.0 on entry surfaces — the difference is intentional.
- Offer the dark panel mode as a whole-screen preference (Claro / Escuro / Sistema), using the Panel Dark Mode tokens — never a dark band inside a light screen.

### Don't
- Don't introduce accents beyond `{colors.primary}`, `{colors.accent-lime}`, and `{colors.accent-pink}` — and keep their jobs apart: orange acts, lime highlights words, pink decorates illustrations. Adding teal, yellow, or a second orange shade dilutes the hierarchy.
- Don't put white text on `{colors.primary}` — it measures 2.87:1, below WCAG AA. Labels on orange always use `{colors.on-primary}`; when orange has to sit behind white text or work as text on light canvas, switch to `{colors.primary-strong}`.
- Don't use `{colors.primary}` for warning or error states — orange already means "act here"; status messages use the semantic status colors.
- Don't use `{colors.danger}` for anything but errors and destructive actions, and don't make a destructive button orange.
- Don't use `{colors.hairline-cool}` as the only border of a form control — it measures 1.54:1; inputs use `{colors.hairline-input}`.
- Don't set table cells, values, or typed input text at weight 500 — data runs at 400; 500 is for labels and names.
- Don't apply drop shadows to cards on dark canvas — depth comes from texture and illustration, not from light-on-dark shadows that would muddy the violet.
- Don't use `{typography.display-hero}` (88px) for anything except the entry-surface hero — even sub-pages cap at `{typography.display-large}` (60px).
- Don't put body text in `{colors.accent-lime}` — it's a chip color, not a type color, and breaks contrast at body sizes.
- Don't put illustrations inside cards or constrained containers — their job is to break grid, not occupy it.

## Responsive Behavior

### Breakpoints

| Name         | Width       | Key Changes                                                                              |
| ------------ | ----------- | ---------------------------------------------------------------------------------------- |
| 4K / Wide    | ≥ 1440px    | Full 4-tier pricing row, hero illustration sits beside headline at full scale            |
| Desktop      | 1152–1440px | Default content max-width sits at 1152px, all 4-tier patterns hold                       |
| Laptop       | 992–1151px  | Tier cards collapse to 2-up; below 1024px the sidebar becomes an off-canvas drawer       |
| Tablet       | 768–991px   | 2-column feature grids and form grids collapse to 1-up; KPI cards run 2-up               |
| Mobile Large | 640–767px   | Hero display drops from 88px to ~56px; filters stack                                     |
| Mobile       | 576–639px   | Single-column layout (KPI cards stay 2-up); tables become card lists; section padding 32–48px |
| Small Mobile | 1–575px     | Compact mode; sticker illustrations shrink or hide to preserve content priority       |

### Touch Targets
- Primary buttons hit a minimum 44×44px on mobile (12px vertical padding × 16px font + line-height = ~44px). Maintains WCAG AAA touch-target spec.
- Pill tags and badges in nav and feature surfaces stay above 32×32px even at small mobile breakpoints; they enlarge if necessary rather than shrink.
- Form fields and buttons stay at the 44px minimum height on mobile.

### Collapsing Strategy
- **Hero display headline** drops from 88px → 60px → 48px across the breakpoint stair; the lime keyword chip preserves padding and corner radius at every step.
- **Pricing tiers** stair-step from 4-up → 2-up → 1-up. The featured dark tier always remains visually distinguished — it never loses its inversion at any breakpoint.
- **Sticker illustrations** are progressively de-emphasized: at desktop they overlap section boundaries; at tablet they shift to inline within section padding; at small mobile most are hidden via `display: none` to keep the content scan-able.
- **Sidebar** becomes an off-canvas drawer below 1024px, opened from the topbar menu button; it keeps the canvas polarity of the screen (light, or dark in the dark panel mode). The entry-surface top nav collapses to a menu button below 768px, and the client area switches to `tabbar-mobile` on phones.
- **Data tables** become card lists below 640px: number and status on the first line, client and equipment below, value and row actions at the bottom; secondary columns (technician, date) move to the detail view.
- **Code blocks** preserve 16px Monaco at every breakpoint — they never scale down — but switch to horizontal scroll on overflow rather than wrap.

### Image Behavior
- Product UI mocks scale proportionally; on small mobile they often anchor to one edge with horizontal overflow rather than shrink to illegibility.
- Sticker illustrations scale by 50–70% at mobile breakpoints, preserving their personality but ceding screen space to content.
- The lime footer squiggle scales the SVG to container width while keeping stroke width visually consistent.

## Iteration Guide

1. Focus on ONE component at a time. Don't rebuild the system — extend it.
2. Reference component names and tokens directly (`{colors.accent-lime}`, `{button-primary}-pressed`, `{rounded.xxl}`) — do not paraphrase.
3. Run `npx @google/design.md lint DESIGN.md` after edits — `broken-ref`, `contrast-ratio`, and `orphaned-tokens` warnings flag issues automatically.
4. Add new variants as separate component entries (`-pressed`, `-disabled`, `-focused`) — do not bury them inside prose.
5. Default to `{typography.body-md}` (400) for operational UI body and data, `{typography.label-md}` (500) for labels and names, and `{typography.body-lg}` for entry-surface prose — the leading and weight differences are intentional and load-bearing.
6. Keep `{colors.accent-lime}` scarce — one lime element per viewport. The signature only works because it's rare.
7. Before adding anything orange, confirm it is an action or an active state. If it isn't, it probably wants `{colors.accent-violet-mid}` or a neutral.
8. When polarizing a new surface, choose one canvas (`{colors.surface-canvas-dark}` or `{colors.surface-canvas-light}`) and commit to it; don't blend the two on a single page band.
