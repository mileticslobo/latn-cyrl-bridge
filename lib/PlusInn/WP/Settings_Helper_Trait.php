<?php
/**
 * PlusInn\WP\Settings_Helper_Trait wrapper
 */

namespace PlusInn\WP;

if ( ! trait_exists( __NAMESPACE__ . '\\Settings_Helper_Trait', false ) && trait_exists( '\\Oblak\\WP\\Settings_Helper_Trait' ) ) {
    trait Settings_Helper_Trait {
        use \Oblak\WP\Settings_Helper_Trait;
    }
}
