<?php
/**
 * Widget API: PTB_Widget_Taxonomies class
 *
 * @package PTB
 * @subpackage PTB/includes
 * @since 1.2.8
 */

class PTB_Widget_Taxonomies extends WP_Widget {

    /**
     * Sets up a new PTB Recent Posts widget instance.
     *
     * @access public
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'ptb_taxonomies',
            'description' => __('Displays list of terms in a taxonomy.','ptb')
        );
        parent::__construct('ptb-taxonomies', __('PTB Taxonomy Terms','ptb'), $widget_ops);
    }

    /**
     * Outputs the content for the current PTB Recent Posts widget instance.
     *
     * @access public
     *
     * @param array $args     Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current PTB Recent Posts widget instance.
     */
    public function widget( $args, $instance ) {
        
		$output = PTB_Public::get_instance()->display_taxonomy_hierarchy( $instance );
		if ( $output ) {
			echo $args['before_widget'];
			$title = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
			$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			echo $output, $args['after_widget'];
		}
    }
    
    /**
     * Handles updating the settings for the widget.
     *
     * @access public
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update( $new_instance, $old_instance ) {
		$instance = array();
		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
		}
		if ( ! empty( $new_instance['tax'] ) ) {
			$instance['tax'] = sanitize_text_field( $new_instance['tax'] );
		}
		if ( ! empty( $new_instance['children_depth'] ) ) {
			$instance['children_depth'] = (int) $new_instance['children_depth'];
		}

		return $instance;
	}

    /**
     * Outputs the settings form for widget.
     *
     * @since 1.2.8
     * @access public
     *
     * @param array $instance Current settings.
     */
    public function form( $instance ) {
		$instance = is_array( $instance ) ? $instance : [];
		$options = PTB::get_option();
		$taxonomies = $options->get_custom_taxonomies();
		$instance = wp_parse_args( $instance, [
			'tax' => '',
			'children_depth' => 2,
		] );
        ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'ptb' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo ! empty($instance['title']) ? esc_attr($instance['title']):''; ?>" />
		</p>
		<div class="ptb_widget_wrapper">
			<div class="ptb_cmb_item_body">

				<div class="ptb_cmb_input_row">
					<label class="ptb_cmb_input_label" for="<?php echo $this->get_field_id( 'tax' ); ?>"><?php _e('Taxonomy', 'ptb'); ?>:</label>
					<div class="ptb_cmb_input">
						<select class="" id="<?php echo $this->get_field_id( 'tax' ); ?>" name="<?php echo $this->get_field_name( 'tax' ); ?>">
							<?php foreach ( $taxonomies as $tax ) : ?>
								<option value="<?php echo esc_attr( $tax->id ); ?>" <?php selected( $tax->id, $instance['tax'] ); ?>><?php echo PTB_Utils::get_label( $tax->plural_label, $tax->id ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="ptb_cmb_input_row">
					<label class="ptb_cmb_input_label" for="<?php echo $this->get_field_id( 'children_depth' ); ?>"><?php _e('Levels', 'ptb'); ?>:</label>
					<div class="ptb_cmb_input">
						<select class="" id="<?php echo $this->get_field_id( 'children_depth' ); ?>" name="<?php echo $this->get_field_name( 'children_depth' ); ?>">
							<option value="1" <?php selected( 1, (int) $instance['children_depth'] ); ?>>1</option>
							<option value="2" <?php selected( 2, (int) $instance['children_depth'] ); ?>>2</option>
							<option value="3" <?php selected( 3, (int) $instance['children_depth'] ); ?>>3</option>
							<option value="4" <?php selected( 4, (int) $instance['children_depth'] ); ?>>4</option>
							<option value="5" <?php selected( 5, (int) $instance['children_depth'] ); ?>>5</option>
							<option value="6" <?php selected( 6, (int) $instance['children_depth'] ); ?>>6</option>
							<option value="7" <?php selected( 7, (int) $instance['children_depth'] ); ?>>7</option>
							<option value="8" <?php selected( 8, (int) $instance['children_depth'] ); ?>>8</option>
							<option value="9" <?php selected( 9, (int) $instance['children_depth'] ); ?>>9</option>
							<option value="10" <?php selected( 10, (int) $instance['children_depth'] ); ?>>10</option>
						</select>
					</div>
				</div>

			</div>
		</div>
		<?php
	}
}