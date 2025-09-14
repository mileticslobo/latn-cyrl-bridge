# Latn–Cyrl Bridge (SR)

Two-way Serbian transliteration (Ćirilica ↔ Latinica) with `/lat/` URLs and SEO integration (canonical, hreflang, HTML `lang`), plus automatic Yoast SEO support including a Latin sitemap variant.

Fork of SrbTransLatin, reworked to support clean `/lat/` routing while keeping a single source of content in Cyrillic. Original authors credited in the plugin header.

Core features:
- `/lat/` URL prefix with transparent content resolution
- Transliteration of titles, content, menus, search in Latin mode
- Canonical + hreflang pairs (`sr-Cyrl-RS` ↔ `sr-Latn-RS`), `<html lang>`
- Yoast: canonical/hreflang filters + Latin sitemap variant
- Internal link prefixing to keep navigation in Latin mode

Requirements: PHP 8+, WordPress 6.0+
