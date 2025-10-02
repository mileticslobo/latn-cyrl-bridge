<?php
/**
 * Url_Rewriter class file
 *
 * @package LatnCyrlBridge
 */

namespace Oblak\STL\Frontend;

/**
 * Adds script prefixes to internal links when serving a non-source script
 */
class Url_Rewriter {
    public function __construct() {
        \add_filter( 'home_url', array( $this, 'prefix_home_url' ), 10, 4 );

        // Extra safety for direct link filters that might bypass home_url.
        foreach ( array( 'post_link', 'page_link', 'term_link', 'attachment_link', 'post_type_archive_link', 'author_link', 'day_link', 'month_link', 'year_link' ) as $filter ) {
            \add_filter( $filter, array( $this, 'prefix_url' ), 10, 1 );
        }
    }

    /**
     * Prefix URLs generated via home_url() with the active script slug when needed.
     */
    public function prefix_home_url( $url, $path, $orig_scheme, $blog_id ) { // phpcs:ignore
        return $this->maybe_prefix( $url );
    }

    /**
     * Prefix direct URLs when serving a non-source script
     */
    public function prefix_url( $url ) { // phpcs:ignore
        return $this->maybe_prefix( $url );
    }

    private function maybe_prefix( $url ) {
        // Only for Serbian Latin on frontend.
        if ( ! function_exists( 'STL' ) ) {
            return $url;
        }

        $manager = STL()->manager;
        if ( ! $manager->should_transliterate() ) {
            return $url;
        }

        $target_script = $manager->get_script();
        $source_script = $manager->get_source_script();

        if ( $target_script === $source_script ) {
            return $url;
        }

        $parts = \wp_parse_url( $url );
        if ( empty( $parts ) || empty( $parts['host'] ) ) {
            return $url;
        }

        $path = isset( $parts['path'] ) ? $parts['path'] : '/';

        $slug = lcb_script_slug( $target_script );

        // Already prefixed with target slug or is a file path like /wp-admin/, skip admin.
        if (
            0 === strpos( $path, '/' . $slug . '/' ) || rtrim( $path, '/' ) === '/' . $slug ||
            0 === strpos( $path, '/wp-admin' ) || 0 === strpos( $path, '/wp-login' ) || 0 === strpos( $path, '/wp-json' )
        ) {
            return $url;
        }

        // Remove other script slugs to avoid stacking.
        foreach ( array( 'lat', 'cir' ) as $other_slug ) {
            if ( $other_slug === $slug ) {
                continue;
            }
            if ( 0 === strpos( $path, '/' . $other_slug . '/' ) ) {
                $path = substr( $path, strlen( '/' . $other_slug ) );
                if ( '' === $path ) {
                    $path = '/';
                }
                break;
            }
            if ( rtrim( $path, '/' ) === '/' . $other_slug ) {
                $path = '/';
                break;
            }
        }

        // Prefix.
        $parts['path'] = '/' . $slug . ( '/' === $path ? '/' : $path );

        return $this->unparse_url( $parts );
    }

    private function unparse_url( $parts ) {
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
}
