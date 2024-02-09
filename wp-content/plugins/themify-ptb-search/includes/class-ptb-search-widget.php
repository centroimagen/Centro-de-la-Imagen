<?php

class PTB_Search_Widget Extends WP_Widget {
	function __construct() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'ptb-search', 'description' => __('Themify PTB Search', 'ptb-search') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'ptb-search' );

		/* Create the widget. */
		parent::__construct( 'ptb-search', __('PTB Search', 'ptb-search'), $widget_ops, $control_ops );

	}

	///////////////////////////////////////////
	// Widget
	///////////////////////////////////////////
	function widget( $args, $instance ) {

		$ptb_options = PTB::get_option();
		if ( ! isset( $ptb_options->option_post_type_templates[ $instance['form'] ]['search'] ) ) {
			return;
		}
		$template = $ptb_options->option_post_type_templates[ $instance['form'] ];
		if ( empty( $template['search']['layout'] ) ) {
			return;
		}

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		/* Before widget (defined by themes). */
		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] , $title , $args['after_title'];
		}

		echo do_shortcode( sprintf( '[ptb_search form="%s"]', $instance['form'] ) );

		/* After widget (defined by themes). */
		echo $args['after_widget'];
	}
	
	
	///////////////////////////////////////////
	// Update
	///////////////////////////////////////////
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['form'] = $new_instance['form'];

		return $instance;
	}
	
	///////////////////////////////////////////
	// Form
	///////////////////////////////////////////
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => '',
			'form' => '',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$forms = $this->get_forms();
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'ptb-search'); ?></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php  echo esc_attr( $instance['title'] ); ?>" class="widefat" type="text" />
		<p>
			<label for="<?php echo $this->get_field_id( 'form' ); ?>"><?php _e( 'Form', 'ptb-search' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'form' ); ?>" name="<?php  echo esc_attr( $this->get_field_name( 'form' ) ); ?>">
				<?php
				foreach( $forms as $search_form ) {
					echo '<option value="' . $search_form['slug'] . '"' . selected( $instance['form'], $search_form['slug'], false ) . '>'
						. esc_html( $search_form['title'] )
					. '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}

	public function get_forms() {
		$forms = array();
		$ptb_options = PTB::get_option();
		if ( isset( $ptb_options->option_post_type_templates ) && ! empty( $ptb_options->option_post_type_templates ) ) {
			foreach ( $ptb_options->option_post_type_templates as $id => $t ) {
				if ( isset( $t['search'] ) && isset( $t['post_type'] ) ) {
					$id = sanitize_key( $id );
					$forms[] = array(
						'ID' => $id,
						'title' => $t['title'],
						'post_type' => $t['post_type'],
						'slug' => $id,
					);
				}
			}
		}
		return $forms;
	}

	public static function register() {
		register_widget( __CLASS__ );
	}
}