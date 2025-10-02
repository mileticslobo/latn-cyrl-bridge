=== Latn–Cyrl Bridge (SR) ===
Contributors: plusinnovative
Tags: transliteration, Serbian, Cyrillic, Latin, hreflang, canonical, Yoast SEO
Requires at least: 6.0
Tested up to: 6.3.1
Requires PHP: 8.0
Stable tag: 1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Two-way transliteration for Serbian (Ćirilica ↔ Latinica) with SEO support and optional script-prefixed URLs. Fork of SrbTransLatin.

== Description ==

Latn–Cyrl Bridge lets you publish content once — in whichever script you author (Cyrillic or Latin) — and automatically serves the opposite script under the same structure (prefixed paths such as `/lat/` or `/cir/`). Both variants can be indexed with correct canonical/hreflang tags and `<html lang>` values.

Highlights:

- Transliteration: Titles, content, menus, gettext strings and search results transliterate in either direction (Cyrillic ↔ Latin) based on the active view.
- Dual URLs: `/path` serves the source script; `/lat/path` or `/cir/path` expose the other script automatically.
- SEO: Canonical (self‑canonical by default), `hreflang` pairs (`sr-Cyrl-RS` ↔ `sr-Latn-RS` or `bs-…-BA`), `<html lang>` reflects context.
- Yoast SEO: Auto-detected. Canonical/hreflang filters, full meta/transparency transliteration (title, description, OG/Twitter, schema), and a Latin sitemap variant expose both scripts cleanly. Works with Yoast Free (fallback hreflang provided).
- Internal links: When viewing the secondary script, internal links are prefixed with the matching `/lat/` or `/cir/` variant automatically.
- Cross-script search: One search box works across scripts; queries redirect to the matching view with results immediately.

Fork notes: Based on SrbTransLatin. Rebranded and extended for `/lat/` routing and SEO.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins`, or install via the WP admin.
2. Activate the plugin.
3. Visit Settings → Permalinks → Save (flush rewrites).
4. Open Settings → Latn–Cyrl Bridge to choose the content source script, default script, and other preferences.
5. Visit your site at `/` (source script) and the generated counterpart (e.g. `/lat/` or `/cir/`) to verify.

= Script priority =
- URL first (default): `/lat/...` forces Latin; base URLs force Cyrillic. Best for SEO clarity.
- Cookie wins: user’s last choice persists even on base URLs. More UX-driven; base URLs can show Latin content.

== Frequently Asked Questions ==

= Do I need to enter content twice? =
No. Choose your authoring script in Settings → Latn–Cyrl Bridge (Cyrillic or Latin); the plugin transliterates to the other script on demand.

= Can I keep content in Latin and still show a Cyrillic site? =
Yes. Set “Content source script” to Latin. The base URLs will stay Latin while `/cir/...` (or `/lat/...` if you reverse it later) serves Cyrillic.

= Does this work with Yoast SEO? =
Yes. Yoast is auto-detected. Canonical/hreflang integrate via filters; when Yoast Free is used (no hreflang), the plugin outputs hreflang tags itself. A Latin sitemap variant is discoverable from the main index.

= Will internal links point to /lat/ in Latin mode? =
Yes. Most internal URLs are prefixed with `/lat` automatically in Latin mode, excluding admin/login/REST URLs.

= Does search work across scripts? =
Yes. With “Cross-script search” enabled (default), Latin queries find Cyrillic content and vice versa, with automatic redirection to the matching script view.

== Changelog ==

= Unreleased =
- Nothing yet.

= 1.2 =
- New “Content source script” option to run the site with either Cyrillic or Latin as the stored script.
- Engine, titles, menus, and URL rewrites now transliterate both directions (cir→lat and lat→cir) depending on the active view.
- Added `/cir/` routing, script-aware canonical/hreflang helpers, and Yoast sitemap support for whichever script is secondary.
- Cross-script search now redirects with the original query intact and matches content regardless of input script.

= 0.1.0 =
- Initial fork and rebranding
- `/lat/` prefix routing and link prefixing
- Canonical/hreflang and `<html lang>` handling
- Yoast canonical/hreflang filters and Latin sitemap variant
