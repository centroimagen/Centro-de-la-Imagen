<?php

class Builder_Timeline_Post_Source {

	public function get_id() {
		return 'posts';
	}

	public function get_name() {
		return __( 'Posts', 'builder-timeline' );
	}
        
	/**
	 * Gets the data from a "query_category" field and
	 * returns a formatted "tax_query" array expected by WP_Query.
	 *
	 * @return array
	 */
	private static function parse_query_category_field( $value, $taxonomy = 'category' ) {
		$query = array();
		if ( '0' !== $value ) {
			$terms =  explode( ',', $value );
			$ids_in = $ids_in=$slugs_in=$slugs_not_in=array();
			foreach($terms as $a){
			    $a = trim($a);
			    if(is_numeric( $a )){
				if($a[0]==='-'){
				   $ids_not_in[] = abs($a); 
				}
				else{
				    $ids_in[]=$a;
				}
			    }
			    else if($a!==''){
				if($a[0]==='-'){
				   $slugs_not_in[] = abs($a); 
				}
				else{
				    $slugs_in[]=$a;
				}
			    }
			}
			unset($terms);

			if ( ! empty( $ids_in ) ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => $ids_in
				);
			}
			if ( ! empty( $ids_not_in ) ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => $ids_not_in,
					'operator' => 'NOT IN'
				);
			}
			if ( ! empty( $slugs_in ) ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $slugs_in
				);
			}
			if ( ! empty( $slugs_not_in ) ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $slugs_not_in,
					'operator' => 'NOT IN'
				);
			}
		}
		return $query;
	}

	public function get_items( $args ) {
		global  $paged, $post;
		$items = array();
		$args = wp_parse_args( $args, array(
			'post_type_timeline' => 'post',
			'tax_timeline' => 'category',
			'category_post_timeline' => '',
			'post_per_page_post_timeline' => '',
			'offset_post_timeline' => 0,
			'order_post_timeline' => 'desc',
			'orderby_post_timeline' => '',
			'display_post_timeline' => '',
			'hide_feat_img_post_timeline' => '',
			'image_size_post_timeline' => '',
			'img_width_post_timeline' => '',
			'img_height_post_timeline' => ''
		) );
		if( $args['category_post_timeline'] !== '' ){
			$args['category_post_timeline'] = Themify_Builder_Component_Base::get_param_value( $args['category_post_timeline'] );
		}
		$paged = Themify_Builder_Component_Base::get_paged_query();
		$query = array(
			'post_type' => $args['post_type_timeline'],
			'posts_per_page' => $args['post_per_page_post_timeline'],
			'order' => $args['order_post_timeline'],
			'orderby' => $args['orderby_post_timeline'],
			'paged' => $paged,
			'suppress_filters' => false,
			'ignore_sticky_posts' => true,
		);
		if ( $args['offset_post_timeline']  !== '' ) {
			$query['offset'] = ( ( $paged - 1 ) * $args['post_per_page_post_timeline']  ) + $args['offset_post_timeline'];
		}
		$query['tax_query'] = self::parse_query_category_field( $args['category_post_timeline'], $args['tax_timeline'] );

		if ( method_exists( 'Themify_Builder_Model', 'parse_query_filter' ) ) {
			Themify_Builder_Model::parse_query_filter( $args, $query );
		}

		$query = new WP_Query( apply_filters( 'builder_timeline_source_post_query', $query, $args ) );
		$date_format = get_option( 'date_format' );
		$show_img='yes' !== $args['hide_feat_img_post_timeline'];

		if ( $query->have_posts() ) {
			while( $query->have_posts() ) {
				$query->the_post();
				$has_img = $show_img ? has_post_thumbnail() : false;
				$item = array(
					'id' => get_the_ID(),
					'title' => get_the_title(),
					'link' => get_permalink(),
					'date' => mysql2date( 'Y-m-d G:i:s', $post->post_date ), /* do not use get_the_date to avoid translation of the date which will break strtotime */
					'date_formatted' => date_i18n( $date_format, strtotime( $post->post_date ) ),
					'hide_featured_image' =>!$has_img,
					'image' => $has_img ? themify_get_image( 'urlonly=true&w='.$args['img_width_post_timeline'] .'&h=' . $args['img_height_post_timeline'] ) : null,
					'hide_content' => 'none' === $args['display_post_timeline'],
					'content' => 'content' === $args['display_post_timeline'] ? get_the_content() : get_the_excerpt(),
				);
				$items[] = $item;
			}
		}
		wp_reset_postdata();

		return apply_filters( 'builder_timeline_source_post_items', $items );
	}

	public function get_options() {
		return array(
			array(
			    'type' => 'query_posts',
				'id' => 'post_type_timeline',
			    'term_id' => 'category_post_timeline',
				'tax_id' => 'tax_timeline',
			    'help' => sprintf(__('Add more <a href="%s" target="_blank">blog posts</a>', 'builder-timeline'), admin_url('post-new.php')),
				'query_filter' => true,
			),
			array(
				'id' => 'post_per_page_post_timeline',
				'type' => 'number',
				'label' => __('Posts Per Page', 'builder-timeline'),
				'help' => __('number of posts to show', 'builder-timeline')
			),
			array(
				'id' => 'offset_post_timeline',
				'type' => 'number',
				'label' => __('Offset', 'builder-timeline'),
				'help' => __('number of post to displace or pass over', 'builder-timeline')
			),
			array(
				'id' => 'order_post_timeline',
				'type' => 'select',
				'label' => __('Order', 'builder-timeline'),
				'help' => __('Descending = show newer posts first', 'builder-timeline'),
				'options' => array(
					'desc'=> __('Descending', 'builder-timeline'),
					'asc'=> __('Ascending', 'builder-timeline')
				)
			),
			array(
				'id' => 'orderby_post_timeline',
				'type' => 'select',
				'label' => __('Order By', 'builder-timeline'),
				'options' => array(
					'date' => __('Date', 'builder-timeline'),
					'id' => __('ID', 'builder-timeline'),
					'author' => __('Author', 'builder-timeline'),
					'title' => __('Title', 'builder-timeline'),
					'name' => __('Name', 'builder-timeline'),
					'modified' => __('Modified', 'builder-timeline'),
					'rand' => __('Rand', 'builder-timeline'),
					'comment_count' => __('Comment Count', 'builder-timeline')
				)
			),
			array(
				'id' => 'display_post_timeline',
				'type' => 'select',
				'label' => __('Display', 'builder-timeline'),
				'options' => array(
					'excerpt' => __('Excerpt', 'builder-timeline'),
					'content' => __('Content', 'builder-timeline'),
					'none' => __('None', 'builder-timeline')
				)
			),
			array(
				'id' => 'hide_feat_img_post_timeline',
				'type' => 'toggle_switch',
				'label' => __('Featured Image', 'builder-timeline'),
				'binding' => array(
					'checked' => array(
						'show' => array('image_size_post_timeline', 'img_width_post_timeline','img_height_post_timeline')
					),
					'not_checked' => array(
						'hide' => array('image_size_post_timeline', 'img_width_post_timeline','img_height_post_timeline')
					)
				)
			),
			array(
				'id' => 'image_size_post_timeline',
				'type' => 'select',
				'label' => __('Image Size', 'builder-timeline'),
				'hide' => !Themify_Builder_Model::is_img_php_disabled(),
				'image_size' => true
			),
			array(
				'id' => 'img_width_post_timeline',
				'type' => 'number',
				'label' => __('Image Width', 'builder-timeline')
			),
			array(
				'id' => 'img_height_post_timeline',
				'type' => 'number',
				'label' => __('Image Height', 'builder-timeline')
			)
		);
	}

}