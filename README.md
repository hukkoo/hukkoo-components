# hukkoo-component

A standalone, framework-agnostic WordPress design-system library — not a
plugin. It has no `Plugin Name` header on purpose. A host plugin requires
it directly and boots it explicitly:

```php
require_once __DIR__ . '/hukkoo-component/hukkoo-components.php';

hukkoo_components(__FILE__, [
    'showcase' => true,
])->boot();
```

Requires PHP 8.1+ (readonly properties, enums-adjacent constructs used
throughout `src/`). No build step, no Composer requirement at runtime —
classes autoload via a small `spl_autoload_register()` mapping
`Hukkoo\Components\*` to `src/` (and `Hukkoo\Components\Showcase\*` to
`Showcase/`).

## Layout

```
hukkoo-components.php   Entry point: constants, autoloader, hukkoo_components() factory
index.php                Directory-listing guard (present in every directory)

assets/
  css/
    variables.css        Design tokens ONLY — colors, spacing, type, radius, shadow, z-index
    base.css              Reset + typography, scoped under .hk-page
    layout.css             Structural shell: Container/Stack/Grid/Toolbar/Sidebar/Header
    components.css          Component visuals, one banner-commented section per component
    utilities.css             Escape-hatch utility classes, loaded last
  js/hukkoo-components.js  Vanilla JS: modal, tabs, dropdown, focus trap — no jQuery, delegated

src/
  Library.php             Composition root / singleton bootstrapper
  AssetManager.php         Enqueue orchestration for the CSS chain + JS
  ComponentRegistry.php     Named-component resolution, filterable
  Component.php              Abstract base: class/attribute builder + enforced escaping
  Html.php                    Explicit "this string is already safe HTML" wrapper
  Version.php                  Filterable version resolution
  Layout/                       Structural components (Container, …)
  Components/                    Generic UI components (Button, Card, …)
  Forms/                          Form, Field (abstract), concrete field types (Text, …)
  Data/                            Table (and other data-display components)
  Business/                        Domain-flavored composites — empty by design, extend here

Showcase/                Optional, self-contained admin demo/docs app
  Showcase.php             admin_menu + enqueue registration + page shell
  ShowcaseRouter.php        slug → renderer map (explicit argument, never reads $_GET itself)
  Navigation.php             Nav tree, takes the active slug as an explicit argument
  Gallery/
    Registry.php              Auto-discovers *Gallery.php via glob() — no manual registration
    Contracts/GalleryInterface.php
    Components/*Gallery.php    One file per component category (ButtonGallery is the reference one)
    GalleryPage.php / GallerySection.php / CodeBlock.php / CodeHighlighter.php / PhpLiteral.php
    ApiReference.php            Renders a live $args table via Reflection on the class docblock
  Pages/                          Static doc pages (Homepage, …)
  Helpers/Template.php
  assets/{css,js}/                 Showcase-only chrome, layered on the shared design system
```

## House rules

These exist because each one was a real bug once. Breaking them is a
regression, not a style nit.

1. **Never `@import` a stylesheet you want cache-busted.** WordPress's
   `?ver=` only reaches URLs that are actually enqueued. `@import`-ed
   sub-resources carry no query string, so a browser can cache one forever
   regardless of version bumps. Every layer in `assets/css/` is enqueued
   individually by `AssetManager`, each versioned off its own
   `filemtime()` so editing one file invalidates only that file's cache.

2. **Escaping is enforced at one choke point, not left to convention.**
   `Component::text()` / `::url()` / `::cell()` escape by default; the
   only way to output raw HTML from a leaf value is the explicit
   `Html::raw()` wrapper, which is a visible, greppable decision. Don't
   `echo`/string-concatenate raw `$this->args[...]` values inside a
   `render()` body — route them through those helpers. See the
   escaping-convention note in `Component.php` for the
   trusted-composition-vs-untrusted-data distinction (child component
   output is trusted; request/DB-derived leaf values are not).

3. **Every file starts with the `ABSPATH` guard; every directory gets an
   `index.php` stub.** Scaffold both when creating a new file/directory —
   don't retrofit later.

4. **Superglobals are read once, sanitized, at the entry point — never
   mutated, never re-read deep in the call stack.** `Showcase.php` does
   the one `sanitize_key(wp_unslash($_GET['tab']))` read for the whole
   Showcase tree and passes `$slug` down as an explicit argument through
   `Navigation` and `ShowcaseRouter`. If you add a new route source, keep
   that shape.

5. **Any method contracted to `render(): string` must never call a
   WordPress function with print-by-default semantics without explicitly
   passing the "don't echo" flag.** `wp_nonce_field()` defaults to
   `$echo = true` — inside a method that's supposed to return a string,
   that prints the nonce field at the wrong point in the page and
   silently breaks whatever depended on it being inside the `<form>` this
   method is building. `Forms/Form.php` calls it as
   `wp_nonce_field($action, $name, true, false)` and splices the return
   value in. Grep for any future `wp_*_field(` / similar call before
   merging.

6. **Don't ship a config option that doesn't do anything.** There is
   deliberately no `'prefix'` config key. CSS class names and custom
   properties are hardcoded to `hk-` throughout `assets/`; a real
   per-product prefix would require templating/generating the CSS at
   build time, which this base doesn't do. `Library::prefix()` is a fixed
   value, not a config lookup — if that ever changes, the CSS has to
   change with it in the same PR, not just the PHP default.

## Starting a new product from this base

1. Give the new host plugin a real `Plugin Name` header, Text Domain,
   `readme.txt`, `uninstall.php`.
2. Vendor this library with `require_once` + `hukkoo_components(FILE,
   $config)->boot()`, or package it via Composer if the product needs
   independent versioning from the host.
3. Extend the component set with `add_filter('hukkoo_components_register',
   …)` rather than forking `Component.php` — you keep its escaping
   guarantees for free.
4. New product-specific stylesheets declare `['hukkoo-components']`
   (`AssetManager::public_style_handle()`) as a dependency and get their
   own `filemtime()`-based version — see `Showcase::enqueue()` for the
   pattern. Never `@import`.
5. Add a `*Gallery.php` implementing `GalleryInterface` for any new
   component — `Registry::boot()`'s `glob()` picks it up with zero
   registration code, and you get a live example + a Reflection-derived
   API table for free. `ButtonGallery.php` is the reference example.
6. Apply the house rules above as defaults on every new file, not as a
   retrofit pass before merge.
