<?php
/**
 * Plugin Name:       Latn–Cyrl Bridge (SR)
 * Plugin URI:        https://github.com/plusinnovative/latn-cyrl-bridge
 * Description:       Dvosmjerno preslovljavanje srpskog pisma (ćirilica ↔ latinica) uz SEO podršku (kanonikali, hreflang) i opcioni /lat/ URL prefiks. Fork originalnog SrbTransLatin plugina.
 * Version:           0.1.0
 * Author:            Plus Innovative SRLS
 * Author URI:        https://plusinnovative.com
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       latn-cyrl-bridge
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Original work: SrbTransLatin — Predrag Šupurović / Oblak Solutions (GPL-2.0-or-later)
 * This fork is maintained by Plus Innovative SRLS.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// New fork constants.
if ( ! defined( 'LCB_VERSION' ) )      define( 'LCB_VERSION', '0.1.0' );
if ( ! defined( 'LCB_FILE' ) )         define( 'LCB_FILE', __FILE__ );
if ( ! defined( 'LCB_PATH' ) )         define( 'LCB_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'LCB_URL' ) )          define( 'LCB_URL',  plugin_dir_url( __FILE__ ) );
if ( ! defined( 'LCB_TEXTDOMAIN' ) )   define( 'LCB_TEXTDOMAIN', 'latn-cyrl-bridge' );

// Back-compat constants expected by original SrbTransLatin bootstrap.
if ( ! defined( 'STL_PLUGIN_FILE' ) )      define( 'STL_PLUGIN_FILE', __FILE__ );
if ( ! defined( 'STL_PLUGIN_VERSION' ) )   define( 'STL_PLUGIN_VERSION', '3.2.0-fork' );
if ( ! defined( 'STL_PLUGIN_BASENAME' ) )  define( 'STL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
if ( ! defined( 'STL_PLUGIN_PATH' ) )      define( 'STL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// i18n: load text domain.
add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( LCB_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// Composer autoload first.
require_once __DIR__ . '/vendor/autoload.php';

// Original plugin bootstrap (kept for compatibility with existing structure).
require_once __DIR__ . '/lib/Utils/core.php';
require_once __DIR__ . '/lib/Utils/compat.php';
require_once __DIR__ . '/lib/Utils/compat-sgi.php';

// Activation / deactivation hooks (flush rewrite rules; future: add /lat/ rules before flushing).
register_activation_hook( __FILE__, function () {
	if ( function_exists( 'flush_rewrite_rules' ) ) {
		flush_rewrite_rules();
	}
} );

register_deactivation_hook( __FILE__, function () {
	if ( function_exists( 'flush_rewrite_rules' ) ) {
		flush_rewrite_rules();
	}
} );

// Boot original plugin (STL() is defined in lib/Utils/core.php).
if ( function_exists( 'STL' ) ) {
	STL();
}
