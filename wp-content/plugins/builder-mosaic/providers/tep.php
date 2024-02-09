<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_Event_Posts extends Builder_Data_Provider {

	function is_available() {
		return class_exists( 'Themify_Event_Post' );
	}

	function get_id() {
		return 'tep';
	}

	function get_label() {
		return __( 'Themify Event Posts', 'builder-mosaic' );
	}
	function get_error(){
	    return sprintf( __( 'Please install <a href="%s">Themify Event Posts</a> plugin.', 'builder-mosaic' ), 'https://wordpress.org/plugins/themify-event-post/' );
	}
	function get_options() {
		if ( ! $this->is_available() ) {
			return array(
				array(
					'type' => 'separator',
					'html' => '<p>'.$this->get_error().'</p>'
				)
			);
		}

		return array(
			array(
				'id' => 'tep_show',
				'type' => 'select',
				'label' => __( 'Show', 'builder-mosaic' ),
				'options' => array(
					'mix' => __( 'All Events', 'builder-mosaic' ),
					'upcoming' => __( 'Upcoming Events', 'builder-mosaic' ),
					'past' => __( 'Past Events', 'builder-mosaic' ),
				)
			),
			array(
				'term_id' => 'tep_category',
				'type' => 'query_posts',
				'taxonomy' => 'event-category',
			),
			array(
				'id' => 'tep_offset',
				'type' => 'number',
				'label' => __( 'Offset', 'builder-mosaic' ),
				'help' => __( 'number of post to displace or pass over', 'builder-mosaic' )
			),
			array(
				'id' => 'tep_order',
				'type' => 'select',
				'label' => __( 'Order', 'builder-mosaic' ),
				'help' => __( 'Descending = show newer posts first', 'builder-mosaic' ),
				'order' =>true
			),
			array(
				'id' => 'tep_orderby',
				'type' => 'select',
				'label' => __( 'Order By', 'builder-mosaic' ),
				'options' => array(
					'event_date' => __( 'Event Date', 'builder-mosaic' ),
					'date' => __( 'Published Date', 'builder-mosaic' ),
					'id' => __( 'ID', 'builder-mosaic' ),
					'author' => __( 'Author', 'builder-mosaic' ),
					'title' => __( 'Title', 'builder-mosaic' ),
					'name' => __( 'Name', 'builder-mosaic' ),
					'modified' => __( 'Modified', 'builder-mosaic' ),
					'rand' => __( 'Random', 'builder-mosaic' ),
					'comment_count' => __( 'Comment Count', 'builder-mosaic' )
				)
			),
		);
	}

	function get_items( $settings, $limit, $paged ) {
		global $post;

		$settings = wp_parse_args( $settings, array(
			'tep_show' => 'mix',
			'tep_category' => 0,
			'tep_offset' => 0,
			'tep_order' => 'desc',
			'tep_orderby' => 'event_date',
		));

		if ( ! function_exists( 'themify_event_post_parse_query' ) ) {
			return new WP_Error( 'tep_outdated', __( 'Please update the Event Post plugin to latest version.', 'builder-mosaic' ) );
		}

		$args = themify_event_post_parse_query( array(
			'show' => $settings['tep_show'],
			'limit' => $limit,
			'offset' => $settings['tep_offset'],
			'category' => preg_replace( '/\|[multiple|single]*$/', '', $settings['tep_category'] ),
			'order' => $settings['tep_order'],
		) );

		if ( $paged > 1 ) {
			$args['offset'] = ( ( $paged - 1 ) * $limit ) + $settings['tep_offset'];
		}
		$query = new WP_Query( $args );
		$items = array();
		if ( $query->have_posts() ) {
			global $ThemifyBuilder;
			$isLoop=$ThemifyBuilder->in_the_loop===true;
			$ThemifyBuilder->in_the_loop = true;
			if ( is_object( $post ) ){
				$saved_post = clone $post;
			}
			while ( $query->have_posts() ) {
				$query->the_post();

				ob_start();
				themify_event_post_date();
				$event_date = ob_get_clean();
				$text =
					'<p>'
						. $event_date
						. get_the_excerpt()
					. '</p>';

				$items[] = array(
					'title' => get_the_title(),
					'image' => has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_id(), 'full' ) : '',
					'text' => $text,
					'link' => get_permalink(),
					'css_classes' => str_replace( 'post ', '', join( ' ', get_post_class() ) ),
				);
			}
			$ThemifyBuilder->in_the_loop=$isLoop;
			if ( isset( $saved_post ) && is_object( $saved_post ) ) {
				$post = $saved_post;
				setup_postdata( $saved_post );
			}
		}
		wp_reset_postdata();
		return array(
			'items' => $items,
			'total_items' => $query->found_posts,
		);
	}
}