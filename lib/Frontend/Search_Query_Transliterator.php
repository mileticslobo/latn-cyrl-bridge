<?php
/**
 * Search_Query_Transliterator class file.
 *
 * @package LatnCyrlBridge
 */

namespace Oblak\STL\Frontend;

use function add_action;
use function add_filter;
use function add_query_arg;
use function is_search;
use function wp_safe_redirect;
use function wp_unslash;

use WP_Query;

/**
 * Transliterates search query
 */
class Search_Query_Transliterator {
    /**
     * Class constructor
     */
    public function __construct() {
        add_filter( 'posts_search', array( $this, 'convert_terms_to_cyrillic' ), 100, 2 );
        add_action( 'template_redirect', array( $this, 'redirect_to_matching_script' ), 1 );
    }

    /**
     * Expands search SQL so Latin and Cyrillic queries match either script.
     *
     * Runs when the cross-script search option is enabled, on the main front-end
     * search query for supported locales, and the search string contains
     * characters we can transliterate.
     *
     * @param  string   $search   Search SQL clause.
     * @param  WP_Query $wp_query WP_Query object.
     * @return string             Modified search SQL clause.
     */
    public function convert_terms_to_cyrillic( $search, $wp_query ) {
        if (
            ! STL()->get_settings( 'advanced', 'fix_search' ) ||
            ! $wp_query instanceof WP_Query ||
            ! STL()->manager->is_serbian() ||
            ! $wp_query->is_main_query()
        ) {
            return $search;
        }

        $raw = (string) $wp_query->get( 's' );
        if ( '' === trim( $raw ) || 'none' === $this->detect_script( $raw ) ) {
            return $search;
        }

        // Modify the order by clause to match the search terms.
        add_filter( 'posts_search_orderby', array( $this, 'modify_search_orderby' ), 100, 2 );

        return $this->parse_search( $wp_query->query_vars );
    }

    /**
     * Modifes the search orderby clause to match the search terms.
     *
     * @param  string   $orderby  Orderby SQL clause.
     * @param  WP_Query $wp_query WP_Query object.
     * @return string             Modified orderby SQL clause.
     */
    public function modify_search_orderby( $orderby, $wp_query ) {
        $orderby = $this->parse_search_order( $wp_query->query_vars );
        return $orderby;
    }

    /**
     * Redirect search results to the matching script variant based on query script.
     */
    public function redirect_to_matching_script() {
        if ( ! STL()->get_settings( 'advanced', 'fix_search' ) || ! STL()->manager->is_serbian() || ! is_search() ) {
            return;
        }

        $query = isset( $_GET['s'] ) ? trim( (string) wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( '' === $query ) {
            return;
        }

        $target_script = $this->detect_script( $query );
        if ( in_array( $target_script, array( 'none', 'mixed' ), true ) ) {
            return;
        }

        $current_script = STL()->manager->get_script();
        if ( $current_script === $target_script ) {
            return;
        }

        if ( ! function_exists( 'stl_get_current_url' ) ) {
            return;
        }

        $current_url = stl_get_current_url();
        if ( function_exists( 'lcb_get_script_url' ) ) {
            $destination = lcb_get_script_url( $target_script, $current_url );
        } elseif ( 'lat' === $target_script && function_exists( 'lcb_get_lat_url' ) ) {
            $destination = lcb_get_lat_url( $current_url );
        } else {
            $destination = function_exists( 'lcb_get_base_url' ) ? lcb_get_base_url( $current_url ) : null;
        }

        if ( empty( $destination ) || $destination === $current_url ) {
            return;
        }

        $query_args = ! empty( $_GET ) ? wp_unslash( $_GET ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! empty( $query_args ) ) {
            $destination = add_query_arg( $query_args, $destination );
        }

        wp_safe_redirect( $destination );
        exit;
    }

    /**
     * Generates SQL for the WHERE clause based on passed search terms.
     * Copied from `wp-includes/class-wp-query.php`
     *
     * Modified to transliterate latin search terms to cyrillic.
     *
     * @since 3.7.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param array $q Query variables.
     * @return string WHERE clause.
     */
    private function parse_search( &$q ) {
        global $wpdb;

        $search = '';

        // Added slashes screw with quote grouping when done early, so done later.
        $q['s'] = stripslashes( $q['s'] );

        // There are no line breaks in <input /> fields.
        $q['s']                  = str_replace( array( "\r", "\n" ), '', $q['s'] );
        $q['search_terms_count'] = 1;
        if ( ! empty( $q['sentence'] ) ) {
            $q['search_terms'] = array( $q['s'] );
        } elseif ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['s'], $matches ) ) {
            $q['search_terms_count'] = count( $matches[0] );
            $q['search_terms']       = $this->parse_search_terms( $matches[0] );
            // If the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
            if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 ) {
                $q['search_terms'] = array( $q['s'] );
            }
        } else {
            $q['search_terms'] = array( $q['s'] );
        }

        $n         = ! empty( $q['exact'] ) ? '' : '%';
        $searchand = '';

        $q['search_title_like_groups'] = array();
        $q['search_title_like_flat']   = array();

        /**
         * Filters the prefix that indicates that a search term should be excluded from results.
         *
         * @since 4.7.0
         *
         * @param string $exclusion_prefix The prefix. Default '-'. Returning
         *                                 an empty value disables exclusions.
         */
        $exclusion_prefix = apply_filters( 'wp_query_search_exclusion_prefix', '-' );

        foreach ( $q['search_terms'] as $term ) {
            // If there is an $exclusion_prefix, terms prefixed with it should be excluded.
            $exclude = $exclusion_prefix && ( substr( $term, 0, 1 ) === $exclusion_prefix );
            if ( $exclude ) {
                $like_op  = 'NOT LIKE';
                $andor_op = 'AND';
                $term     = substr( $term, 1 );
            } else {
                $like_op  = 'LIKE';
                $andor_op = 'OR';
            }

            $variants          = $this->get_variants( $term );
            $variant_clauses   = array();
            $title_like_clauses = array();

            foreach ( $variants as $variant ) {
                $like = $n . $wpdb->esc_like( $variant ) . $n;
                // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $clause  = '(' . "{$wpdb->posts}.post_title {$like_op} %s" . ')';
                $clause .= " {$andor_op} (" . "{$wpdb->posts}.post_excerpt {$like_op} %s" . ')';
                $clause .= " {$andor_op} (" . "{$wpdb->posts}.post_content {$like_op} %s" . ')';

                $variant_clauses[] = $wpdb->prepare(
                    $clause,
                    $like,
                    $like,
                    $like
                );
                //phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

                if ( $n && ! $exclude ) {
                    $title_like_clauses[] = $wpdb->prepare( "{$wpdb->posts}.post_title $like_op %s", $like );
                }
            }

            if ( $n && ! $exclude && ! empty( $title_like_clauses ) ) {
                $q['search_title_like_groups'][] = '(' . implode( ' OR ', $title_like_clauses ) . ')';
                $q['search_title_like_flat']     = array_merge( $q['search_title_like_flat'], $title_like_clauses );
            }

            if ( empty( $variant_clauses ) ) {
                continue;
            }

            $search   .= $searchand . '(' . implode( ' ' . $andor_op . ' ', $variant_clauses ) . ')';
            $searchand = ' AND ';
        }

        $q['search_title_like_groups'] = array_values( array_unique( $q['search_title_like_groups'] ) );
        $q['search_title_like_flat']   = array_values( array_unique( $q['search_title_like_flat'] ) );

        if ( ! empty( $search ) ) {
            $search = " AND ({$search}) ";
            if ( ! is_user_logged_in() ) {
                $search .= " AND ({$wpdb->posts}.post_password = '') ";
            }
        }

        return $search;
    }

    /**
     * Generates SQL for the ORDER BY condition based on passed search terms.
     *
     * @since 3.7.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param array $q Query variables.
     * @return string ORDER BY clause.
     */
    protected function parse_search_order( &$q ) {
        global $wpdb;

        $flat_clauses   = isset( $q['search_title_like_flat'] ) ? array_values( array_unique( $q['search_title_like_flat'] ) ) : array();
        $group_clauses  = isset( $q['search_title_like_groups'] ) ? array_values( array_unique( $q['search_title_like_groups'] ) ) : array();
        $has_multi_terms = ( $q['search_terms_count'] ?? 0 ) > 1;

        $order_parts = array();

        foreach ( $flat_clauses as $clause ) {
            $order_parts[] = "WHEN {$clause} THEN 1";
        }

        if ( $has_multi_terms && ! empty( $group_clauses ) ) {
            $group_count = count( $group_clauses );
            if ( $group_count < 7 ) {
                $order_parts[] = 'WHEN ' . implode( ' AND ', $group_clauses ) . ' THEN 2';
                if ( $group_count > 1 ) {
                    $order_parts[] = 'WHEN ' . implode( ' OR ', $group_clauses ) . ' THEN 3';
                }
            }
        }

        $sentence_likes = array();
        if ( ! preg_match( '/(?:\s|^)\-/', $q['s'] ) ) {
            foreach ( $this->get_variants( $q['s'] ) as $variant ) {
                $sentence_likes[] = '%' . $wpdb->esc_like( $variant ) . '%';
            }
            $sentence_likes = array_unique( $sentence_likes );
        }

        foreach ( $sentence_likes as $like ) {
            $order_parts[] = $wpdb->prepare( "WHEN {$wpdb->posts}.post_excerpt LIKE %s THEN 4 ", $like );
            $order_parts[] = $wpdb->prepare( "WHEN {$wpdb->posts}.post_content LIKE %s THEN 5 ", $like );
        }

        if ( ! $has_multi_terms && empty( $order_parts ) && ! empty( $flat_clauses ) ) {
            return '(' . implode( ' OR ', $flat_clauses ) . ') DESC';
        }

        if ( empty( $order_parts ) ) {
            return "{$wpdb->posts}.post_date DESC";
        }

        return '(CASE ' . implode( ' ', $order_parts ) . ' ELSE 6 END)';
    }

    /**
     * Check if the terms are suitable for searching.
     *
     * Uses an array of stopwords (terms) that are excluded from the separate
     * term matching when searching for posts. The list of English stopwords is
     * the approximate search engines list, and is translatable.
     *
     * Copied from `wp-includes/class-wp-query.php`
     *
     * @since 3.7.0
     *
     * @param  string[] $terms Array of terms to check.
     * @return string[]        Terms that are not stopwords.
     */
    private function parse_search_terms( $terms ) {
        $strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
        $checked    = array();

        $stopwords = $this->get_search_stopwords();

        foreach ( $terms as $term ) {
            // Keep before/after spaces when term is for exact match.
            if ( preg_match( '/^".+"$/', $term ) ) {
                $term = trim( $term, "\"'" );
            } else {
                $term = trim( $term, "\"' " );
            }

            // Avoid single A-Z and single dashes.
            if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
                continue;
            }

            if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) ) {
                continue;
            }

            $checked[] = $term;
        }

        return $checked;
    }

    /**
     * Retrieve stopwords used when parsing search terms.
     *
     * Copied from `wp-includes/class-wp-query.php`
     *
     * @since 3.7.0
     *
     * @return string[] Stopwords.
     */
    private function get_search_stopwords() {
        /*
         * translators: This is a comma-separated list of very common words that should be excluded from a search,
         * like a, an, and the. These are usually called "stopwords". You should not simply translate these individual
         * words into your language. Instead, look for and provide commonly accepted stopwords in your language.
         */
        $words = explode(
            ',',
            _x(
                'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
                'Comma-separated list of search stopwords in your language',
                'default'
            )
        );

        $stopwords = array();
        foreach ( $words as $word ) {
            $word = trim( $word, "\r\n\t " );
            if ( $word ) {
                $stopwords[] = $word;
            }
        }

        /**
         * Filters stopwords used when parsing search terms.
         *
         * @since 3.7.0
         *
         * @param string[] $stopwords Array of stopwords.
         */
        return apply_filters( 'wp_search_stopwords', $stopwords );
    }

    /**
     * Build unique transliteration variants for a string (original, Latin, Cyrillic)
     */
    private function get_variants( $content ) {
        $content  = (string) $content;
        $variants = array( $content );

        if ( function_exists( 'STL' ) ) {
            $stl = STL();
            if ( isset( $stl->engine ) ) {
                $variants[] = $stl->engine->convert_to_latin( $content );
                $variants[] = $stl->engine->convert_to_cyrillic( $content );
            }
        }

        $variants = array_filter(
            array_unique( $variants ),
            static function ( $value ) {
                return '' !== $value;
            }
        );

        return array_values( $variants );
    }

    /**
     * Detect which script the string predominantly uses.
     */
    private function detect_script( $content ) {
        $has_latin     = $this->is_latin_string( $content );
        $has_cyrillic  = $this->is_cyrillic_string( $content );

        if ( $has_latin && $has_cyrillic ) {
            return 'mixed';
        }
        if ( $has_latin ) {
            return 'lat';
        }
        if ( $has_cyrillic ) {
            return 'cir';
        }

        return 'none';
    }

    /**
     * Checks if the string contains latin characters
     *
     * @param  string $content String to check.
     * @return bool            True if the string contains latin characters, false otherwise.
     */
    private function is_latin_string( $content ) {
        return (bool) preg_match( '/[\p{Latin}]+/u', $content );
    }

    /**
     * Checks if the string contains cyrillic characters
     */
    private function is_cyrillic_string( $content ) {
        return (bool) preg_match( '/[\p{Cyrillic}]+/u', $content );
    }
}
