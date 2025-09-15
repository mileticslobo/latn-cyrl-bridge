=== Latn–Cyrl Bridge (SR) ===
Contributors: plusinnovative
Tags: transliteration, Serbian, Cyrillic, Latin, hreflang, canonical, Yoast SEO
Requires at least: 6.0
Tested up to: 6.3.1
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Two-way transliteration for Serbian (Ćirilica ↔ Latinica) with SEO support and optional /lat/ URL prefix. Fork of SrbTransLatin.

== Description ==

Latn–Cyrl Bridge lets you publish content once in Serbian Cyrillic (primary script) while automatically serving Latin content under the same structure, prefixed with `/lat/`. Both versions can be indexed, with correct canonical/hreflang and `<html lang>`.

Highlights:

- Transliteration: Titles, content, menus and search transliterate to Latin in `/lat/` mode.
- Dual URLs: `/path` (Cyrillic) and `/lat/path` (Latin) resolve to the same content.
- SEO: Canonical (self‑canonical by default), `hreflang` pairs (`sr-Cyrl-RS` ↔ `sr-Latn-RS` or `bs-…-BA`), `<html lang>` reflects context.
- Yoast SEO: Auto-detected. Canonical/hreflang filters applied; a Latin sitemap variant is discoverable from the main index. Works with Yoast Free (fallback hreflang provided).
- Internal links: In Latin mode, all internal links point to `/lat/` variants.

Fork notes: Based on SrbTransLatin. Rebranded and extended for `/lat/` routing and SEO.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins`, or install via the WP admin.
2. Activate the plugin.
3. Visit Settings → Permalinks → Save (flush rewrites).
4. Visit your site at `/` (Cyrillic) and `/lat/` (Latin) to verify.

= Script priority =
- URL first (default): `/lat/...` forces Latin; base URLs force Cyrillic. Best for SEO clarity.
- Cookie wins: user’s last choice persists even on base URLs. More UX-driven; base URLs can show Latin content.

== Frequently Asked Questions ==

= Do I need to enter content twice? =
No. Enter content in Cyrillic; the plugin serves transliterated Latin under `/lat/`.

= Does this work with Yoast SEO? =
Yes. Yoast is auto-detected. Canonical/hreflang integrate via filters; when Yoast Free is used (no hreflang), the plugin outputs hreflang tags itself. A Latin sitemap variant is discoverable from the main index.

= Will internal links point to /lat/ in Latin mode? =
Yes. Most internal URLs are prefixed with `/lat` automatically in Latin mode, excluding admin/login/REST URLs.

== Changelog ==

= 0.1.0 =
- Initial fork and rebranding
- `/lat/` prefix routing and link prefixing
- Canonical/hreflang and `<html lang>` handling
- Yoast canonical/hreflang filters and Latin sitemap variant
