<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_NextGen_Gallery extends Builder_Data_Provider {

	function is_available() {
		return class_exists( 'C_NextGEN_Bootstrap' );
	}
	function get_error(){
	    return __( 'Please install NextGEN Gallery plugin.', 'builder-mosaic' );
	}
	function get_id() {
		return 'nextgen';
	}

	function get_label() {
		return __( 'NextGEN Gallery', 'builder-mosaic' );
	}

	function get_options() {
		if ( ! $this->is_available() ) {
			return array(
				array(
					'type' => 'separator',
					'label' => $this->get_error()
				)
			);
		}

		return array(
			array(
				'id' => 'nextgen_gallery',
				'type' => 'select',
				'label' => __('Gallery', 'builder-mosaic'),
				'options' => $this->get_galleries(),
			),
		);
	}

	/**
	 * Gets a list of galleries from NGG
	 *
	 * @return array
	 */
	function get_galleries() {
		$galleries = C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper')->find_all();
		if ( ! empty( $galleries ) ) {
			return wp_list_pluck( $galleries, 'title', 'gid' );
		}
	}

	function get_items( $settings, $limit, $paged ) {
		global $nggdb;

		if ( empty( $settings['nextgen_gallery'] ) ) {
			return new WP_Error( 'ngg_empty', __( 'No Gallery has been specified.', 'builder-mosaic' ) );
		}

		$items = array();
		$gallery = $nggdb->get_gallery( $settings['nextgen_gallery'], 'sortorder', 'DESC', true, $limit, ( $paged - 1 ) * $limit );
		if ( ! empty( $gallery ) ) {
			foreach( $gallery as $image ) {
				$items[] = array(
					'title' => $image->alttext,
					'image' => $image->imageURL,
					'text' => '<p>' . $image->description . '</p>',
					'link' => $image->imageURL,
					'css_classes' => '',
				);
			}
		}

		return array(
			'items' => $items,
			'total_items' => count( $nggdb->get_gallery( $settings['nextgen_gallery'] ) ),
		);
	}
}