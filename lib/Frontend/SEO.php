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
use function is_array;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function stl_array_map_recursive;
use function wp_parse_url;

class SEO {
    /**
     * Tracks if Yoast hreflang filter ran and emitted alternates
     *
     * @var bool
     */
    private $hreflang_emitted = false;

    /**
     * Tracks buffering state when intercepting Yoast head output.
     *
     * @var bool
     */
    private $yoast_buffering = false;
    public function __construct() {
        // HTML lang attribute.
        add_filter( 'language_attributes', array( $this, 'language_attributes' ), 10, 2 );

        // Yoast integration when available.
        add_filter( 'wpseo_canonical', array( $this, 'yoast_canonical' ), 10, 1 );
        add_filter( 'wpseo_hreflang_urls', array( $this, 'yoast_hreflang' ), 10, 1 );
        if ( defined( 'WPSEO_VERSION' ) ) {
            $string_filters = array(
                'wpseo_title',
                'wpseo_metadesc',
                'wpseo_opengraph_title',
                'wpseo_opengraph_desc',
                'wpseo_opengraph_site_name',
                'wpseo_twitter_title',
                'wpseo_twitter_description',
                'wpseo_author_name',
                'wpseo_twitter_author_name',
            );
            foreach ( $string_filters as $filter ) {
                add_filter( $filter, array( $this, 'maybe_transliterate_string' ), 100, 1 );
            }
            add_filter( 'wpseo_opengraph_url', array( $this, 'yoast_opengraph_url' ), 10, 1 );
            add_action( 'wpseo_head', array( $this, 'yoast_head_buffer_start' ), 0 );
            add_action( 'wpseo_head', array( $this, 'yoast_head_buffer_end' ), PHP_INT_MAX );
            add_filter( 'wpseo_schema_graph', array( $this, 'transliterate_schema_graph' ), 10, 1 );
        }

        add_filter( 'get_the_author_display_name', array( $this, 'maybe_transliterate_string' ), 20, 1 );

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
        // Determine main script preference: 'cir' | 'lat' | null (self).
        $main = apply_filters( 'lcb_main_script', null );
        // Back-compat: force base canonical implies main = 'cir'.
        if ( apply_filters( 'lcb_force_base_canonical', false ) ) {
            $main = 'cir';
        }

        if ( 'cir' === $main ) {
            return $this->ensure_script( $current, 'cir' );
        }
        if ( 'lat' === $main ) {
            return $this->ensure_script( $current, 'lat' );
        }
        // Default: self-canonical per context so both scripts can be indexed.
        return $this->ensure_script( $current, STL()->manager->get_script() );
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
        $base    = $this->ensure_script( $current, 'cir' );
        $lat     = $this->ensure_script( $current, 'lat' );

        $this->hreflang_emitted = true;

        return array(
            $this->lang_tag( $locale, 'Cyrl' ) => $base,
            $this->lang_tag( $locale, 'Latn' ) => $lat,
        );
    }

    /**
     * Begin buffering Yoast head output for transliteration.
     */
    public function yoast_head_buffer_start() {
        if ( ! $this->should_transliterate_meta() || $this->yoast_buffering ) {
            return;
        }
        ob_start();
        $this->yoast_buffering = true;
    }

    /**
     * Flush buffered Yoast head output after transliterating it for the active script.
     */
    public function yoast_head_buffer_end() {
        if ( ! $this->yoast_buffering ) {
            return;
        }

        $output = ob_get_clean();
        $this->yoast_buffering = false;

        if ( false === $output || '' === $output ) {
            return;
        }

        echo $this->maybe_transliterate_string( $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Ensure Yoast schema graph data respects the current script on the front-end.
     *
     * @param array $graph Yoast schema graph data.
     * @return array
     */
    public function transliterate_schema_graph( $graph ) { // phpcs:ignore
        if ( ! $this->should_transliterate_meta() || ! is_array( $graph ) ) {
            return $graph;
        }

        return stl_array_map_recursive( array( $this, 'maybe_transliterate_string' ), $graph );
    }

    /**
     * Ensure Open Graph URL reflects the current script variant.
     *
     * @param string $url URL generated by Yoast.
     * @return string
     */
    public function yoast_opengraph_url( $url ) { // phpcs:ignore
        if ( ! function_exists( 'STL' ) ) {
            return $url;
        }

        $target_script = STL()->manager->get_script();
        return $this->ensure_script( $url, $target_script );
    }

    /**
     * Register query var used to trigger LAT sitemap rendering
     */
    public function register_query_vars( $qv ) { // phpcs:ignore
        $qv[] = 'lcb_lat'; // Back-compat
        $qv[] = 'lcb_script';
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
        $manager    = STL()->manager;
        $source     = $manager->get_source_script();
        $alt_script = 'lat' === $source ? 'cir' : 'lat';

        $index_url = add_query_arg( 'lcb_script', $alt_script, home_url( '/sitemap_index.xml' ) );
        if ( 'lat' === $alt_script ) {
            $index_url = add_query_arg( 'lcb_lat', '1', $index_url );
        }

        $links[] = array(
            'loc'     => $index_url,
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

        $target_script = $this->get_sitemap_target_script();
        if ( null === $target_script ) {
            return $content;
        }

        // Replace each <loc>...</loc> with its LAT variant.
        $content = preg_replace_callback(
            '#<loc>\s*([^<\s]+)\s*</loc>#i',
            function ( $m ) use ( $target_script ) {
                $url = $m[1];
                return '<loc>' . esc_url( $this->ensure_script( $url, $target_script ) ) . '</loc>';
            },
            $content
        );

        return $content;
    }

    public function output_fallback_tags() {
        if ( ! function_exists( 'STL' ) ) {
            return;
        }

        $locale = STL()->manager->get_locale();
        if ( ! in_array( $locale, array( 'sr_RS', 'bs_BA' ), true ) ) {
            return;
        }

        $current      = $this->current_url();
        $cir          = $this->ensure_script( $current, 'cir' );
        $lat          = $this->ensure_script( $current, 'lat' );
        $yoast_active = defined( 'WPSEO_VERSION' );

        // Canonical: only output if Yoast is not active (Yoast handles canonical); honor main-script preference.
        if ( ! $yoast_active ) {
            $main = apply_filters( 'lcb_main_script', null );
            if ( apply_filters( 'lcb_force_base_canonical', false ) ) {
                $main = 'cir';
            }
            if ( 'cir' === $main ) {
                $canonical = $cir;
            } elseif ( 'lat' === $main ) {
                $canonical = $lat;
            } else {
                $canonical = $this->ensure_script( $current, STL()->manager->get_script() );
            }
            echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        // Hreflang: emit if Yoast did not provide hreflang (free version or another SEO plugin).
        if ( ! $this->hreflang_emitted ) {
            echo '<link rel="alternate" href="' . esc_url( $cir ) . '" hreflang="' . esc_attr( $this->lang_tag( $locale, 'Cyrl' ) ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<link rel="alternate" href="' . esc_url( $lat ) . '" hreflang="' . esc_attr( $this->lang_tag( $locale, 'Latn' ) ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    /**
     * Whether transliteration should run for metadata on the current request.
     *
     * @return bool
     */
    private function should_transliterate_meta() {
        if ( ! function_exists( 'STL' ) ) {
            return false;
        }

        return STL()->manager->should_transliterate();
    }

    /**
     * Transliterates string values according to the active script.
     *
     * @param mixed $value Value to convert.
     * @return mixed
     */
    public function maybe_transliterate_string( $value ) { // phpcs:ignore
        if ( ! is_string( $value ) || '' === $value ) {
            return $value;
        }

        $engine = STL()->engine ?? null;
        if ( ! $engine ) {
            return $value;
        }

        if ( $this->should_transliterate_meta() ) {
            return $engine->transliterate( $value );
        }

        // Latin view with Cyrillic names but no global transliteration (e.g. author metadata).
        if ( STL()->manager->is_latin() && preg_match( '/[\x{0400}-\x{04FF}]/u', $value ) ) {
            return $engine->convert_to_latin( $value );
        }

        return $value;
    }

    private function get_sitemap_target_script() {
        $script = get_query_var( 'lcb_script' );
        if ( empty( $script ) && ! empty( get_query_var( 'lcb_lat' ) ) ) {
            $script = 'lat';
        }

        return in_array( $script, array( 'lat', 'cir' ), true ) ? $script : null;
    }

    private function current_url() {
        if ( ! function_exists( 'stl_get_current_url' ) ) {
            return home_url( '/' );
        }
        return stl_get_current_url();
    }

    private function ensure_lat( $url ) {
        return $this->ensure_script( $url, 'lat' );
    }

    private function ensure_base( $url ) {
        $source = function_exists( 'STL' ) ? STL()->manager->get_source_script() : 'cir';
        return $this->ensure_script( $url, $source );
    }

    private function ensure_script( $url, $script ) {
        if ( function_exists( 'lcb_get_script_url' ) ) {
            return lcb_get_script_url( $script, $url );
        }
        return $url;
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

    /**
     * Build a BCP 47 language tag for supported locales and scripts.
     *
     * @param string $locale sr_RS or bs_BA
     * @param string $script Latn or Cyrl
     * @return string e.g. sr-Latn-RS
     */
    private function lang_tag( $locale, $script ) {
        switch ( $locale ) {
            case 'bs_BA':
                return 'bs-' . $script . '-BA';
            case 'sr_RS':
            default:
                return 'sr-' . $script . '-RS';
        }
    }
}
