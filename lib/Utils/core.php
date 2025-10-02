<?php
/**
 * Utility functions.
 *
 * @package LatnCyrlBridge
 * @subpackage Utils
 */

use Oblak\STL\SrbTransLatin;

/**
 * SrbTransLatin instance.
 *
 * @return SrbTransLatin
 */
function STL() { // phpcs:ignore
    return SrbTransLatin::instance();
}

/**
 * Fork helper alias
 */
function LCB() { // phpcs:ignore
    return STL();
}

/**
 * Get settings array.
 *
 * @return array[][]
 */
function stl_get_settings_array() {
    return include STL_PLUGIN_PATH . 'config/settings.php';
}

/**
 * Get the available scripts for the website.
 *
 * @return string[]
 */
function stl_get_available_scripts() {
    return array(
        'cir' => __( 'Cyrillic', 'latn-cyrl-bridge' ),
        'lat' => __( 'Latin', 'latn-cyrl-bridge' ),
    );
}

/**
 * Get suffixes for the available scripts.
 *
 * @param string $name_type Name type: native_name, english_name.
 */
function stl_get_script_suffixes( $name_type ) {
    $suffixes = array(
        'sr_RS'      => ' (Ћирилица)',
        'sr_latn_RS' => ' (Латиница)',
    );

    if ( 'english_name' === $name_type || ! str_contains( $name_type, 'sr' ) ) {
        $suffixes = array(
            'sr_RS'      => ' (Cyrillic)',
            'sr_latn_RS' => ' (Latin)',
        );
    }

    return $suffixes;
}

/**
 * Get the current URL.
 *
 * @return string
 */
function stl_get_current_url() {
    global $wp;
    return home_url( add_query_arg( array(), $wp->request ) );
}

/**
 * Get base (Cyrillic) variant of a URL.
 */
function lcb_get_base_url( $url = null ) {
    $source = function_exists( 'STL' ) ? STL()->manager->get_source_script() : 'cir';
    return lcb_get_script_url( $source, $url );
}

/**
 * Get Latin variant of a URL (prefix with /lat).
 */
function lcb_get_lat_url( $url = null ) {
    return lcb_get_script_url( 'lat', $url );
}

/**
 * Map script key to URL slug.
 */
function lcb_script_slug( $script ) {
    return 'lat' === $script ? 'lat' : 'cir';
}

/**
 * Get variant of URL for a given script.
 */
function lcb_get_script_url( $script, $url = null ) {
    $script = in_array( $script, array( 'cir', 'lat' ), true ) ? $script : 'cir';
    $url    = $url ?? stl_get_current_url();
    $parts  = wp_parse_url( $url );
    if ( empty( $parts ) ) {
        return $url;
    }

    $path = $parts['path'] ?? '/';
    $slugs = array(
        'cir' => lcb_script_slug( 'cir' ),
        'lat' => lcb_script_slug( 'lat' ),
    );

    foreach ( $slugs as $slug ) {
        if ( 0 === strpos( $path, '/' . $slug . '/' ) ) {
            $path = substr( $path, strlen( '/' . $slug ) );
            if ( '' === $path ) {
                $path = '/';
            }
            break;
        }
        if ( rtrim( $path, '/' ) === '/' . $slug ) {
            $path = '/';
            break;
        }
    }

    $source_script = 'cir';
    if ( function_exists( 'STL' ) && isset( STL()->manager ) ) {
        $source_script = STL()->manager->get_source_script();
    }

    if ( $script === $source_script ) {
        $parts['path'] = $path;
        return lcb_unparse_url( $parts );
    }

    $slug          = $slugs[ $script ];
    $parts['path'] = '/' . $slug . ( '/' === $path ? '/' : $path );
    return lcb_unparse_url( $parts );
}

function lcb_unparse_url( $parts ) {
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
 * Minimal clean front-end switcher (base vs /lat/)
 */
function lcb_switcher( $args = array(), $echo = true ) {
    $defaults = array(
        'cir_caption' => __( 'Ћирилица', 'latn-cyrl-bridge' ),
        'lat_caption' => __( 'Latinica', 'latn-cyrl-bridge' ),
        'separator'   => ' | ',
    );
    $args = wp_parse_args( $args, $defaults );

    $base = lcb_get_base_url();
    $lat  = lcb_get_lat_url();
    $html = sprintf(
        '<a href="%s">%s</a>%s<a href="%s">%s</a>',
        esc_url( $base ),
        esc_html( $args['cir_caption'] ),
        wp_kses_post( $args['separator'] ),
        esc_url( $lat ),
        esc_html( $args['lat_caption'] )
    );

    if ( $echo ) {
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        return;
    }
    return $html;
}

/**
 * Recursively remaps the keys of an array using the provided callback function.
 *
 * @param callable $callback The callback function to apply to each key.
 * @param array    $arr    The array to remap.
 *
 * @return array The remapped array.
 */
function stl_array_map_recursive( $callback, $arr ) {
    $result = array();
    foreach ( $arr as $key => $value ) {
        if ( is_array( $value ) ) {
            $result[ $key ] = stl_array_map_recursive( $callback, $value );
            continue;
        }

        $result[ $key ] = $callback( $value );
    }

    return $result;
}

/**
 * Display the script selector
 *
 * @param  array $args   Arguments.
 * @param  bool  $eecho  Whether to echo or return the output.
 * @return string|void   HTML for the script selector.
 */
function stl_script_selector( $args, $eecho = true ) {
	$args = wp_parse_args(
        $args,
        array(
			'selector_type' => 'oneline',
			'separator'     => '<span>&nbsp; | &nbsp;</span>',
			'cir_caption'   => 'Ћирилица',
			'lat_caption'   => 'Latinica',
            'inactive_only' => false,
            'active_script' => STL()->manager->get_script(),
            'cir_link'      => lcb_get_script_url( 'cir', stl_get_current_url() ),
            'lat_link'      => lcb_get_script_url( 'lat', stl_get_current_url() ),
        )
	);

    $scripts = array(
        array(
            'name'    => 'cir',
            'link'    => $args['cir_link'],
            'caption' => $args['cir_caption'],
        ),
        array(
            'name'    => 'lat',
            'link'    => $args['lat_link'],
            'caption' => $args['lat_caption'],
        ),
    );

    if ( 'dropdown' !== $args['selector_type'] && $args['inactive_only'] ) {
        $scripts = array_filter(
            $scripts,
            function ( $script ) use ( $args ) {
                return $script['name'] !== $args['active_script'];
            }
        );
    }

    $template = locate_template( 'templates/stl/selector-' . $args['selector_type'] . '.php' );

    if ( ! $template ) {
        $template = STL_PLUGIN_PATH . 'templates/selector-' . $args['selector_type'] . '.php';
    }

    if ( ! $eecho ) {
        ob_start();
    }

    echo '<div class="stl-script-selector">';

    include $template;

    echo '</div>';

    if ( ! $eecho ) {
        return ob_get_clean();
    }
}
