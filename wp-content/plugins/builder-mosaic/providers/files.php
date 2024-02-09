<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_Files extends Builder_Data_Provider {

	function get_id() {
		return 'files';
	}

	function get_label() {
		return __( 'Directory List', 'builder-mosaic' );
	}

	function get_options() {
		return array(
			array(
				'id' => 'dir_path',
				'type' => 'text',
				'label' => __( 'Folder Path', 'builder-mosaic' ),
				'class' => 'large',
				'description' => trailingslashit( ABSPATH ),
			),
			array(
				'id' => 'levels',
				'type' => 'number',
				'label' => __( 'Levels', 'builder-mosaic' ),
				'help' => __( ' Levels of folders to follow (default = 1)', 'builder-mosaic' )
			),
			array(
				'id' => 'extensions',
				'type' => 'text',
				'label' => __( 'File Extensions', 'builder-mosaic' ),
				'class' => 'large',
				'after' => __( 'Comma-separated list of file extensions to look for (leave empty to list all).', 'builder-mosaic' )
			),
			array(
				'id' => 'files_orderby',
				'type' => 'select',
				'label' => __( 'Order By', 'builder-mosaic' ),
				'options' => array(
					'name' => __( 'File Name', 'builder-mosaic' ),
					'size' => __( 'File Size', 'builder-mosaic' ),
					'date' => __( 'File Modified Time', 'builder-mosaic' ),
				),
			),
			array(
				'id' => 'files_order',
				'type' => 'select',
				'label' => __( 'Order', 'builder-mosaic' ),
				'order' =>true
			),
		);
	}

	function get_items( $settings, $limit, $paged ) {
		include_once ABSPATH . 'wp-admin/includes/media.php';
		include_once ABSPATH . 'wp-admin/includes/file.php';

		$settings = wp_parse_args( $settings, array(
			'dir_path' => '',
			'levels' => 1,
			'exclusions' => '',
			'extensions' => '',
			'files_orderby' => 'name',
			'files_order' => 'ASC',
		) );
		extract( $settings );

		$dir_path = trailingslashit( ABSPATH ) . $dir_path;

		$exclusions = explode( ',', $exclusions );
		$files = list_files( $dir_path, $levels, $exclusions );

		if ( ! empty( $extensions ) ) {
			$extensions = str_replace( ',', '|', $extensions );
			foreach( $files as $key => $file ) {
				if ( ! preg_match( '/(' . $extensions . ')$/', $file ) ) {
					unset( $files[ $key ] );
				}
			}
		}

		foreach ( $files as $index => $file ) {
			$files[ $index ] = array(
				'path' => $file,
				'name' => ucfirst( basename( $file ) ), /*@note: wp_list_sort() sorts Uppercase words first, ucfirst() is used on "name" to normalize this behavior */
				'size' => filesize( $file ),
				'date' => filemtime( $file ),
			);
		}
		$files = wp_list_sort( $files, $files_orderby, $files_order );

		$items = array();
		foreach ( $files as $file ) {
			$info = pathinfo( $file['path'] );
			$info['extension'] = ! isset( $info['extension'] ) ? '' : strtolower( $info['extension'] );
			$filesize = size_format( $file['size'], 2 );
			$url = str_replace( trailingslashit( ABSPATH ), trailingslashit( home_url() ), $file['path'] );
			$link_lightbox = false;
			$text = sprintf( __( 'Size: %s', 'builder-mosaic' ), $filesize );
			$title = $info['filename'];
			$audio = '';
			$image = '';

			if ( in_array( $info['extension'], array( 'png', 'jpeg', 'jpg', 'webp', 'bmp', 'gif', 'bmp' ) ) ) {
				$image = $url; // for image files, use the image itself as thumbnail
				$link_lightbox = true;
			} 
			elseif ( $info['extension']==='mp4' || $info['extension']==='flv') {
				$meta_data = wp_read_video_metadata( $file['path'] );
				if ( isset( $meta_data['length_formatted'] ) ) {
					$text .= ' - ' . sprintf( __( 'Length: %s', 'builder-mosaic' ), $meta_data['length_formatted'] );
				}
			}
			elseif ( $info['extension']==='mp3' || $info['extension']==='ogg' || $info['extension']==='wav') {
				$audio = $url;
				$url = '';
				$meta_data = wp_read_audio_metadata( $file['path'] );
				if ( isset( $meta_data['image']['data'] ) ) {
					$image = 'data:image/jpeg;charset=utf-8;base64,' . base64_encode( $meta_data['image']['data'] );
				}
				if ( isset( $meta_data['title'] ) ) {
					$title = $meta_data['title'];
				}
				$text = '';
				if ( isset( $meta_data['artist'] ) ) {
					$text .= sprintf( __( 'Artist: %s', 'builder-mosaic' ), $meta_data['artist'] );
				}
				if ( isset( $meta_data['album'] ) ) {
					$text .= ' - ' . sprintf( __( 'Album: %s', 'builder-mosaic' ), $meta_data['album'] );
				}
				if ( isset( $meta_data['year'] ) ) {
					$text .= ' (' . $meta_data['year'] . ')';
				}
			} 
			elseif ( is_dir( $file['path'] ) ) {
				$image = Builder_Mosaic::$url . 'assets/images/folder.svg';
			}

			$items[] = array(
				'title' => $title,
				'image' => $image,
				'text' => $text,
				'link' => $url,
				'link_lightbox' => $link_lightbox,
				'css_classes' => '',
				'badge' => strtoupper( $info['extension'] ),
				'audio' => $audio,
			);
		}

		$items = array_slice( $items, ( $paged - 1 ) * $limit, $limit );
		return array(
			'items' => $items,
			'total_items' => count( $files ),
		);
	}
}