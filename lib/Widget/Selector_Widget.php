<?php //phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag
/**
 * Selector_Widget class file.
 *
 * @package SrbTransLatin
 */

namespace Oblak\STL\Widget;

/**
 * Enables script selection across the website
 *
 * @since 2.0.0
 */
class Selector_Widget extends \WP_Widget {
    /**
     * {@inheritDoc}
     */
    public function __construct() {
        parent::__construct(
            'plusinn_lcb_widget',
            __( 'Serbian Script selector', 'latncyrl-bridge-sr' ),
            array(
                'description' => __( 'Serbian Script selection widget', 'latncyrl-bridge-sr' ),
            )
        );

        $this->defaults = array(
            'title'         => __( 'Script Selection', 'latncyrl-bridge-sr' ),
            'selector_type' => 'oneline',
            'separator'     => '&nbsp;|&nbsp;',
            'cir_caption'   => __( 'Ћирилица', 'latncyrl-bridge-sr' ),
            'lat_caption'   => __( 'Latinica', 'latncyrl-bridge-sr' ),
            'inactive_only' => false,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, $this->defaults );

        printf(
            '<p>
                <label for="%s">%s</label>
                <input id="%s" name="%s" value="%s" type="text" class="widefat">
            </p>',
            esc_attr( $this->get_field_id( 'title' ) ),
            esc_html__( 'Title', 'latncyrl-bridge-sr' ),
            esc_attr( esc_attr( $this->get_field_id( 'title' ) ) ),
            esc_attr( $this->get_field_name( 'title' ) ),
            esc_attr( $instance['title'] ),
        );

        printf(
            '<h4>%s</h4>',
            esc_html__( 'Link Options', 'latncyrl-bridge-sr' )
        );

        printf(
            '<p>
                <label for="%s">%s</label>
                <input id="%s" name="%s" value="%s" type="text" class="widefat">
            </p>',
            esc_attr( $this->get_field_id( 'cir_caption' ) ),
            esc_html__( 'Link text - Cyrillic', 'latncyrl-bridge-sr' ),
            esc_attr( $this->get_field_id( 'cir_caption' ) ),
            esc_attr( $this->get_field_name( 'cir_caption' ) ),
            esc_attr( $instance['cir_caption'] )
        );

        printf(
            '<p>
                <label for="%s">%s</label>
                <input id="%s" name="%s" value="%s" type="text" class="widefat">
            </p>',
            esc_attr( $this->get_field_id( 'lat_caption' ) ),
            esc_html__( 'Link text - Latin', 'latncyrl-bridge-sr' ),
            esc_attr( $this->get_field_id( 'lat_caption' ) ),
            esc_attr( $this->get_field_name( 'lat_caption' ) ),
            esc_attr( $instance['lat_caption'] )
        );

        printf(
            '<h4>%s</h4>',
            esc_html__( 'Display Options', 'latncyrl-bridge-sr' )
        );

        printf(
            '<p>
                <label for="%s">%s</label>
                <select id="%s" name="%s" class="widefat">
                    %s
                </select>
            </p>',
            esc_attr( $this->get_field_id( 'selector_type' ) ),
            esc_html__( 'Selector style', 'latncyrl-bridge-sr' ),
            esc_attr( $this->get_field_id( 'selector_type' ) ),
            esc_attr( $this->get_field_name( 'selector_type' ) ),
            wp_kses(
                $this->selector_select( $instance ),
                array(
					'option' => array(
						'value'    => array(),
						'selected' => array( 'selected' ),
					),
                )
            )
        );

        printf(
            '<p>
                <label for="%s">%s</label>
                <input id="%s" name="%s" value="%s" type="text" class="widefat">
            </p>',
            esc_attr( $this->get_field_id( 'separator' ) ),
            esc_html__( 'Separator (oneline)', 'latncyrl-bridge-sr' ),
            esc_attr( $this->get_field_id( 'separator' ) ),
            esc_attr( $this->get_field_name( 'separator' ) ),
            esc_attr( $instance['separator'] )
        );
    }

    /**
     * Outputs the script selector options
     *
     * @param  array $instance Widget instance.
     * @return string          HTML for the selector options
     */
    private function selector_select( $instance ) {
        $selectors = array(
            'oneline' => __( 'One Line', 'latncyrl-bridge-sr' ),
            'list'    => __( 'Dropdown', 'latncyrl-bridge-sr' ),
            'links'   => __( 'List', 'latncyrl-bridge-sr' ),
        );

        $html = '';

        foreach ( $selectors as $value => $title ) {
            $html .= sprintf(
                '<option %s value="%s">%s</option>',
                selected( $instance['selector_type'], $value, false ),
                $value,
                $title
            );
        }

        return $html;
    }

    /**
     * {@inheritDoc}
     */
    public function update( $new_instance, $old_instance ) {
        $instance                  = $old_instance;
        $instance['title']         = wp_strip_all_tags( $new_instance['title'] );
        $instance['cir_caption']   = wp_strip_all_tags( $new_instance['cir_caption'] );
        $instance['lat_caption']   = wp_strip_all_tags( $new_instance['lat_caption'] );
        $instance['separator']     = wp_strip_all_tags( $new_instance['separator'] );
        $instance['selector_type'] = $new_instance['selector_type'];

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function widget( $args, $instance ) {
        $instance = wp_parse_args( (array) $instance, $this->defaults );

        echo wp_kses_post( $args['before_widget'] );

        $title = apply_filters( 'widget_title', $instance['title'] );

        if ( ! empty( $title ) ) {
            printf(
                '%s%s%s',
                wp_kses_post( $args['before_title'] ),
                esc_html( $title ),
                wp_kses_post( $args['after_title'] )
            );
        }

        stl_script_selector( $instance );

        echo wp_kses_post( $args['after_widget'] );
    }
}
