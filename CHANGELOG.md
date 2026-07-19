# Changelog

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
