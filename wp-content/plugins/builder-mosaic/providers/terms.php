<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_Terms extends Builder_Data_Provider {

	function get_id() {
		return 'terms';
	}

	function get_label() {
		return __( 'Taxonomy Terms', 'builder-mosaic' );
	}

	function get_options() {
		return array(
			array(
				'id' => 'terms_tax',
				'type' => 'select',
				'dataset' => 'taxonomy',
				'label' => __( 'Taxonomy', 'tbp' )
			),
			array(
				'id' => 'terms_order',
				'type' => 'select',
				'label' => __( 'Order', 'builder-mosaic' ),
				'help' => __( 'Descending = show newer posts first', 'builder-mosaic' ),
				'order' => true
			),
			array(
				'id' => 'terms_orderby',
				'type' => 'select',
				'label' => __( 'Order By', 'builder-mosaic' ),
				'options' => array(
					'name' => __( 'Name', 'builder-mosaic' ),
					'term_id' => __( 'Term ID', 'builder-mosaic' ),
					'parent' => __( 'Parent', 'builder-mosaic' ),
					'count' => __( 'Post Count', 'builder-mosaic' ),
				)
			),
			array(
				'id' => 'terms_parent',
				'type' => 'text',
				'label' => __( 'Parent', 'tbp' ),
				'help' => __( 'Parent term ID to retrieve direct-child terms of.', 'builder-mosaic' ),
				'wrap_class' => 'tb_disable_dc',
			),
		);
	}

	function get_items( $settings, $limit, $paged ) {

		$settings = wp_parse_args( $settings, [
			'terms_tax' => 'category',
			'terms_order' => 'DESC',
			'terms_orderby' => 'name',
			'terms_parent' => '',
		] );

		$items = [];
		$args = [
			'taxonomy' => $settings['terms_tax'],
			'order' => $settings['terms_order'],
			'orderby' => $settings['terms_orderby'],
			'number' => $limit,
			'parent' => $settings['terms_parent'],
		];

		$terms = get_terms( $args );
		foreach ( $terms as $term ) {
			$image = get_term_meta( $term->term_id, 'tbp_cover', true );

			/* fallback image for Product Category taxonomy from WooCommerce */
			if ( empty( $image ) && $args['taxonomy'] === 'product_cat' ) {
				$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true ); 
				$image = wp_get_attachment_url( $thumbnail_id );
			}

			$items[] = array(
				'title' => $term->name,
				'image' => $image,
				'text' => empty( $term->description ) ? '' : apply_filters( 'themify_builder_module_content', $term->description ),
				'link' => get_term_link( $term->term_id ),
				'css_classes' => '',
				'link_lightbox' => false,
				'badge' => $term->count,
			);
		}

		return array(
			'items' => $items,
			'total_items' => count( $items ),
		);
	}
}