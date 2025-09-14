<?php
/**
 * PlusInn class aliases for nicer DX without changing autoload
 *
 * @package LatnCyrlBridge
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
// Provide a PlusInn API surface mapping to existing classes

// Helper to alias if autoloadable
if ( ! function_exists( 'PlusInn\\LCB\\_alias' ) ) {
    function _alias( $from, $to ) {
        if ( ! class_exists( $to, false ) && class_exists( $from ) ) {
            class_alias( $from, $to );
        }
    }
}

_alias( 'Oblak\\STL\\SrbTransLatin', 'PlusInn\\LCB\\Plugin' );
_alias( 'Oblak\\Transliterator', 'PlusInn\\Transliterator' );
_alias( 'Oblak\\STL\\Core\\Engine', 'PlusInn\\LCB\\Core\\Engine' );
_alias( 'Oblak\\STL\\Core\\Script_Manager', 'PlusInn\\LCB\\Core\\Script_Manager' );
_alias( 'Oblak\\STL\\Core\\Multi_Language', 'PlusInn\\LCB\\Core\\Multi_Language' );
_alias( 'Oblak\\STL\\Frontend\\Url_Rewriter', 'PlusInn\\LCB\\Frontend\\Url_Rewriter' );
_alias( 'Oblak\\STL\\Frontend\\Menu_Extender', 'PlusInn\\LCB\\Frontend\\Menu_Extender' );
_alias( 'Oblak\\STL\\Frontend\\Title_Transliterator', 'PlusInn\\LCB\\Frontend\\Title_Transliterator' );
_alias( 'Oblak\\STL\\Frontend\\SEO', 'PlusInn\\LCB\\Frontend\\SEO' );
_alias( 'Oblak\\STL\\Frontend\\Search_Query_Transliterator', 'PlusInn\\LCB\\Frontend\\Search_Query_Transliterator' );
_alias( 'Oblak\\STL\\Widget\\Selector_Widget', 'PlusInn\\LCB\\Widget\\Selector_Widget' );
_alias( 'Oblak\\STL\\Shortcode\\Shortcode_Manager', 'PlusInn\\LCB\\Shortcode\\Shortcode_Manager' );
_alias( 'Oblak\\STL\\Shortcode\\Translator', 'PlusInn\\LCB\\Shortcode\\Translator' );
_alias( 'Oblak\\STL\\Shortcode\\Selective_Output', 'PlusInn\\LCB\\Shortcode\\Selective_Output' );
_alias( 'Oblak\\STL\\Shortcode\\Base_Shortcode', 'PlusInn\\LCB\\Shortcode\\Base_Shortcode' );
_alias( 'Oblak\\STL\\Language\\WPML', 'PlusInn\\LCB\\Language\\WPML' );

