<?php
    /**
     * Settings config
     *
     * @package latn-cyrl-bridge
     * @subpackage Config
     */

defined( 'ABSPATH' ) || exit;

$wplang = get_locale();

$disable_permalinks = 'sr_RS' === $wplang || 'bs_BA' === $wplang;
$navigation_menus   = get_registered_nav_menus();

$nav_menus = 0 === count( $navigation_menus ) ? false : true;

return array(
    'name'     => __( 'Latn–Cyrl Bridge (SR)', 'latn-cyrl-bridge' ),
    'basename' => STL_PLUGIN_BASENAME,
    'meta'     => array(
        array(
            'link' => 'https://github.com/plusinnovative/latn-cyrl-bridge',
            'text' => __( 'Documentation', 'latn-cyrl-bridge' ),
        ),
    ),
    'page'     => array(
        'root'       => true,
        'parent'     => 'options-general.php',
        'title'      => __( 'Latinisation', 'latn-cyrl-bridge' ),
        'menu_title' => __( 'Settings', 'default' ),
        'cap'        => 'manage_options',
        // 'image' removed in this fork (no admin UI), keep entry unused.
        'prio'       => 99,
    ),
    'settings' => array(
        'general'        => array(
            array(
                'title' => _x( 'General settings', 'section name', 'latn-cyrl-bridge' ),
                'type'  => 'title',
                'desc'  => __( 'General settings control main functionality of the plugin', 'latn-cyrl-bridge' ),
                'id'    => 'lcb_general_settings',
            ),

            array(
                'title'   => __( 'Default script', 'latn-cyrl-bridge' ),
                'desc'    => __( 'Default script used for the website if user did not select a script', 'latn-cyrl-bridge' ),
                'id'      => 'default_script',
                'type'    => 'select',
                'default' => 'cir',
                'options' => stl_get_available_scripts(),
            ),

            array(
                'title'   => __( 'Content source script', 'latn-cyrl-bridge' ),
                'desc'    => __( 'Script used when authoring content in WordPress (the plugin will transliterate to the other script on demand)', 'latn-cyrl-bridge' ),
                'id'      => 'content_script',
                'type'    => 'select',
                'default' => 'cir',
                'options' => stl_get_available_scripts(),
            ),

            array(
                'title'   => __( 'URL Parameter', 'latn-cyrl-bridge' ),
                'id'      => 'url_param',
                'desc'    => __( 'URL parameter used for script selector', 'latn-cyrl-bridge' ),
                'type'    => 'text',
                'default' => 'pismo',
            ),

            array(
                'type' => 'sectionend',
                'id'   => 'lcb_general_settings',
            ),

            array(
                'title' => _x( 'Menu settings', 'section name', 'latn-cyrl-bridge' ),
                'type'  => 'title',
                'desc'  => __( 'Options that control the display of the script selector', 'latn-cyrl-bridge' ),
                'id'    => 'lcb_menu_settings',
            ),

            array(
                'type' => 'sectionend',
                'id'   => 'lcb_menu_settings',
            ),
        ),
        'menu'           => array(
            array(
                'title' => _x( 'Navigation menu settings', 'section name', 'latn-cyrl-bridge' ),
                'type'  => 'title',
                'desc'  => __( 'Menu settings control extending and tweaking the script selector in theme menus', 'latn-cyrl-bridge' ),
                'id'    => 'lcb_menu_settings',
            ),
            array(
                'id'   => 'lcb_menu_warning',
                'type' => 'info',
                'text' => ! $nav_menus
                    ? '<strong>' . __( 'Options in this section are disabled because you do not have any navigation menus registered', 'latn-cyrl-bridge' ) . '</strong>'
                    : '',
            ),

            array(
                'title'    => __( 'Extend navigation menu', 'latn-cyrl-bridge' ),
                'desc'     => __( 'Adds a script selector to the navigation menu', 'latn-cyrl-bridge' ),
                'id'       => 'extend',
                'type'     => 'checkbox',
                'disabled' => ! $nav_menus,
                'default'  => 'yes',
            ),

            array(
                'title'    => __( 'Navigation menu to extend', 'latn-cyrl-bridge' ),
                'desc'     => __( 'Select the navigation menu you want to extend', 'latn-cyrl-bridge' ),
                'id'       => 'extend_menu',
                'type'     => 'select',
                'options'  => array_merge(
                    array( '' => __( 'Select a menu', 'latn-cyrl-bridge' ) ),
                    $navigation_menus,
                ),
                'disabled' => ! $nav_menus,
                'default'  => '',
            ),

            array(
                'title'    => __( 'Selector type', 'latn-cyrl-bridge' ),
                'desc'     => __( 'Choose the type of the script selector', 'latn-cyrl-bridge' ),
                'id'       => 'selector_type',
                'type'     => 'select',
                'options'  => array(
                    'submenu' => __( 'Submenu', 'latn-cyrl-bridge' ),
                    'inline'  => __( 'Inline', 'latn-cyrl-bridge' ),
                ),
                'default'  => 'submenu',
                'disabled' => ! $nav_menus,

            ),

            array(
                'title'    => __( 'Menu item title', 'latn-cyrl-bridge' ),
                'desc'     => __( 'Title of the menu item', 'latn-cyrl-bridge' ),
                'id'       => 'menu_title',
                'type'     => 'text',
                'default'  => __( 'Script', 'latn-cyrl-bridge' ),
                'disabled' => ! $nav_menus,

            ),

            array(
                'type' => 'sectionend',
                'id'   => 'lcb_media_settings',
            ),
        ),
        'media'          => array(
            array(
                'title' => _x( 'File and Media settings', 'section name', 'latn-cyrl-bridge' ),
                'type'  => 'title',
                'desc'  => __( 'File and media settings control filename transliteration and media saving', 'latn-cyrl-bridge' ),
                'id'    => 'lcb_media_settings',
            ),

            array(
                'title'   => __( 'Transliterate uploads', 'latn-cyrl-bridge' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Transliterate filenames on upload', 'latn-cyrl-bridge' ),
                'default' => 'yes',
                'id'      => 'transliterate_uploads',
            ),

            array(
                'title'   => __( 'Script specific filenames', 'latn-cyrl-bridge' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Check this box if you want to have separate filenames for each script', 'latn-cyrl-bridge' ),
                'id'      => 'separate_uploads',
                'default' => 'yes',
            ),

            array(
                'title'   => __( 'Filename separator', 'latn-cyrl-bridge' ),
                'type'    => 'text',
                'desc'    => __( 'Separator used for script specific filenames', 'latn-cyrl-bridge' ),
                'id'      => 'filename_separator',
                'class'   => 'small-text',
                'default' => '-',
            ),

            array(
                'title'   => __( 'Transliteration method', 'latn-cyrl-bridge' ),
                'type'    => 'select',
                'desc'    => __( 'Choose if you want to limit the script specific filenames on the entire website, or in content only', 'latn-cyrl-bridge' ),
                'id'      => 'transliteration_method',
                'options' => array(
                    'website' => __( 'Entire website', 'latn-cyrl-bridge' ),
                    'content' => __( 'Content only', 'latn-cyrl-bridge' ),
                ),
                'default' => 'website',
            ),

            array(
                'type' => 'sectionend',
                'id'   => 'lcb_media_settings',
            ),
        ),
        'wpml'           => array(
            array(
                'id'      => 'extend_ls',
                'title'   => __( 'Enable', 'latn-cyrl-bridge' ),
                'type'    => 'checkbox',
                'default' => 'yes',
                'desc'    => __( 'Extend WPML Language Switcher', 'latn-cyrl-bridge' ),
            ),
        ),
        'polylang'       => array(),
        'translatepress' => array(),
        'advanced'       => array(
            array(
                'title' => _x( 'Advanced settings', 'section name', 'latn-cyrl-bridge' ),
                'type'  => 'title',
                'desc'  => __( 'Advanced settings control permalink and search settings', 'latn-cyrl-bridge' ),
                'id'    => 'lcb_advanced_settings',
            ),

            array(
                'title'    => __( 'Fix Permalinks', 'latn-cyrl-bridge' ),
                'desc'     => __( 'Fixes permalinks for cyrillic scripts', 'latn-cyrl-bridge' ),
                'id'       => 'fix_permalinks',
                'type'     => 'checkbox',
                'default'  => 'no',
                'disabled' => $disable_permalinks,
                'tooltip'  => $disable_permalinks
                    ? sprintf(
                        // translators: %s is the current locale.
                        __( 'This option is currently disabled because your current locale is set to %s which will automatically change permalnks', 'latn-cyrl-bridge' ),
                        $wplang
                    )
                    : null,
            ),

            array(
                'title'   => __( 'Fix Search', 'latn-cyrl-bridge' ),
                'desc'    => __( 'Enables searching cyrillic content via latin script', 'latn-cyrl-bridge' ),
                'id'      => 'fix_search',
                'type'    => 'checkbox',
                'default' => 'no',
            ),

            array(
                'title'   => __( 'Fix Ajax', 'latn-cyrl-bridge' ),
                'desc'    => __( 'Transliterates ajax calls', 'latn-cyrl-bridge' ),
                'id'      => 'fix_ajax',
                'type'    => 'checkbox',
                'default' => 'no',
            ),

            array(
                'title'   => __( 'Fix Titles', 'latn-cyrl-bridge' ),
                'desc'    => __( 'Fixes titles for cyrillic scripts', 'latn-cyrl-bridge' ),
                'id'      => 'fix_titles',
                'type'    => 'checkbox',
                'default' => 'no',
            ),

            array(
                'type' => 'sectionend',
                'id'   => 'lcb_advanced_settings',
            ),
        ),
    ),
);
