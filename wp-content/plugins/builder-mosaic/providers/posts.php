<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_Posts extends Builder_Data_Provider {

	function __construct() {
		add_action( 'template_redirect', array( $this, 'post_lightbox_init' ), 100 );
	}

	function get_id() {
		return 'posts';
	}

	function get_label() {
		return __( 'Posts', 'builder-mosaic' );
	}

	function get_options() {
		return array(
			array(
			    'type' => 'query_posts',
			    'id' => 'post_type_post',
			    'tax_id' => 'tax',
			    'term_id' => 'tax_category',
			    'slug_id' => 'post_slug',
				'query_filter' => true,
			),
			array(
				'id' => 'offset',
				'type' => 'number',
				'label' => __( 'Offset', 'builder-mosaic' ),
				'help' => __( 'Enter the number of post to displace or pass over.', 'builder-mosaic' )
			),
			array(
				'id' => 'order',
				'type' => 'select',
				'label' => __( 'Order', 'builder-mosaic' ),
				'help' => __( 'Descending = show newer posts first', 'builder-mosaic' ),
				'order' =>true
			),
			array(
				'id' => 'orderby',
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
				'id' => 'lightbox_link',
				'type' => 'toggle_switch',
				'label' => __( 'Post Lightbox', 'builder-mosaic' ),
				'help' => __( 'Open post in lightbox window', 'builder-mosaic' ),
				'options' => array(
					'on' => array( 'value' =>'en', 'name' => '1' ),
					'off' => array( 'value' => 'dis', 'name' => '0' )
				),
				'binding' => array(
				    '1' => array( 'show' =>'tb_lightbox_dim' ),
				    '0' => array( 'hide' =>'tb_lightbox_dim' )
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
					        '%' =>''
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
					        '%' =>''
				        )
			        )
		        ),
	        ),
		);
	}

	function get_items( $settings, $limit, $paged ) {
		global $post;

		$settings = wp_parse_args( $settings, array(
			'post_type_post' => 'post',
			'tax' => 'category',
			'post_slug' => '',
			'offset' => '',
			'order' => 'desc',
			'orderby' => 'date',
			'lightbox_link' => '0',
			'lightbox_width' => '',
			'lightbox_height' => '',
			'lightbox_width_unit' => 'px',
			'lightbox_height_unit' => 'px',
		) );
		$args = array(
			'post_type' => $settings['post_type_post'],
			'post_status' => 'publish',
			'posts_per_page' => $limit,
			'order' => $settings['order'],
			'orderby' => $settings['orderby'],
			'suppress_filters' => false,
			'paged' => $paged,
			'ignore_sticky_posts' => true,
		);
		if ( $settings['offset'] !== '' ) {
			$args['offset'] = ( ( $paged - 1 ) * $limit ) + $settings['offset'];
		}
		if ( $settings['tax'] === 'post_slug' ) {
			if ( $settings['post_slug'] !== '' ) {
				$args['post__in'] = Themify_Builder_Model::parse_slug_to_ids( $settings['post_slug'], $settings['post_type'] );
			}
		} else {
			$terms = isset( $settings["tax_{$settings['tax']}"] ) ? $settings["tax_{$settings['tax']}"] : ( isset($settings['tax_category'] ) ? $settings['tax_category'] : false );
			if ( $terms === false ) {
			    return new WP_Error( 'empty_term', __( 'No term has been specified.', 'builder-mosaic' ) );
			}
			Themify_Builder_Model::parseTermsQuery( $args, $terms, $settings['tax'] );
		}

		if ( method_exists( 'Themify_Builder_Model', 'parse_query_filter' ) ) {
			Themify_Builder_Model::parse_query_filter( $settings, $args );
		}
		
		$query = new WP_Query( apply_filters( 'themify_builder_mosaic_query', $args, $settings ) );
		
		$items = array();
		if ( $query->have_posts() ) {
			add_filter( 'excerpt_length', array( $this, 'excerpt_length' ), 999 );
			global $ThemifyBuilder;
			$isLoop=$ThemifyBuilder->in_the_loop===true;
			$ThemifyBuilder->in_the_loop = true;
			if ( is_object( $post ) ){
				$saved_post = clone $post;
			}
			while ( $query->have_posts() ) {
				$query->the_post();
				$text = get_the_excerpt();
				$url = $this->get_permalink();
				$lightbox_size = '';
				if ( $settings['lightbox_link'] ) {
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
					'link_lightbox' => $settings['lightbox_link'] ? true : false,
					'lightbox_size' => $lightbox_size,
					'badge' => ( $categories = get_the_category() ) ? $categories[0]->name : '',
				);
			}
			$ThemifyBuilder->in_the_loop = $isLoop;
			if ( isset( $saved_post ) && is_object( $saved_post ) ) {
				$post = $saved_post;
				setup_postdata( $saved_post );
			}
			remove_filter( 'excerpt_length', array( $this, 'excerpt_length' ), 999 );
		}
		wp_reset_postdata();

		return array(
			'items' => $items,
			'total_items' => $query->found_posts - (int) $settings['offset'],
		);
	}

	/**
	 * Sets the excerpt length, high priority to ensure content is not trimmed by external sources.
	 *
	 * @return int
	 */
	function excerpt_length() {
		return 100;
	}

	/**
	 * Returns the post permalink.
	 * in Themify themes, if External Link custom field is used, that URL will be used instead.
	 *
	 * @return string
	 */
	function get_permalink() {
		if ( function_exists( 'themify_get' ) && themify_get( 'external_link', '' ) !== '' ) {
			return themify_get( 'external_link' );
		} else {
			return get_permalink();
		}
	}


	/**
	 * Handle display of single post lightbox
	 *
	 * @hook to template_redirect
	 */
	function post_lightbox_init() {
		if ( self::is_lightbox() && is_singular() ) {
			show_admin_bar( false );
			add_filter( 'body_class', array( $this, 'lightbox_body_class' ) );
			add_filter( 'template_include', array( $this, 'template_include' ), 100 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 100 );
		}
	}

	/**
	 * Template to display in the lightbox window
	 *
	 * @return string
	 */
	function template_include( $file ) {
		return trailingslashit( dirname( __FILE__ ) ) . 'templates/post-lightbox.php';
	}

	/**
	 * Adds stylesheet for single post lightbox
	 */
	function wp_enqueue_scripts() {
		wp_enqueue_style( 'tb_mosaic_lightbox', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/tbp-post-lightbox.min.css',null,Builder_Mosaic::get_version() );
	}

	/**
	 * Adds a unique classname to <body> when page is displayed inside lightbox
	 *
	 * @return array
	 */
	function lightbox_body_class( $classes ) {
		$classes[] = 'tb_mosaic_lightbox';

		return $classes;
	}
}