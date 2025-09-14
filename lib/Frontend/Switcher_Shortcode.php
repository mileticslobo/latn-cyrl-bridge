<?php
/**
 * Switcher Shortcode
 *
 * @package LatnCyrlBridge
 */

namespace Oblak\STL\Frontend;

/**
 * Registers [lcb_switcher] shortcode that renders the base/lat switcher
 */
class Switcher_Shortcode {
    public function __construct() {
        \add_shortcode( 'lcb_switcher', array( $this, 'render' ) );
    }

    public function render( $atts = array(), $content = '' ) {
        if ( ! function_exists( 'lcb_switcher' ) ) {
            return '';
        }
        return lcb_switcher( (array) $atts, false );
    }
}

