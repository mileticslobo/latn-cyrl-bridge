<?php
/**
 * SEO helpers: canonical, hreflang, html lang attribute
 *
 * @package LatnCyrlBridge
 */

namespace Oblak\STL\Frontend;

use function add_action;
use function add_filter;
use function add_query_arg;
use function esc_attr;
use function esc_url;
use function get_query_var;
use function home_url;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function wp_parse_url;

class SEO {
    public function __construct() {
        // HTML lang attribute.
        add_filter( 'language_attributes', array( $this, 'language_attributes' ), 10, 2 );

        // Yoast integration when available.
        add_filter( 'wpseo_canonical', array( $this, 'yoast_canonical' ), 10, 1 );
        add_filter( 'wpseo_hreflang_urls', array( $this, 'yoast_hreflang' ), 10, 1 );

        // Yoast sitemap integration (auto-detect, no settings shown).
        add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
        add_filter( 'wpseo_sitemap_index_links', array( $this, 'add_lat_index_link' ) );
        add_filter( 'wpseo_sitemap_content', array( $this, 'rewrite_sitemap_to_lat' ) );

        // Fallback tags when no SEO plugin manages them.
        add_action( 'wp_head', array( $this, 'output_fallback_tags' ), 1 );
    }

    public function language_attributes( $output, $doctype ) { // phpcs:ignore
        if ( ! function_exists( 'STL' ) ) {
            return $output;
        }
        $locale = STL()->manager->get_locale();
        if ( ! in_array( $locale, array( 'sr_RS', 'bs_BA' ), true ) ) {
            return $output;
        }

        $lang = $this->lang_tag( $locale, STL()->manager->is_latin() ? 'Latn' : 'Cyrl' );
        // Replace existing lang="..." or append when missing.
        if ( preg_match( '/lang="[^"]*"/i', $output ) ) {
            $output = preg_replace( '/lang="[^"]*"/i', 'lang="' . esc_attr( $lang ) . '"', $output );
        } else {
            $output .= ' lang="' . esc_attr( $lang ) . '"';
        }
        return $output;
    }

    public function yoast_canonical( $url ) { // phpcs:ignore
        if ( ! function_exists( 'STL' ) ) {
            return $url;
        }
        $current = $this->current_url();
        return STL()->manager->is_latin() ? $this->ensure_lat( $current ) : $this->ensure_base( $current );
    }

    public function yoast_hreflang( $urls ) { // phpcs:ignore
        if ( ! function_exists( 'STL' ) ) {
            return $urls;
        }

        $locale = STL()->manager->get_locale();
        if ( ! in_array( $locale, array( 'sr_RS', 'bs_BA' ), true ) ) {
            return $urls;
        }

        $current = $this->current_url();
        $base    = $this->ensure_base( $current );
        $lat     = $this->ensure_lat( $current );

        return array(
            $this->lang_tag( $locale, 'Cyrl' ) => $base,
            $this->lang_tag( $locale, 'Latn' ) => $lat,
        );
    }

    /**
     * Register query var used to trigger LAT sitemap rendering
     */
    public function register_query_vars( $qv ) { // phpcs:ignore
        $qv[] = 'lcb_lat';
        return $qv;
    }

    /**
     * Append a link to the LAT variant of the sitemap index into Yoast's index.
     * This lets crawlers discover the LAT sitemaps without extra settings.
     */
    public function add_lat_index_link( $links ) { // phpcs:ignore
        if ( ! defined( 'WPSEO_VERSION' ) || ! function_exists( 'STL' ) || ! STL()->manager->is_serbian() ) {
            return $links;
        }
        $lat_index = add_query_arg( 'lcb_lat', '1', home_url( '/sitemap_index.xml' ) );
        $links[]   = array(
            'loc'     => $lat_index,
            'lastmod' => date( 'c' ),
        );
        return $links;
    }

    /**
     * When Yoast is generating a sitemap and lcb_lat=1 is present, rewrite all URLs to their LAT variant.
     */
    public function rewrite_sitemap_to_lat( $content ) { // phpcs:ignore
        if ( ! defined( 'WPSEO_VERSION' ) ) {
            return $content;
        }
        if ( empty( get_query_var( 'lcb_lat' ) ) ) {
            return $content;
        }

        // Replace each <loc>...</loc> with its LAT variant.
        $content = preg_replace_callback(
            '#<loc>\s*([^<\s]+)\s*</loc>#i',
            function ( $m ) {
                $url = $m[1];
                return '<loc>' . esc_url( $this->ensure_lat( $url ) ) . '</loc>';
            },
            $content
        );

        return $content;
    }

    public function output_fallback_tags() {
        // Skip when Yoast or another SEO plugin likely handles these.
        if ( defined( 'WPSEO_VERSION' ) ) {
            return;
        }
        if ( ! function_exists( 'STL' ) ) {
            return;
        }

        $locale = STL()->manager->get_locale();
        if ( ! in_array( $locale, array( 'sr_RS', 'bs_BA' ), true ) ) {
            return;
        }

        $current = $this->current_url();
        $base    = $this->ensure_base( $current );
        $lat     = $this->ensure_lat( $current );

        // Canonical points to current context
        $canonical = STL()->manager->is_latin() ? $lat : $base;

        echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<link rel="alternate" href="' . esc_url( $base ) . '" hreflang="' . esc_attr( $this->lang_tag( $locale, 'Cyrl' ) ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<link rel="alternate" href="' . esc_url( $lat ) . '" hreflang="' . esc_attr( $this->lang_tag( $locale, 'Latn' ) ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    private function current_url() {
        if ( ! function_exists( 'stl_get_current_url' ) ) {
            return home_url( '/' );
        }
        return stl_get_current_url();
    }

    private function ensure_base( $url ) {
        // Remove a single leading /lat segment if present.
        $parts = wp_parse_url( $url );
        if ( empty( $parts ) ) {
            return $url;
        }
        $path = $parts['path'] ?? '/';
        if ( 0 === strpos( $path, '/lat/' ) ) {
            $parts['path'] = substr( $path, 4 );
            if ( '' === $parts['path'] ) {
                $parts['path'] = '/';
            }
        } elseif ( rtrim( $path, '/' ) === '/lat' ) {
            $parts['path'] = '/';
        }
        return $this->unparse_url( $parts );
    }

    private function ensure_lat( $url ) {
        $parts = wp_parse_url( $url );
        if ( empty( $parts ) ) {
            return $url;
        }
        $path = $parts['path'] ?? '/';
        if ( 0 === strpos( $path, '/lat/' ) || rtrim( $path, '/' ) === '/lat' ) {
            return $url;
        }
        $parts['path'] = '/lat' . ( '/' === $path ? '/' : $path );
        return $this->unparse_url( $parts );
    }

    private function unparse_url( $parts ) {
        $scheme   = isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '';
        $user     = $parts['user'] ?? '';
        $pass     = isset( $parts['pass'] ) ? ':' . $parts['pass']  : '';
        $auth     = $user ? $user . $pass . '@' : '';
        $host     = $parts['host'] ?? '';
        $port     = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
        $path     = $parts['path'] ?? '';
        $query    = isset( $parts['query'] ) ? '?' . $parts['query'] : '';
        $fragment = isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';

        return $scheme . $auth . $host . $port . $path . $query . $fragment;
    }
}
