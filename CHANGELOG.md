# Changelog

## 0.3.0 — 2026-07-19

First real host integration (`hukkoo-core`'s "All Tables" screen), which
surfaced a new reusable component and two real CSS bugs the Showcase
alone never happened to exercise.

**Added**
- `Data\CrudTable` — the "Full example" pattern from the Table gallery
  (search, sortable columns, an Add button, per-row View/Edit/Delete with
  a delete-confirmation Modal, client-side pagination) as an actual
  component instead of hand-assembled markup. Composes `Table`, `Text`,
  `Button`, `Modal` and `Pagination`; registered in `ComponentRegistry`
  as `crud-table` and documented in the Showcase. `hukkoo-core`'s own
  "All Tables" admin screen is built on this class.
- `CrudTable`: `numbered` arg prefixes each row with a running "Sl.No"
  column, renumbered client-side on every search/sort/page change to
  match each row's current position rather than a fixed per-row id.
- `Table`: a row may carry a reserved `_attrs` key rendered onto its
  `<tr>` — lets a host's own JS sort/search against a column's real
  value instead of its formatted display (e.g. a Badge).
- `Data\ListTable` — the server-driven counterpart to `CrudTable`, for a
  host with its own query layer (real SQL search/sort/LIMIT-OFFSET)
  instead of a small in-memory dataset: every interaction (search, column
  sort, pagination) is a real navigation — a link or a GET form — rather
  than client-side JS re-filtering rows already on the page. Adds a
  checkbox column with bulk actions (a "Bulk actions" select + Apply,
  submitting the checked rows to a host-provided endpoint) and per-row
  Edit/Delete as plain links with a JS `confirm()`, matching how WP's own
  list tables work. Composes `Table`, `Text`, `Select`, `Button` and the
  now dual-mode `Pagination`. Registered as `list-table`. `hukkoo-core`'s
  per-table records screens (`admin.php?page=hukkoo-data-{slug}`), a real
  `WP_List_Table` subclass previously, are now built on this class —
  which also surfaced and fixed a pre-existing gap where bulk/single
  record delete had a working handler that was never actually hooked up.
- `Pagination`: `url` arg — when set, renders real `<a href>` navigation
  (the current page and disabled prev/next ends render as an inert
  `<span>`) instead of `data-hk-page` buttons, for `ListTable` and any
  other server-driven host where each page is an actual page load.

**Changed**
- The `hk-demo-toolbar`/`hk-demo-footer`/`hk-demo-cell-*`/`hk-demo-actions`
  classes moved from the Showcase's own stylesheet into the public
  `components.css` chain, renamed to `hk-table-*`. They were assumed to
  be Showcase-only demo chrome; the first real host to need the same
  toolbar/footer layout (via `CrudTable`) proved that assumption wrong.
- `CrudTable` (and now `ListTable`) carry their own card frame (padding,
  border, shadow) instead of relying on it — the padded/bordered look in
  the Showcase demos came entirely from Showcase-only wrapper chrome, so
  on a real host the toolbar and table floated directly on the bare page
  with no spacing around them. The Showcase now strips its own copy of
  the same frame from either component's output to avoid a doubled box.
- `.wrap.hk-page` (the shape a real host page like `hukkoo-core`'s admin
  screens is built as) now has its own padding — WP core's `.wrap` only
  ever adds a small margin, so a page's heading/notices/content sat
  flush against the sidebar edge. Scoped to `.wrap` specifically so it
  doesn't affect the Showcase, which lays out its own sidebar/content
  grid and isn't wrapped in `.wrap`.

**Fixed**
- `.hk-table-wrap` used `overflow: hidden` to clip the `<table>`'s square
  corners to the wrapper's rounded ones — but with no `overflow-x`, a
  table wider than its container (an unbounded host field count, e.g.
  hukkoo-core's per-table records screens, isn't something this library
  can size for up front) silently clipped the columns that didn't fit
  instead of scrolling to them. Split into `overflow-x: auto` (so it
  scrolls) and `overflow-y: hidden` (still just the corner-clip's job —
  row count is pagination's responsibility, not a vertical scrollbar
  here).
- `.hk-button--outline:hover` hardcoded white text regardless of whether
  a color variant was set, so a colorless outline `Button` (e.g. the
  View/Edit row actions `CrudTable`/`ListTable` render) turned white
  text on a near-white gray hover fill — unreadable. Inconsistent with
  every other style variant (`--dash`, `--soft`, `--ghost`), which
  already fall back correctly when no color is set. Added a
  `--hk-btn-c-on` token (mirroring the existing `--hk-btn-c`/
  `--hk-btn-c-hover` pair, set alongside them by each `.hk-button--*`
  color rule) so `--outline`'s hover state can fall back to
  `--hk-color-text` when no color variant is present, and only use
  on-color white when one actually is (e.g. Delete's solid-red hover).
- A solid-color `Button` rendered as `<a>` (via its `url` arg) lost its
  white text on hover, falling back to WP admin's own `a:hover { color }`
  rule — its `(0,1,1)` specificity beat the base `.hk-button--primary`'s
  `(0,1,0)`, and the hover-state color/background rule never re-asserted
  `color` so it didn't contest it. Nearly unreadable, since the fallback
  color was close to the button's own hover background. Fixed by setting
  `color` explicitly in that already-higher-specificity hover rule.
- `.hk-page a` (the base link-color reset) had higher specificity than
  `.hk-button`'s own color rules, so any `Button` rendered as `<a>` (via
  its `url` arg) lost its intended text color — invisible on solid
  variants. Fixed with `:not(:where(.hk-button))` rather than a plain
  `:not(.hk-button)` — the latter would have (and briefly did) made the
  rule's own specificity higher than before, regressing unrelated a-tag
  rules elsewhere, like the Showcase's own active sidebar link.

## 0.2.0 — 2026-07-19

Full Showcase coverage of `ComponentRegistry`, plus the Toast/Table/Form
component work that came out of designing those galleries.

**Added**
- `hukkoo-components.php` now supports multiple Hukkoo plugins vendoring
  their own copy of this library on the same site: each copy registers a
  version candidate on load, and the actual bootstrap (constants,
  autoloader) resolves lazily on first `hukkoo_components()` call to
  whichever copy is newest — not just whichever plugin's file WordPress
  happened to require first. A single active plugin behaves exactly as
  before; this only matters once a second Hukkoo plugin is introduced.
- Gallery pages for every remaining registered component: `Card`,
  `Container`, `Toast`, `Form`, `Modal`, `Badge`, `IconButton`,
  `Pagination` — all 21 entries in `ComponentRegistry::defaults()` now
  have a live example page (was 3 before this release).
- `Badge`: `outline` and `dot` args for the status-pill look (colored dot
  on a bordered surface instead of a solid fill) — adopted by the Table
  gallery's status/stock cells.
- `Form`: `layout` arg (`'stacked'|'grid'`) for a two-fields-per-row
  form; `Field`/`Checkbox`/`Radio` gained a matching `width`
  (`'auto'|'full'`) so one field can span every grid column.
- `Toast`: a server-rendered "static" mode (`message`/`title`/`color`
  args) alongside the existing JS-driven `window.hkToast()` shell, for
  docs previews and flash-message-style usage.
- Showcase: a proper landing page (header, card grid of every
  component), sidebar nav grouped by category, and a responsive
  breakpoint (matching wp-admin's own 782px) so the layout stacks on
  narrow screens instead of squeezing.

**Changed**
- `ComponentRegistry::defaults()` now lists all 21 components; it
  previously only mapped 3, so `ComponentRegistry::make()` silently
  couldn't resolve most of what the library actually ships.
- `Toast` CSS redesigned to a card layout (icon, title, message, dismiss
  button); `Table` header typography and row spacing refreshed.
- `Forms/Form.php` builds its submit button via the real `Button`
  component instead of hand-rolled markup; `Text`/`Textarea`/`Number`
  share one `Field::field_input_class()` instead of duplicating the
  color/size/ghost class logic.

**Fixed**
- `ApiReference`'s docblock parser was misreading a wrapped multi-line
  `$args` description as extra bogus rows (its first two words read as a
  fake name/type) — it now uses indentation to detect continuation
  lines.
- Remaining hardcoded `#fff` values in `components.css` replaced with
  the `--hk-color-on-color` token.

## 0.1.0 — 2026-07-18

Initial scaffold of the standalone component library base.

- Composition root (`Library`), autoloader, `ComponentRegistry`,
  `AssetManager`, `Component`/`Html` escaping primitives.
- Layered CSS chain (`variables` → `base` → `layout` → `components` →
  `utilities`), each enqueued and versioned individually — no `@import`.
- Vanilla JS behavior layer (modal, tabs, dropdown, focus trap).
- Reference components: `Layout\Container`, `Components\Button`,
  `Components\Card`, `Forms\Field`/`Forms\Text`/`Forms\Form`, `Data\Table`.
- Showcase app: auto-discovering `Gallery\Registry`, explicit-argument
  `ShowcaseRouter`, Reflection-based `ApiReference`, and `ButtonGallery`
  as the reference gallery implementation.
