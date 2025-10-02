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
    'name'     => __( 'Latn–Cyrl Bridge (SR)', 'latncyrl-bridge-sr' ),
    'basename' => STL_PLUGIN_BASENAME,
    'meta'     => array(
        array(
            'link' => 'https://github.com/plusinnovative/latn-cyrl-bridge',
            'text' => __( 'Documentation', 'latncyrl-bridge-sr' ),
        ),
    ),
    'page'     => array(
        'root'       => true,
        'parent'     => 'options-general.php',
        'title'      => __( 'Latinisation', 'latncyrl-bridge-sr' ),
        'menu_title' => __( 'Settings', 'default' ),
        'cap'        => 'manage_options',
        // 'image' removed in this fork (no admin UI), keep entry unused.
        'prio'       => 99,
    ),
    'settings' => array(
        'general'        => array(
            array(
                'title' => _x( 'General settings', 'section name', 'latncyrl-bridge-sr' ),
                'type'  => 'title',
                'desc'  => __( 'General settings control main functionality of the plugin', 'latncyrl-bridge-sr' ),
                'id'    => 'lcb_general_settings',
            ),

            array(
                'title'   => __( 'Default script', 'latncyrl-bridge-sr' ),
                'desc'    => __( 'Default script used for the website if user did not select a script', 'latncyrl-bridge-sr' ),
                'id'      => 'default_script',
                'type'    => 'select',
                'default' => 'cir',
                'options' => stl_get_available_scripts(),
            ),

            array(
                'title'   => __( 'Content source script', 'latncyrl-bridge-sr' ),
                'desc'    => __( 'Script used when authoring content in WordPress (the plugin will transliterate to the other script on demand)', 'latncyrl-bridge-sr' ),
                'id'      => 'content_script',
                'type'    => 'select',
                'default' => 'cir',
                'options' => stl_get_available_scripts(),
            ),

            array(
                'title'   => __( 'URL Parameter', 'latncyrl-bridge-sr' ),
                'id'      => 'url_param',
                'desc'    => __( 'URL parameter used for script selector', 'latncyrl-bridge-sr' ),
                'type'    => 'text',
                'default' => 'pismo',
            ),

            array(
                'type' => 'sectionend',
                'id'   => 'lcb_general_settings',
            ),

            array(
                'title' => _x( 'Menu settings', 'section name', 'latncyrl-bridge-sr' ),
                'type'  => 'title',
                'desc'  => __( 'Options that control the display of the script selector', 'latncyrl-bridge-sr' ),
                'id'    => 'lcb_menu_settings',
            ),

            array(
                'type' => 'sectionend',
                'id'   => 'lcb_menu_settings',
            ),
        ),
        'menu'           => array(
            array(
                'title' => _x( 'Navigation menu settings', 'section name', 'latncyrl-bridge-sr' ),
                'type'  => 'title',
                'desc'  => __( 'Menu settings control extending and tweaking the script selector in theme menus', 'latncyrl-bridge-sr' ),
                'id'    => 'lcb_menu_settings',
            ),
            array(
                'id'   => 'lcb_menu_warning',
                'type' => 'info',
                'text' => ! $nav_menus
                    ? '<strong>' . __( 'Options in this section are disabled because you do not have any navigation menus registered', 'latncyrl-bridge-sr' ) . '</strong>'
                    : '',
            ),

            array(
                'title'    => __( 'Extend navigation menu', 'latncyrl-bridge-sr' ),
                'desc'     => __( 'Adds a script selector to the navigation menu', 'latncyrl-bridge-sr' ),
                'id'       => 'extend',
                'type'     => 'checkbox',
                'disabled' => ! $nav_menus,
                'default'  => 'yes',
            ),

            array(
                'title'    => __( 'Navigation menu to extend', 'latncyrl-bridge-sr' ),
                'desc'     => __( 'Select the navigation menu you want to extend', 'latncyrl-bridge-sr' ),
                'id'       => 'extend_menu',
                'type'     => 'select',
                'options'  => array_merge(
                    array( '' => __( 'Select a menu', 'latncyrl-bridge-sr' ) ),
                    $navigation_menus,
                ),
                'disabled' => ! $nav_menus,
                'default'  => '',
            ),

            array(
                'title'    => __( 'Selector type', 'latncyrl-bridge-sr' ),
                'desc'     => __( 'Choose the type of the script selector', 'latncyrl-bridge-sr' ),
                'id'       => 'selector_type',
                'type'     => 'select',
                'options'  => array(
                    'submenu' => __( 'Submenu', 'latncyrl-bridge-sr' ),
                    'inline'  => __( 'Inline', 'latncyrl-bridge-sr' ),
                ),
                'default'  => 'submenu',
                'disabled' => ! $nav_menus,

            ),

            array(
                'title'    => __( 'Menu item title', 'latncyrl-bridge-sr' ),
                'desc'     => __( 'Title of the menu item', 'latncyrl-bridge-sr' ),
                'id'       => 'menu_title',
                'type'     => 'text',
                'default'  => __( 'Script', 'latncyrl-bridge-sr' ),
                'disabled' => ! $nav_menus,

            ),

            array(
                'type' => 'sectionend',
                'id'   => 'lcb_media_settings',
            ),
        ),
        'media'          => array(
            array(
                'title' => _x( 'File and Media settings', 'section name', 'latncyrl-bridge-sr' ),
                'type'  => 'title',
                'desc'  => __( 'File and media settings control filename transliteration and media saving', 'latncyrl-bridge-sr' ),
                'id'    => 'lcb_media_settings',
            ),

            array(
                'title'   => __( 'Transliterate uploads', 'latncyrl-bridge-sr' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Transliterate filenames on upload', 'latncyrl-bridge-sr' ),
                'default' => 'yes',
                'id'      => 'transliterate_uploads',
            ),

            array(
                'title'   => __( 'Script specific filenames', 'latncyrl-bridge-sr' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Check this box if you want to have separate filenames for each script', 'latncyrl-bridge-sr' ),
                'id'      => 'separate_uploads',
                'default' => 'yes',
            ),

            array(
                'title'   => __( 'Filename separator', 'latncyrl-bridge-sr' ),
                'type'    => 'text',
                'desc'    => __( 'Separator used for script specific filenames', 'latncyrl-bridge-sr' ),
                'id'      => 'filename_separator',
                'class'   => 'small-text',
                'default' => '-',
            ),

            array(
                'title'   => __( 'Transliteration method', 'latncyrl-bridge-sr' ),
                'type'    => 'select',
                'desc'    => __( 'Choose if you want to limit the script specific filenames on the entire website, or in content only', 'latncyrl-bridge-sr' ),
                'id'      => 'transliteration_method',
                'options' => array(
                    'website' => __( 'Entire website', 'latncyrl-bridge-sr' ),
                    'content' => __( 'Content only', 'latncyrl-bridge-sr' ),
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
                'title'   => __( 'Enable', 'latncyrl-bridge-sr' ),
                'type'    => 'checkbox',
                'default' => 'yes',
                'desc'    => __( 'Extend WPML Language Switcher', 'latncyrl-bridge-sr' ),
            ),
        ),
        'polylang'       => array(),
        'translatepress' => array(),
        'advanced'       => array(
            array(
                'title' => _x( 'Advanced settings', 'section name', 'latncyrl-bridge-sr' ),
                'type'  => 'title',
                'desc'  => __( 'Advanced settings control permalink and search settings', 'latncyrl-bridge-sr' ),
                'id'    => 'lcb_advanced_settings',
            ),

            array(
                'title'    => __( 'Fix Permalinks', 'latncyrl-bridge-sr' ),
                'desc'     => __( 'Fixes permalinks for cyrillic scripts', 'latncyrl-bridge-sr' ),
                'id'       => 'fix_permalinks',
                'type'     => 'checkbox',
                'default'  => 'no',
                'disabled' => $disable_permalinks,
                'tooltip'  => $disable_permalinks
                    ? sprintf(
                        // translators: %s is the current locale.
                        __( 'This option is currently disabled because your current locale is set to %s which will automatically change permalnks', 'latncyrl-bridge-sr' ),
                        $wplang
                    )
                    : null,
            ),

            array(
                'title'   => __( 'Fix Search', 'latncyrl-bridge-sr' ),
                'desc'    => __( 'Enables searching cyrillic content via latin script', 'latncyrl-bridge-sr' ),
                'id'      => 'fix_search',
                'type'    => 'checkbox',
                'default' => 'no',
            ),

            array(
                'title'   => __( 'Fix Ajax', 'latncyrl-bridge-sr' ),
                'desc'    => __( 'Transliterates ajax calls', 'latncyrl-bridge-sr' ),
                'id'      => 'fix_ajax',
                'type'    => 'checkbox',
                'default' => 'no',
            ),

            array(
                'title'   => __( 'Fix Titles', 'latncyrl-bridge-sr' ),
                'desc'    => __( 'Fixes titles for cyrillic scripts', 'latncyrl-bridge-sr' ),
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
