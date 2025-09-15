# Latn–Cyrl Bridge (SR)

Two-way Serbian transliteration (Ćirilica ↔ Latinica) with `/lat/` URLs and SEO integration (canonical, hreflang, HTML `lang`), plus Yoast SEO support (Free and Premium). Keep content in Cyrillic and serve Latin under `/lat/` — no duplication.

Fork of SrbTransLatin, reworked to support clean `/lat/` routing while keeping a single source of content in Cyrillic. Original authors credited in the plugin header.

## Features

- `/lat/` URL prefix with transparent content resolution
- Transliteration of titles, content, menus, search in Latin mode
- SEO signals
  - `<html lang>`: `sr-Cyrl-RS` ↔ `sr-Latn-RS` (or `bs-…-BA`)
  - Canonical: self‑canonical per context (optional filter to force base)
  - Hreflang: alternates for the two scripts; works with Yoast Free via fallback
- Yoast
  - Canonical/hreflang filters
  - Latin sitemap index discoverable from the main index
- Internal link prefixing to keep navigation in Latin mode

## Requirements

- PHP 8.0+
- WordPress 6.0+

## Install & Setup

1. Copy to `wp-content/plugins/latn-cyrl-bridge` and activate.
2. Permalinks: visit Settings → Permalinks → Save (flush).
3. Verify `/` (Cyrillic) and `/lat/` (Latin) both load.

Script priority (Settings → Latn–Cyrl Bridge)
- URL first (default): `/lat/...` forces Latin; base URLs force Cyrillic. Best for SEO clarity since URL maps to script.
- Cookie wins: user’s last choice persists even on base URLs. Useful for UX, but base URLs can show Latin content; consider SEO implications.

## Switcher

- Template helper: `<?php if ( function_exists('lcb_switcher') ) { lcb_switcher(); } ?>`
- Shortcode: `[lcb_switcher]`
- Optional body class for styling:
  ```php
  add_filter('body_class', function ($c) {
      if ( function_exists('STL') && STL()->manager->is_latin() ) $c[] = 'script-latin';
      else $c[] = 'script-cyrillic';
      return $c;
  });
  ```

## Yoast SEO

- Canonical
  - Default: self‑canonical per context so both scripts can be indexed.
  - Force base (Cyrillic) canonical if desired:
    ```php
    add_filter('lcb_force_base_canonical', '__return_true');
    ```
- Hreflang
  - If Yoast emits hreflang (Premium), those are used.
  - If not (Yoast Free), plugin outputs hreflang in `wp_head`.
- Sitemaps
  - Main index: `/sitemap_index.xml` includes a Latin index entry.
  - Latin index: appending `?lcb_lat=1` lists `/lat/...` URLs in child sitemaps.
  - You can submit both indices to GSC:
    - `https://example.com/sitemap_index.xml`
    - `https://example.com/sitemap_index.xml?lcb_lat=1`

## Supported Locales

- Serbian (`sr_RS`): `sr-Cyrl-RS` / `sr-Latn-RS`
- Bosnian (`bs_BA`): `bs-Cyrl-BA` / `bs-Latn-BA`

## Helpers & Filters

- URL helpers: `lcb_get_base_url($url)`, `lcb_get_lat_url($url)`
- Switcher: `lcb_switcher($args = [], $echo = true)`
- Filters:
  - `lcb_transliteration_priority` (int)
  - `lcb_default_script` (return 'cir' or 'lat' to set cookie default)
  - `lcb_main_script` ('cir' | 'lat' | null for self‑canonical)
  - `lcb_force_base_canonical` (bool, deprecated in favor of `lcb_main_script`)

## Notes

- Routing: `/lat/...` is routed to WP and stripped before query parsing.
- Internal links are prefixed to `/lat/` in Latin mode; admin/login/REST are excluded.
- REST/admin safety: Plugin does not run on `/wp-json` REST requests or in wp-admin; AJAX transliteration is disabled by default and can be enabled per action.

## License

GPL-2.0-or-later.
