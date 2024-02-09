<?php
/**
 * Term Cover Image
 */

final class PTB_Term_Images {

	/**
	 * Add options to all taxonomy terms that have archives
	 */
	public static function admin_init() {
		add_filter( 'ptb_get_taxonomy_options', array( __CLASS__, 'form_fields' ), 10, 2 );
	}

	public static function form_fields( $fields, $taxonomy ) {
		if ( PTB::get_option()->has_custom_taxonomy( $taxonomy ) ) {
			$fields = array_merge( $fields, array (
				'term_cover' => array (
					'type' => 'image',
					'id' => 1,
					'deleted' => false,
					'name' => __( 'PTB Term Cover', 'ptb' ),
					'description' => __( 'Image is used inside [ptb_taxonomy] shortcode.', 'ptb' ),
				),
			) );
		}

		return $fields;
	}
}