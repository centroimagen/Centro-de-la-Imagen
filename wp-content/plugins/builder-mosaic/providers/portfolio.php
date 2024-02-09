<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_Portfolio extends Builder_Data_Provider {

	function is_available() {
		return class_exists( 'Themify_Portfolio_Post' );
	}

	function get_id() {
		return 'portfolio';
	}
	function get_error(){
	    return __( 'Please install Themify Portfolio Posts plugin.', 'builder-mosaic' );
	}
	function get_label() {
		return __( 'Portfolio', 'builder-mosaic' );
	}

	function get_options() {
		if ( ! $this->is_available() ) {
			return array(
				array(
					'type' => 'separator',
					'label' =>  $this->get_error()
				)
			);
		}

		return array(
			array(
				'type' => 'query_posts',
				'term_id' => 'portfolio-category',
				'taxonomy'=>'portfolio-category'
			),
			array(
				'id' => 'offset_portfolio',
				'type' => 'number',
				'label' => __( 'Offset', 'builder-mosaic' ),
				'help' => __( 'Enter the number of post to displace or pass over.', 'builder-mosaic' )
			),
			array(
				'id' => 'order_portfolio',
				'type' => 'select',
				'label' => __( 'Order', 'builder-mosaic' ),
				'help' => __( 'Descending = show newer posts first', 'builder-mosaic' ),
				'order' =>true
			),
			array(
				'id' => 'orderby_portfolio',
				'type' => 'select',
				'label' => __( 'Order By', 'builder-mosaic' ),
				'options' => array(
					'date' => __( 'Date', 'builder-mosaic' ),
					'id' => __( 'ID', 'builder-mosaic' ),
					'author' => __( 'Author', 'builder-mosaic' ),
					'title' => __( 'Title', 'builder-mosaic' ),
					'name' => __( 'Name', 'builder-mosaic' ),
					'modified' => __( 'Modified', 'builder-mosaic' ),
					'rand' => __( 'Random', 'builder-mosaic' ),
					'comment_count' => __( 'Comment Count', 'builder-mosaic' )
				)
			),
			array(
				'id' => 'lightbox_link_portfolio',
				'type' => 'toggle_switch',
				'label' => __( 'Post Lightbox', 'builder-mosaic' ),
				'help' => __( 'Open post in lightbox window', 'builder-mosaic' ),
				'options' => array(
					'on' => array( 'value' =>'en', 'name' => '1' ),
					'off' => array( 'value' => 'dis', 'name' => '0' )
				),
				'binding' => array(
					'1' => array( 'show' =>  'tb_lightbox_dim'),
					'0' => array( 'hide' => 'tb_lightbox_dim' )
				),
				'control'=>false
			),
			array(
				'type' => 'multi',
				'label' => __('Lightbox Dimension', 'builder-mosaic'),
				'wrap_class'=>'tb_lightbox_dim',
				'options' => array(
					array(
						'id' => 'lightbox_width',
						'type' => 'range',
						'label' =>'w',
						'control' =>false,
						'units' => array(
							'px' => array(
								'max' => 3500
							),
							'%' => ''
						)
					),
					array(
						'id' => 'lightbox_height',
						'type' => 'range',
						'label' => 'ht',
						'control' => false,
						'units' => array(
							'px' => array(
								'max' => 3500
							),
							'%' => ''
						)
					)
				),
			),
		);
	}

	function get_items( $settings, $limit, $paged ) {
		global $post;

		$settings = wp_parse_args( $settings, array(
			'post_type' => 'portfolio',
			'tax' => 'portfolio-category',
			'post_slug' => '',
			'offset_portfolio' => 0,
			'order_portfolio' => 'desc',
			'orderby_portfolio' => 'date',
			'lightbox_link_portfolio' => '0',
			'lightbox_width' => '',
			'lightbox_height' => '',
			'lightbox_width_unit' => 'px',
			'lightbox_height_unit' => 'px',
		) );
		$settings['tax'] = 'portfolio-category';
		$args = array(
			'post_type' => $settings['post_type'],
			'post_status' => 'publish',
			'posts_per_page' => $limit,
			'order' => $settings['order_portfolio'],
			'orderby' => $settings['orderby_portfolio'],
			'suppress_filters' => false,
			'paged' => $paged,
			'ignore_sticky_posts' => true,
		);
		if ( $settings['offset_portfolio'] !== '' ) {
			$args['offset'] = ( ( $paged - 1 ) * $limit ) + $settings['offset_portfolio'];
		}
		if ( $settings['tax'] === 'post_slug' ) {
			if ($settings['post_slug']!=='') {
				$args['post__in'] = Themify_Builder_Model::parse_slug_to_ids( $settings['post_slug'], $settings['post_type'] );
			}
		} else {
			$terms = isset( $settings["{$settings['tax']}"] ) ? $settings["{$settings['tax']}"] : (isset($settings['tax_category'])?$settings['tax_category']:false);
			if($terms===false){
				return;
			}
			Themify_Builder_Model::parseTermsQuery($args,$terms, $settings['tax']);
		}

		$query = new WP_Query( apply_filters( 'themify_builder_mosaic_query', $args, $settings ) );
		$items = array();
		if ( $query->have_posts() ){ 
			global $ThemifyBuilder;
			$isLoop=$ThemifyBuilder->in_the_loop===true;
			$ThemifyBuilder->in_the_loop = true;
			if ( is_object( $post ) ){
				$saved_post = clone $post;
			}
			while ( $query->have_posts() ) {
				$query->the_post();
				if ( has_excerpt() ) {
					$text = get_the_excerpt();
				} else {
					$text = get_the_content( '' );
					$text = strip_shortcodes( $text );
				}
				$url = get_permalink();
				$lightbox_size = '';
				if ( $settings['lightbox_link_portfolio'] ) {
					$url = add_query_arg( array( 'tb-mosaic-lightbox' => 1 ), $url );
					if ( ! empty( $settings['lightbox_width'] ) || ! empty( $settings['lightbox_height'] ) ) {
						$lightbox_size = sprintf( '%s|%s', $settings['lightbox_width'] .  $settings['lightbox_width_unit'], $settings['lightbox_height'] .  $settings['lightbox_height_unit'] );
					}
				}
				$items[] = array(
					'title' => get_the_title(),
					'image' => has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_id(), 'full' ) : '',
					'text' => '<p>' . $text . '</p>',
					'link' => $url,
					'css_classes' => str_replace( 'post ', '', join( ' ', get_post_class() ) ),
					'link_lightbox' => $settings['lightbox_link_portfolio'] ? true : false,
					'lightbox_size' => $lightbox_size,
					'badge' => ( $categories = get_the_category() ) ? $categories[0]->name : '',
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
			'total_items' => $query->found_posts - (int) $settings['offset_portfolio'],
		);
	}
}