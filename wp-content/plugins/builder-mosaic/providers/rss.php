<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_RSS extends Builder_Data_Provider {

	/**
	 * @type float
	 */
	private static $cache_lifetime=0;

	function get_id() {
		return 'rss';
	}

	function get_label() {
		return __( 'RSS Feeds', 'builder-mosaic' );
	}

	function get_options() {
		return array(
			array(
				'id' => 'rss_cache_lifetime',
				'type' => 'number',
				'label' => __( 'Cache Duration', 'builder-mosaic' ),
				'after' => __( 'hours', 'builder-mosaic' ),
				'help' => '<br/>' . __( 'How long the RSS feeds should be cached. Default: 12 hours.', 'builder-mosaic' )
			),
			array(
				'type' => 'builder',
				'id' => 'rss_feeds',
				'options' => array(
					array(
						'id' => 'feed_url',
						'type' => 'text',
						'label' => __('URL', 'builder-mosaic'),
						'new_row' => __( 'Add new RSS feed source', 'builder-mosaic' ),
					),
				),
			),
		);
	}

	function get_items( $settings, $limit, $paged ) {
		$settings = wp_parse_args( $settings, array(
			'rss_feeds' => array(),
			'rss_cache_lifetime' => 12,
		) );
		$feeds = wp_list_pluck( $settings['rss_feeds'], 'feed_url' );
		if ( empty ( $feeds ) ) {
			return new WP_Error( 'feed_empty', __( 'No feed has been specified.', 'builder-mosaic' ) );
		}

		if ( ! empty( $settings['rss_cache_lifetime'] ) ) {
			self::$cache_lifetime = (float) $settings['rss_cache_lifetime'];
			add_filter( 'wp_feed_cache_transient_lifetime', array( $this, 'filter_cache_transient_lifetime' ), 999 );
		}

		$rss = fetch_feed( $feeds );
		if ( is_wp_error( $rss ) ) {
			return $rss;
		}
		if ( ! $rss->get_item_quantity() ) {
			$rss->__destruct();
			return new WP_Error( 'feed_down', __( 'An error has occurred, which probably means the feed is down. Try again later.', 'builder-mosaic' ) );
		}

		$items = array();
		$query = $rss->get_items( ( $paged - 1 ) * $limit, $limit );
		foreach ( $query as $item ) {
			$_item = $this->get_item( $item, $settings );
			if ( $_item ) {
				$items[] = $_item;
			}
		}

		remove_filter( 'wp_feed_cache_transient_lifetime', array( $this, 'filter_cache_transient_lifetime' ), 999 );

		return array(
			'items' => $items,
			'total_items' => $rss->get_item_quantity(),
		);
	}

	function get_item( $item, $settings ) {
		return array(
			'css_classes' => '',
			'image' => $this->get_first_image( $item->get_content() ),
			'link' => $item->get_permalink(),
			'title' => $item->get_title(),
			'text' => strip_tags( $item->get_description(), '<b>,<i>,<a>,<strong>,<em>,<small>,<big>,<abbr>,<acronym>,<cite>,<dfn>,<sub>,<sup>' ),
			'badge' => ( $category = $item->get_category() ) ? $category->get_label() : '',
		);
	}

	/**
	 * Returns the first <img> tag found within $content.
	 *
	 * @return string
	 */
	function get_first_image( $content ) {
		$first_img = '';
		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches );
		if ( isset( $matches [1] [0] ) ) {
			$first_img = $matches [1] [0];
		}

		return $first_img;
	}

	/**
	 * Filters "wp_feed_cache_transient_lifetime" to change how long
	 * RSS feeds should be cached
	 *
	 * @return int
	 */
	function filter_cache_transient_lifetime( $time ) {
		return self::$cache_lifetime * 60 * 60;
	}
}