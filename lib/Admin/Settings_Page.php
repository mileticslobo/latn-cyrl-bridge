<?php
/**
 * Minimal admin settings page for Latn–Cyrl Bridge
 *
 * @package LatnCyrlBridge
 */

namespace Oblak\STL\Admin;

class Settings_Page {
    /**
     * Hook everything
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add the submenu under Settings
     */
    public function add_menu() {
        add_options_page(
            __( 'Latn–Cyrl Bridge', 'latn-cyrl-bridge' ),
            __( 'Latn–Cyrl Bridge', 'latn-cyrl-bridge' ),
            'manage_options',
            'latn-cyrl-bridge',
            array( $this, 'render_page' )
        );
    }

    /**
     * Register settings and fields
     */
    public function register_settings() {
        register_setting( 'lcb_settings', 'lcb_default_script', array( $this, 'sanitize_script' ) );
        register_setting( 'lcb_settings', 'lcb_script_priority', array( $this, 'sanitize_priority' ) );
        register_setting( 'lcb_settings', 'lcb_main_script', array( $this, 'sanitize_main' ) );
        register_setting( 'lcb_settings', 'lcb_ajax_enable', array( $this, 'sanitize_checkbox' ) );
        register_setting( 'lcb_settings', 'lcb_ajax_actions', array( $this, 'sanitize_actions' ) );

        add_settings_section( 'lcb_section_main', __( 'General', 'latn-cyrl-bridge' ), '__return_false', 'lcb_settings' );

        add_settings_field(
            'lcb_default_script',
            __( 'Default script (when no choice yet)', 'latn-cyrl-bridge' ),
            array( $this, 'field_default_script' ),
            'lcb_settings',
            'lcb_section_main'
        );

        add_settings_field(
            'lcb_main_script',
            __( 'Canonical target (main script)', 'latn-cyrl-bridge' ),
            array( $this, 'field_main_script' ),
            'lcb_settings',
            'lcb_section_main'
        );

        add_settings_field(
            'lcb_script_priority',
            __( 'Script priority', 'latn-cyrl-bridge' ),
            array( $this, 'field_script_priority' ),
            'lcb_settings',
            'lcb_section_main'
        );

        add_settings_field(
            'lcb_ajax_enable',
            __( 'Enable AJAX transliteration', 'latn-cyrl-bridge' ),
            array( $this, 'field_ajax_enable' ),
            'lcb_settings',
            'lcb_section_main'
        );

        add_settings_field(
            'lcb_ajax_actions',
            __( 'AJAX actions whitelist', 'latn-cyrl-bridge' ),
            array( $this, 'field_ajax_actions' ),
            'lcb_settings',
            'lcb_section_main'
        );
    }

    /**
     * Sanitize 'cir' | 'lat'
     */
    public function sanitize_script( $value ) {
        return in_array( $value, array( 'cir', 'lat' ), true ) ? $value : 'cir';
    }

    /**
     * Sanitize 'cir' | 'lat' | 'self'
     */
    public function sanitize_main( $value ) {
        return in_array( $value, array( 'cir', 'lat', 'self' ), true ) ? $value : 'self';
    }

    /**
     * Sanitize script priority: 'url' | 'cookie'
     */
    public function sanitize_priority( $value ) {
        return in_array( $value, array( 'url', 'cookie' ), true ) ? $value : 'url';
    }

    /**
     * Sanitize checkbox to '1' or '0'
     */
    public function sanitize_checkbox( $value ) {
        return $value ? '1' : '0';
    }

    /**
     * Sanitize comma-separated actions list (a-z0-9_-, trimmed)
     */
    public function sanitize_actions( $value ) {
        $parts = array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );
        $safe  = array();
        foreach ( $parts as $p ) {
            $p = strtolower( preg_replace( '/[^a-z0-9_-]/', '', $p ) );
            if ( '' !== $p ) {
                $safe[] = $p;
            }
        }
        return implode( ', ', array_unique( $safe ) );
    }

    /**
     * Render default script radio
     */
    public function field_default_script() {
        $val = get_option( 'lcb_default_script', 'cir' );
        ?>
        <label><input type="radio" name="lcb_default_script" value="cir" <?php checked( $val, 'cir' ); ?>> <?php esc_html_e( 'Cyrillic (cir)', 'latn-cyrl-bridge' ); ?></label><br>
        <label><input type="radio" name="lcb_default_script" value="lat" <?php checked( $val, 'lat' ); ?>> <?php esc_html_e( 'Latin (lat)', 'latn-cyrl-bridge' ); ?></label>
        <?php
    }

    /**
     * Render main script select
     */
    public function field_main_script() {
        $val = get_option( 'lcb_main_script', 'self' );
        ?>
        <select name="lcb_main_script">
            <option value="self" <?php selected( $val, 'self' ); ?>><?php esc_html_e( 'Self (recommended) — canonical to current script', 'latn-cyrl-bridge' ); ?></option>
            <option value="cir" <?php selected( $val, 'cir' ); ?>><?php esc_html_e( 'Cyrillic — canonical always points to base', 'latn-cyrl-bridge' ); ?></option>
            <option value="lat" <?php selected( $val, 'lat' ); ?>><?php esc_html_e( 'Latin — canonical always points to /lat/', 'latn-cyrl-bridge' ); ?></option>
        </select>
        <?php
    }

    /**
     * Render script priority radios
     */
    public function field_script_priority() {
        $val = get_option( 'lcb_script_priority', 'url' );
        ?>
        <label><input type="radio" name="lcb_script_priority" value="url" <?php checked( $val, 'url' ); ?>> <?php esc_html_e( 'URL first (recommended): /lat forces Latin; base URL forces Cyrillic', 'latn-cyrl-bridge' ); ?></label><br>
        <label><input type="radio" name="lcb_script_priority" value="cookie" <?php checked( $val, 'cookie' ); ?>> <?php esc_html_e( 'Cookie wins: user choice persists even on base URLs', 'latn-cyrl-bridge' ); ?></label>
        <p class="description"><?php esc_html_e( 'URL-first is better for SEO clarity. Cookie-wins can keep Latin content at base URLs if the user last chose Latin.', 'latn-cyrl-bridge' ); ?></p>
        <?php
    }

    /**
     * Render AJAX enable checkbox
     */
    public function field_ajax_enable() {
        $val = get_option( 'lcb_ajax_enable', '0' );
        ?>
        <label>
            <input type="checkbox" name="lcb_ajax_enable" value="1" <?php checked( $val, '1' ); ?>>
            <?php esc_html_e( 'Enable transliteration for selected admin-ajax actions only (not REST).', 'latn-cyrl-bridge' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Leave unchecked unless your front end loads visible text via admin-ajax. This does not affect /wp-json/ REST responses.', 'latn-cyrl-bridge' ); ?></p>
        <?php
    }

    /**
     * Render AJAX actions whitelist
     */
    public function field_ajax_actions() {
        $val = get_option( 'lcb_ajax_actions', '' );
        ?>
        <input type="text" class="regular-text" name="lcb_ajax_actions" value="<?php echo esc_attr( $val ); ?>">
        <p class="description"><?php esc_html_e( 'Comma-separated list of admin-ajax actions to transliterate, e.g. lsvr_load_more, theme_live_search. Only used when enabled above.', 'latn-cyrl-bridge' ); ?></p>
        <?php
    }

    /**
     * Render the page
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Latn–Cyrl Bridge', 'latn-cyrl-bridge' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'lcb_settings' );
                do_settings_sections( 'lcb_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
