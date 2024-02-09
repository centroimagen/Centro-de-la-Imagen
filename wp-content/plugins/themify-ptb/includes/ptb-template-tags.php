<?php
/**
 * PTB pluggable template tags
 *
 * @package PTB
 */

if ( ! function_exists( 'ptb_get_the_term_list' ) ) :
/**
 * Retrieves a post's terms as a list with specified format.
 *
 * Terms are linked to their respective term listing pages.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy name.
 * @param string $before   Optional. String to use before the terms. Default empty.
 * @param string $sep      Optional. String to use between the terms. Default empty.
 * @param string $after    Optional. String to use after the terms. Default empty.
 * @param string $link     Make the terms list linkable, on by default.
 * @return string|false|WP_Error A list of terms on success, false if there are no terms,
 *                               WP_Error on failure.
 */
function ptb_get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '', $link = true ) {
	$terms = get_the_terms( $post_id, $taxonomy );

	if ( is_wp_error( $terms ) ) {
			return $terms;
	}

	if ( empty( $terms ) ) {
			return false;
	}

	$links = array();

	foreach ( $terms as $term ) {
		if ( $link ) {
			$link = get_term_link( $term, $taxonomy );
			if ( is_wp_error( $link ) ) {
					return $link;
			}
			$links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . $term->name . '</a>';
		} else {
			$links[] = $term->name;
		}
	}

	/** this filter is defined in wp-includes/category-template.php */
	$term_links = apply_filters( "term_links-{$taxonomy}", $links );  // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

	return $before . implode( $sep, $term_links ) . $after;
}
endif;