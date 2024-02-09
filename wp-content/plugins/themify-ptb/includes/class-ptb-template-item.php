<?php

class PTB_Template_Item {

	function get_id() {
		return '';
	}

	function get_title() {
		return '';
	}

	function get_post_type() {
		return null;
	}

	function get_context() {
		return [ 'archive', 'single', 'custom' ];
	}

	/**
	 * Whether this template should be available in PTB Search
	 *
	 * @return bool
	 */
	function available_in_ptb_search() {
		return false;
	}

	function __construct() {
		$id = $this->get_id();
		if ( is_admin() ) {
			/* add item to the list */
			add_filter( 'ptb_template_modules', [ $this, 'register_item' ], 10, 3 );

			add_filter( "ptb_field_{$id}_template", [ $this, 'form_callback' ], 10, 6 );
		} else {
			add_action( 'ptb_custom_' . $id, [ $this, 'display_callback' ], 10, 6 );
			add_action( 'ptb_search_' . $id, [ $this, 'search_frontend' ], 10, 8 );
		}

		if ( $this->available_in_ptb_search() ) {
			if ( method_exists( $this, 'ptb_search_set_values' ) ) {
				add_filter( 'ptb_search_set_values', [ $this, 'ptb_search_set_values' ], 10, 3 );
			}
			add_filter( 'ptb_search_render', [ $this, 'register_item' ], 10, 3 );
			add_filter( 'ptb_search_filter_by_slug', [ $this, 'ptb_search_filter_by_slug' ], 10, 6 );
			add_filter( 'ptb_search_by_' . $id, [ $this, 'ptb_search_by' ], 10, 6 );
		}
	}

	function register_item( array $cmb_options, $context, $post_type ) {
		if ( $this->get_post_type() && ! in_array( $post_type, (array) $this->get_post_type(), true ) ) {
			return $cmb_options;
		}
		if ( in_array( $context, $this->get_context(), true )
			|| ( $context === 'search' && $this->available_in_ptb_search() )
		) {
			$cmb_options[ $this->get_id() ] = [ 'name' => $this->get_title(), 'type' => $this->get_id() ];
		}

		return $cmb_options;
	}

	function display_callback( $args, $settings, $meta_data, $lang, $is_single, $grid_index ) {
		$this->render( $args, $settings, $meta_data, $lang, $is_single, $grid_index );
	}

	function form_callback( $id, $context, $args, $module, $post_support, $languages ) {
		if ( in_array( $context, $this->get_context(), true ) ) {
			$this->form( $id, $context, $args, $module, $post_support, $languages );
		} else if ( $context === 'search' ) {
			$this->search_form( $id, $context, $args, $module, $post_support, $languages );
		}
	}

	/**
	 * Display item template options
	 *
	 * @return void
	 */
	function form( $id, $context, $args, $module, $post_support, $languages ) {}

	/**
	 * Display the item on frontend
	 *
	 * @return void
	 */
	function render( $args, $settings, $meta_data, $lang, $is_single, $grid_index ) {}

	/**
	 * Display the search form input on frontend
	 *
	 * @return void
	 */
	function search_frontend( $post_type, $id, $args, $module, $value, $label, $lang, $languages ) {}
}