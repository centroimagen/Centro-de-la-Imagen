<?php

/**
 * Fix post thumbnail missing in single pages for PTB posts
 *
 * @return Yoast\WP\SEO\Values\Open_Graph\Images
 */
function themify_ptb_wpseo_add_opengraph_images( $image ) {
	$ptb = PTB_Public::get_instance();
	if ( $ptb::$is_single ) {
		if ( $featured_image = get_post_thumbnail_id( $ptb->get_actual_query()->queried_object_id ) ) {
			$image->add_image_by_id( $featured_image );
		}
	}

	return $image;
}
add_filter( 'wpseo_add_opengraph_images', 'themify_ptb_wpseo_add_opengraph_images' );