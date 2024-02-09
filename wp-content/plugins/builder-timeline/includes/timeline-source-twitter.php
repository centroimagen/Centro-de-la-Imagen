<?php

class Builder_Timeline_Twitter_Source {

	public function get_id() {
		return 'twitter';
	}

	public function get_name() {
		return __( 'Twitter', 'builder-timeline' );
	}
        
	public function get_items( $args ) {
		$items = array();
		$args = wp_parse_args( $args, [
			'tw_username' => '',
			'tw_limit' => 5,
		] );

		/* safety check for Themify 5.4.0 and lower */
		if ( ! is_file( THEMIFY_DIR . '/class-themify-twitter-api.php' ) ) {
			return $items;
		}

		if ( ! class_exists( 'Themify_Twitter_Api' ) ) {
			require THEMIFY_DIR . '/class-themify-twitter-api.php';
		}
		$twitterConnection = new Themify_Twitter_Api();
		$tweets = $twitterConnection->query( [
			'username' => $args['tw_username'],
			'limit' => (int) $args['tw_limit'],
		] );
		if ( is_wp_error( $tweets ) ) {
			return $items;
		}

		$date_format = get_option( 'date_format' );

		foreach ( $tweets as $tweet ) {
			$url = '';
			if ( ! empty( $tweet->entities->urls[0]->url ) ) {
				$url = $tweet->entities->urls[0]->url;
			} else if ( ! empty( $tweet->entities->media[0]->url ) ) {
				$url = $tweet->entities->media[0]->url;
			}
			$item = array(
				'id' => $tweet->id,
				'title' => '',
				'link' => $url,
				'date' => mysql2date( 'Y-m-d G:i:s', $tweet->created_at ),
				'date_formatted' => date_i18n( $date_format, strtotime( $tweet->created_at ) ),
				'hide_featured_image' => true,
				'image' => null,
				'hide_content' => false,
				'content' => Themify_Twitter_Api::make_clickable( $tweet ),
			);
			$items[] = $item;
		}

		return $items;
	}

	public function get_options() {
		return array(
			array(
			    'type' => 'text',
				'id' => 'tw_username',
				'label' => __( 'Username', 'builder-timeline' ),
			),
			array(
			    'type' => 'number',
				'id' => 'tw_limit',
				'label' => __( 'Limit', 'builder-timeline' ),
			),
		);
	}

}