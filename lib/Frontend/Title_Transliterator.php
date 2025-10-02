<?php
/**
 * Title_Transliterator class file
 *
 * @package LatnCyrlBridge
 */

namespace Oblak\STL\Frontend;

use function add_filter;

/**
 * Transliterates page title
 *
 * @since 3.0.0
 */
class Title_Transliterator {
    /**
     * Class constructor
     */
    public function __construct() {
        add_filter( 'wp_title', array( $this, 'transliterate_title' ), 100, 1 );
        add_filter( 'pre_get_document_title', array( $this, 'transliterate_title' ), 100, 1 );
        add_filter( 'document_title_parts', array( $this, 'transliterate_title_parts' ), 100, 1 );
    }

    /**
     * Transliterates the title
     *
     * @param  string $title Page title.
     * @return string        Transliterated title.
     */
    public function transliterate_title( $title ) {
        if ( current_theme_supports( 'title-tag' ) || ! STL()->get_settings( 'advanced', 'fix_titles' ) ) {
            return $title;
        }

        if ( 'none' === STL()->manager->get_transliteration_direction() ) {
            return $title;
        }

        return STL()->engine->transliterate( $title );
    }

    /**
     * Transliterates the title parts
     *
     * @param  string[] $parts Title parts.
     * @return string[]        Transliterated title parts.
     */
    public function transliterate_title_parts( $parts ) {
        if ( ! STL()->get_settings( 'advanced', 'fix_titles' ) ) {
            return $parts;
        }

        if ( 'none' === STL()->manager->get_transliteration_direction() ) {
            return $parts;
        }

        return array_map( array( STL()->engine, 'transliterate' ), $parts );
    }
}
