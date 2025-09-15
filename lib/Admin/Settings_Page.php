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
        register_setting( 'lcb_settings', 'lcb_main_script', array( $this, 'sanitize_main' ) );

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

