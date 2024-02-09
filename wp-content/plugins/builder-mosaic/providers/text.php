<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_Text extends Builder_Data_Provider {

	function get_id() {
		return 'text';
	}

	function get_label() {
		return __( 'Text', 'builder-mosaic' );
	}

	function get_options() {
		return array(
			array(
				'type' => 'builder',
				'id' => 'static_items',
				'options' => array(
					array(
						'id' => 'title',
						'type' => 'text',
						'label' => __('Title', 'builder-mosaic'),
						'control'=>array(
							'selector'=>'.tbm_title'
						)
					),
					array(
						'id' => 'image',
						'type' => 'image',
						'label' => __('Image', 'builder-mosaic'),
					),
					array(
						'id' => 'text',
						'type' => 'textarea',
						'label' => __('Text', 'builder-mosaic'),
						'control'=>array(
							'selector'=>'.tbm_caption'
						)
					),
					array(
						'id' => 'link',
						'type' => 'text',
						'label' => __('Link', 'builder-mosaic'),
					),
					array(
						'id' => 'audio',
						'type' => 'audio',
						'label' => __('Audio File', 'builder-audio'),
						'help' => __('Optional audio file (eg. mp3) to be displayed inside the tile.', 'builder-audio'),
					),
				),
			),
		);
	}

	function get_items( $settings, $limit, $paged ) {
		$settings = wp_parse_args( $settings, array(
			'static_items' => array(),
		) );

		$items = array();
		foreach ( $settings['static_items'] as $item ) {
			$item = wp_parse_args( $item, array(
				'css_classes' => '',
				'image' => '',
				'link' => '',
				'title' => '',
				'text' => '',
				'audio' => '',
			) );
			$item['text'] = '<p>' . $item['text'] . '</p>';
			$items[] = $item;
		}
		$total = count( $items );

		$items = array_slice( $items, ( $paged - 1 ) * $limit, $limit );

		return array(
			'items' => $items,
			'total_items' => $total,
		);
	}
}