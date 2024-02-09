<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_Gallery extends Builder_Data_Provider {


	function get_id() {
		return 'gallery';
	}

	function get_label() {
		return __( 'Gallery', 'builder-mosaic' );
	}

	function get_options() {
		return array(
			array(
				'id' => 'shortcode_gallery',
                'type' => 'gallery',
                'label' => __('Gallery Shortcode', 'themify')
			),
			array(
				'id' => 'gallery_link_to',
				'type' => 'select',
				'label' => __('Link to', 'builder-mosaic'),
				'options' => array(
					'post' => __('Attachment Page','builder-mosaic'),
					'file' => __('Media File','builder-mosaic'),
					'none' => __('None','builder-mosaic')
				),
				'default' =>'file'
			),
		);
	}

	function get_items( $settings, $limit, $paged ) {
		if ( empty( $settings['shortcode_gallery'] ) )
			return new WP_Error( 'no_images', __( 'No gallery images has been added.', 'builder-mosaic' ) );
		
		$settings = wp_parse_args( $settings, array(
			'shortcode_gallery' => '',
			'gallery_link_to' => 'file',
		) );

		$images = themify_get_gallery_shortcode( $settings['shortcode_gallery'] );
		$items = array();
		foreach ( $images as $image ) {
			if ( $settings['gallery_link_to'] === 'file' ) {
				$link = wp_get_attachment_image_src( $image->ID, 'full' );
				$link = $link[0];
			} elseif ( $settings['gallery_link_to'] === 'post' ) {
				$link = get_attachment_link( $image->ID );
			} else {
				$link = '';
			}
			$src = wp_get_attachment_image_src( $image->ID, 'large' );

			$items[] = array(
				'title' => $image->post_title,
				'image' => $src[0],
				'text' => '<p>' . $image->post_excerpt . '</p>',
				'link' => $link,
				'link_lightbox' => $settings['gallery_link_to'] === 'file',
				'css_classes' => '',
			);
		}

		$items = array_slice( $items, ( $paged - 1 ) * $limit, $limit );

		return array(
			'items' => $items,
			'total_items' => count( $images ),
		);
	}
}