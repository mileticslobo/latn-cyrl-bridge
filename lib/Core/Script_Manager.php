<?php
/**
 * Script_Manager class file.
 *
 * @package LatnCyrlBridge
 * @since 3.0.0
 */

namespace Oblak\STL\Core;

/**
 * Detects scripts and
 */
class Script_Manager {

    /**
     * Current website script
     *
     * @var string
     */
    private $script;

    /**
     * Script used when authoring content (source script).
     *
     * @var string
     */
    private $source_script = 'cir';

    /**
     * Whether request is under /lat/ prefix
     *
     * @var bool
     */
    private $prefix_active = false;

    /**
     * Website locale
     *
     * @var string
     */
    private $locale;

    /**
     * Class constructor
     */
    public function __construct() {
        \add_action( 'plugins_loaded', array( $this, 'determine_script' ), 500 );
        // Allow stripping script prefixes (e.g. /lat/, /cir/) before query parsing.
        \add_action( 'pre_parse_request', array( $this, 'detect_script_prefix' ), 0 );
    }

    /**
     * Determines the current script for the website
     */
    public function determine_script() {
        // Do not set or change script in admin or REST to avoid interfering with authentication flows.
        if ( ( function_exists( 'is_admin' ) && is_admin() ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            $this->script        = 'cir';
            $this->locale        = STL()->ml->get_locale();
            $this->source_script = $this->resolve_source_script();
            return;
        }
        $requested_script = sanitize_text_field( wp_unslash( $_REQUEST[$this->get_url_param()] ?? '' ) ); //phpcs:ignore

        // Detect /lat/ prefix early via REQUEST_URI for initial script decision.
        $uri_path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
        $path     = ltrim( $uri_path, '/' );
        $priority = get_option( 'lcb_script_priority', 'url' ); // url | cookie
        if ( 'lat' === $path || 0 === strpos( $path, 'lat/' ) ) {
            $this->prefix_active = true;
            $requested_script    = 'lat';
        } elseif ( 'cir' === $path || 0 === strpos( $path, 'cir/' ) ) {
            $this->prefix_active = true;
            $requested_script    = 'cir';
        } elseif ( 'url' === $priority ) {
            // URL-first: base URLs enforce the content source script.
            $requested_script = $this->resolve_source_script();
        }

        $this->script        = $this->get_cookie( $requested_script );
        $this->locale        = STL()->ml->get_locale();
        $this->source_script = $this->resolve_source_script();
    }

    /**
     * Detect /lat/ prefix and strip it from $wp->request before parsing.
     *
     * @param \WP $wp WP object.
     */
    public function detect_script_prefix( $wp ) { // phpcs:ignore
        $req = ltrim( (string) $wp->request, '/' );
        foreach ( array( 'lat', 'cir' ) as $slug ) {
            if ( $slug === $req || 0 === strpos( $req, $slug . '/' ) ) {
                $this->prefix_active = true;
                $this->script        = $slug;

                // Strip the leading script segment so WP can resolve the actual content.
                $stripped    = ltrim( substr( $req, strlen( $slug ) ), '/' );
                $wp->request = $stripped; // e.g. '' for homepage, or 'category/foo'.
                break;
            }
        }
    }

    /**
     * Get the URL parameter for the script
     *
     * @return string URL parameter for the script
     */
    public function get_url_param() {
        return STL()->get_settings( 'general', 'url_param' );
    }

    /**
     * Get the current script if the site is in Serbian language
     *
     * @return string `cir` or `lat` Depending on the currently selected script
     */
    public function get_script() {
        return $this->script;
    }

    /**
     * Get the current locale for the website
     *
     * @return string Current website locale
     */
    public function get_locale() {
        return $this->locale;
    }

    /**
     * Checks if the current script is latin
     *
     * @return bool
     */
    public function is_latin() {
        return $this->is_serbian() && 'lat' === $this->get_script();
    }

    /**
     * Checks if the current script is cyrillic
     *
     * @return bool
     */
    public function is_cyrillic() {
        return $this->is_serbian() && 'cir' === $this->get_script();
    }

    /**
     * Checks if the current script is Serbian
     *
     * @return bool
     */
    public function is_serbian() {
        return 'sr_RS' === $this->get_locale();
    }

    /**
     * Checks if the current locale is supported for dual-script handling
     *
     * @return bool
     */
    public function is_supported_locale() {
        return in_array( $this->get_locale(), array( 'sr_RS', 'bs_BA', 'mk_MK' ), true );
    }

    /**
     * Whether current request is served under a prefixed script path
     *
     * @return bool
     */
    public function is_prefix_active() {
        return $this->prefix_active;
    }

    /**
     * Checks if the site should be transliterated
     *
     * @return bool True if the site should be transliterated, false otherwise
     */
    public function should_transliterate() {
        return 'none' !== $this->get_transliteration_direction();
    }

    /**
     * Get the source script for stored content.
     */
    public function get_source_script() {
        return $this->source_script;
    }

    /**
     * Determine transliteration direction for the current request.
     *
     * @return string 'cir_to_lat', 'lat_to_cir', or 'none'.
     */
    public function get_transliteration_direction() {
        if ( ! in_array( $this->locale, array( 'mk_MK', 'sr_RS', 'bs_BA' ), true ) ) {
            return 'none';
        }

        if ( $this->script === $this->source_script ) {
            return 'none';
        }

        if ( 'cir' === $this->source_script && 'lat' === $this->script ) {
            return 'cir_to_lat';
        }

        if ( 'lat' === $this->source_script && 'cir' === $this->script ) {
            return 'lat_to_cir';
        }

        return 'none';
    }

    /**
     * Get the cookie for the current script
     *
     * @param  string $requested_script Requested script.
     * @return string                   Cookie value
     */
    private function get_cookie( $requested_script ) {
        if ( ! empty( $requested_script ) ) {
            return $this->set_cookie( $requested_script );
        }

        $cookie = sanitize_text_field( wp_unslash( $_COOKIE['stl_script'] ?? '' ) );
        if ( ! empty( $cookie ) ) {
            return $cookie;
        }

        // Allow themes/plugins to override the default script when no cookie is set.
        $default = apply_filters( 'lcb_default_script', STL()->get_settings( 'general', 'default_script' ) );
        $default = in_array( $default, array( 'cir', 'lat' ), true ) ? $default : 'cir';
        return $this->set_cookie( $default );
    }

    /**
     * Set the cookie for the current script
     *
     * @param  string $requested_script Requested script.
     * @return string                   Value that was set in the cookie
     */
    private function set_cookie( $requested_script ) {
        if ( headers_sent() ) {
            return $requested_script;
        }

        setcookie( 'stl_script', $requested_script, 0, '/', wp_parse_url( home_url() )['host'], is_ssl() );

        return $requested_script;
    }

    /**
     * Resolve authoring script (content source).
     */
    private function resolve_source_script() {
        $default = STL()->get_settings( 'general', 'content_script' );
        $default = in_array( $default, array( 'cir', 'lat' ), true ) ? $default : 'cir';
        $value   = apply_filters( 'lcb_content_script', $default );
        return in_array( $value, array( 'cir', 'lat' ), true ) ? $value : 'cir';
    }
}
