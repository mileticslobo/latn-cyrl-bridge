<?php
<?php
/**
 * Plugin Name:       Latn–Cyrl Bridge (SR)
 * Plugin URI:        https://github.com/plusinnovative/latn-cyrl-bridge
 * Description:       Dvosmjerno preslovljavanje srpskog pisma (ćirilica ↔ latinica) uz SEO podršku (kanonikali, hreflang) i opcioni /lat/ URL prefiks.
 * Version:           0.1.0
 * Author:            Plus Innovative SRLS
 * Author URI:        https://plusinnovative.com
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       latn-cyrl-bridge
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
 * @package SrbTransLatin
 */

defined( 'ABSPATH' ) || exit;

defined( 'STL_PLUGIN_FILE' ) || define( 'STL_PLUGIN_FILE', __FILE__ );
defined( 'STL_PLUGIN_VERSION' ) || define( 'STL_PLUGIN_VERSION', '0.0.0' );
defined( 'STL_PLUGIN_BASENAME' ) || define( 'STL_PLUGIN_BASENAME', plugin_basename( STL_PLUGIN_FILE ) );
defined( 'STL_PLUGIN_PATH' ) || define( 'STL_PLUGIN_PATH', plugin_dir_path( STL_PLUGIN_FILE ) );

require_once __DIR__ . '/lib/Utils/core.php';
require_once __DIR__ . '/lib/Utils/compat.php';
require_once __DIR__ . '/lib/Utils/compat-sgi.php';
require_once __DIR__ . '/vendor/autoload.php';

STL();
