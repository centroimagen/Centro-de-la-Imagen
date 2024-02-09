<?php
/**
 * Publicly exposed API to interact with PTB
 *
 * @package PTB
 */

/**
 * Check whether the $post_type is registered by PTB plugin
 *
 * @return bool
 */
function is_ptb_post_type( $post_type ) {
	return PTB::get_option()->has_custom_post_type( $post_type );
}

/**
 * Returns options for a $field_name
 *
 * @return array|false
 */
function ptb_get_field_definition( $field_name, $post_type = null ) {
	if ( null === $post_type ) {
		$post_type = get_post_type();
	}
	$options = PTB::get_option();
	$cmb_options = $post_support = $post_taxonomies = array();
	$options->get_post_type_data( $post_type, $cmb_options, $post_support, $post_taxonomies );
	if ( isset( $cmb_options[ $field_name ] ) ) {
		$cmb_options[ $field_name ]['key'] = 'ptb_' . $field_name;
		return $cmb_options[ $field_name ];
	}

	return false;
}

/**
 * Gets the value of a PTB custom field. Must be used in a WP loop.
 *
 * @param string $field_name The Meta Key to retrieve.
 * @param array  $attr       Array of display settings, used in the template.
 *
 * @return mixed
 */
function ptb_get_field( $field_name, $attr = [], $context = null ) {
	$field_def = ptb_get_field_definition( $field_name );
	if ( ! $field_def ) {
		return;
	}
	$lang = PTB_Utils::get_current_language_code();
	$is_single = true;

	/* ID of zero could indicate PTB rendering the frontend, check for ptb_get_actual_query() */
	$post_id = ! empty( get_the_ID() ) ? get_the_ID() : ( ! empty( ptb_get_actual_query()->post ) ? ptb_get_actual_query()->post->ID : 0 );
	if ( $post_id ) {
		/* the meta value that is passed to ptb_template_public has ALL the post metas, taxonomies and the $post object */
		$meta_data = array_merge( array(), get_post_custom( $post_id ), get_post( $post_id, ARRAY_A ) );
		$meta_data['post_url'] = get_permalink();
		$meta_data['taxonomies'] = ! empty( $post_taxonomies ) ? wp_get_post_terms( $post_id, array_values( $post_taxonomies ) ) : array();
	}
	if ( ! empty( $meta_data[ $field_def['key'] ] ) ) {
		$meta_data[ $field_def['key'] ] = maybe_unserialize( current( $meta_data[ $field_def['key'] ] ) );
	}

	ob_start();
	$output = apply_filters( 'ptb_template_public' . $field_def['type'], false, $field_def, $attr, $meta_data, $lang, $is_single, false );
	return ob_get_clean();
}

/**
 * Displays a PTB field output, uses ptb_get_field
 *
 * @param string $field_name The Meta Key to retrieve.
 * @param array  $attr       Array of display settings, used in the template.
 *
 * @return void
 */
function ptb_the_field( $field_name, $attr = [] ) {
	echo ptb_get_field( $field_name, $attr );
}

/**
 * Shorthand to PTB_Public->get_actual_query()
 *
 * @return WP_Query
 */
function ptb_get_actual_query() {
	return PTB_Public::get_instance()->get_actual_query();
}