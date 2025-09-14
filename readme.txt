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

Latn–Cyrl Bridge lets you publish content once in Serbian Cyrillic (primary script) while automatically serving Latin content under the same structure, prefixed with `/lat/`. Both versions are indexable, with correct canonical and hreflang signals.

Highlights:

- Transliteration: Titles, content, menus and search are transliterated to Latin in `/lat/` mode.
- Dual URLs: `/path` (Cyrillic) and `/lat/path` (Latin) resolve to the same content.
- SEO: Proper canonical and `hreflang` pairs (`sr-Cyrl-RS` ↔ `sr-Latn-RS`), `<html lang>` reflects context.
- Yoast SEO: Auto-detected. Canonical/hreflang filters applied; a Latin sitemap variant is exposed alongside the default index.
- Internal links: In Latin mode, all internal links point to `/lat/` variants.

Fork notes: Based on SrbTransLatin. Rebranded and extended for `/lat/` strategy and SEO.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins`, or install via the WP admin.
2. Activate the plugin.
3. Visit your site at `/` (Cyrillic) and `/lat/` (Latin) to verify.

== Frequently Asked Questions ==

= Do I need to enter content twice? =
No. Enter content in Cyrillic; the plugin serves transliterated Latin under `/lat/`.

= Does this work with Yoast SEO? =
Yes. Yoast is auto-detected; the plugin provides canonical/hreflang and a Latin sitemap variant without extra settings.

= Will internal links point to /lat/ in Latin mode? =
Yes. Most internal URLs are prefixed with `/lat` automatically in Latin mode, excluding admin/login/REST URLs.

== Changelog ==

= 0.1.0 =
- Initial fork and rebranding
- `/lat/` prefix routing and link prefixing
- Canonical/hreflang and `<html lang>` handling
- Yoast canonical/hreflang filters and Latin sitemap variant
