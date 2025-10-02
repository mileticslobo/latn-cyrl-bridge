<?php
/**
 * SrbTransLatin class file.
 *
 * @package LatnCyrlBridge
 */

namespace Oblak\STL;

// Admin UI removed in this fork.
use Oblak\WP\Settings_Helper_Trait;
use function add_action;
use function add_filter;
use function apply_filters;
use function get_option;
use function do_action;
use function is_admin;
use function determine_locale;
use function load_textdomain;
use function plugin_dir_path;
use function register_widget;

/**
 * Main plugin class wrapping all of the functionalities
 */
class SrbTransLatin {
    use Settings_Helper_Trait;

    /**
     * Plugin instance
     *
     * @var SrbTransLatin
     */
    private static $instance = null;

    /**
     * Undocumented variable
     *
     * @var Core\Script_Manager
     */
    public $manager;

    /**
     * Shortcodes Manager
     *
     * @var Shortcode\Shortcode_Manager
     */
    public $shortcodes;

    /**
     * Transliteration engine
     *
     * @var Core\Engine
     */
    public $engine;

    /**
     * Multi-language plugin
     *
     * @var Core\Multi_Language
     */
    public $ml;

    /**
     * Get plugin instance
     *
     * @return SrbTransLatin
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Checks if the current request is of a certain type
     *
     * @param  string $type Request type: admin, ajax, cron, frontend.
     * @return bool         True if the current request is of the given type
     */
    public function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined( 'DOING_AJAX' );
            case 'cron':
                return defined( 'DOING_CRON' );
            case 'frontend':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Determines if the website should be transliterated
     *
     * @return bool
     */
    public function should_transliterate() {
        return $this->manager->should_transliterate();
    }

    /**
     * Class constructor
     */
    private function __construct() {
        // Use a fork-specific option prefix to avoid collisions with the original plugin.
        $this->settings = $this->load_settings( 'latn_cyrl_bridge', stl_get_settings_array()['settings'], null );
        $this->load_classes();
        $this->init_hooks();
    }



    /**
     * Loads the plugin classes
     * */
    private function load_classes() {
        $this->manager    = new Core\Script_Manager();
        $this->shortcodes = new Shortcode\Shortcode_Manager();
        $this->ml         = new Core\Multi_Language();

        // No admin UI in this fork; settings are loaded programmatically.

        new Frontend\Search_Query_Transliterator();
    }

    /**
     * Plugin hooks
     */
    public function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'bootstrap_textdomain' ) );
        add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
        add_action( 'plugins_loaded', array( $this, 'ml_plugin_compat' ), -1 );
        add_action( 'widgets_init', array( $this, 'register_widget' ) );
        add_action( 'plugins_loaded', array( $this, 'register_option_filters' ), 1 );
    }

    /**
     * Loads the plugin textdomain
     */
    public function bootstrap_textdomain() {
        $textdomain = LCB_TEXTDOMAIN;

        /**
         * Skip bundled translation loading.
         *
         * Allow site owners to short-circuit the manual loader if they prefer to rely on
         * WordPress.org language packs exclusively.
         *
         * @since 1.2.0
         *
         * @param bool $skip Whether to skip loading bundled translations.
         */
        if ( apply_filters( 'lcb_skip_manual_textdomain_load', false ) ) {
            return;
        }

        $locale = determine_locale();
        if ( empty( $locale ) ) {
            return;
        }

        $mofile = plugin_dir_path( LCB_FILE ) . 'languages/' . $textdomain . '-' . $locale . '.mo';
        if ( file_exists( $mofile ) ) {
            load_textdomain( $textdomain, $mofile );
        }
    }

    /**
     * Actions to be performed when the plugin is loaded
     */
    public function on_plugins_loaded() {
        // Skip REST requests entirely to avoid interfering with plugins like Site Kit.
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            // Engine also guards REST; this prevents URL rewriter / SEO filters on REST calls.
            $this->engine = new Core\Engine();
            do_action( 'lcb_loaded' );
            return;
        }

        if ( $this->is_request( 'frontend' ) ) {
            new Frontend\Menu_Extender();
            new Frontend\Title_Transliterator();
            // Prefix internal URLs in Latin mode and manage SEO tags.
            new Frontend\Url_Rewriter();
            new Frontend\SEO();
            new Frontend\Switcher_Shortcode();
        }

        $this->engine = new Core\Engine();

        /**
         * Fired when SrbTransLatin is loaded
         *
         * @since 3.0.0
         */
        // Fire new hook
        do_action( 'lcb_loaded' );
    }

    /**
     * Map saved options to runtime filters and load admin page
     */
    public function register_option_filters() {
        // Default script cookie when unset
        add_filter( 'lcb_default_script', function ( $val ) {
            $opt = get_option( 'lcb_default_script', '' );
            return in_array( $opt, array( 'cir', 'lat' ), true ) ? $opt : $val;
        }, 5 );

        add_filter( 'lcb_content_script', function ( $val ) {
            $opt = get_option( 'lcb_content_script', '' );
            return in_array( $opt, array( 'cir', 'lat' ), true ) ? $opt : $val;
        }, 5 );

        // Global canonical target
        add_filter( 'lcb_main_script', function ( $val ) {
            $opt = get_option( 'lcb_main_script', 'self' );
            if ( in_array( $opt, array( 'cir', 'lat' ), true ) ) {
                return $opt;
            }
            return null; // self‑canonical
        }, 5 );

        if ( $this->is_request( 'admin' ) ) {
            new Admin\Settings_Page();
        }
    }

    /**
     * Loads the multi-language plugin compatibility
     */
    public function ml_plugin_compat() {
        switch ( $this->ml->get_ml_plugin() ) {
            case 'translatepress':
                // Nothing for now.
                break;
            case 'wpml':
                new Language\WPML();
                break;
        }
    }

    /**
     * Registers the widget
     */
    public function register_widget() {
        register_widget( Widget\Selector_Widget::class );
    }
}
