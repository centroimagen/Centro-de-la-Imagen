<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_WooCommerce extends Builder_Data_Provider {

	function is_available() {
		return themify_is_woocommerce_active();
	}

	function get_id() {
		return 'wc';
	}
	function get_error(){
	    return __( 'Please install WooCommerce plugin.', 'builder-mosaic' );
	}
	function get_label() {
		return __( 'WooCommerce Products', 'builder-mosaic' );
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
				'id' => 'query_products',
				'type' => 'radio',
				'label' => __('Products', 'builder-mosaic'),
				'options' => array(
				    array('value'=>'all','name'=>__('All Products', 'builder-mosaic')),
				    array('value'=>'featured','name'=>__('Featured Products', 'builder-mosaic')),
				    array('value'=>'onsale','name'=>__('On Sale', 'builder-mosaic')),
				    array('value'=>'toprated','name'=>__('Top Rated', 'builder-mosaic'))
				)
			),
			array(
			    'type' => 'query_posts',
			    'term_id' => 'category_products',
			    'taxonomy'=>'product_cat'
			),
			array(
				'id' => 'hide_child_products',
				'type' => 'select',
				'label' => __('Parent Category Only', 'builder-mosaic'),
				'rchoose' => true,
				'help' => __( 'Show products only from parent categories.', 'builder-mosaic' )
			),
			array(
				'id' => 'free_products',
				'type' => 'toggle_switch',
				'label' => __('Free Products', 'builder-mosaic'),
				'options' => array(
				    'on' => array('name'=>'show', 'value' =>'s'),
				    'off' => array('name'=>'hide', 'value' =>'hi')
				)
			),
			array(
				'id' => 'outofstock_products',
				'type' => 'toggle_switch',
				'label' => __('Out of Stock Products', 'builder-mosaic'),
				'options' => array(
					'on' => array('name'=>'show', 'value' =>'s'),
					'off' => array('name'=>'hide', 'value' =>'hi')
				)
			),
			array(
				'id' => 'offset_products',
				'type' => 'number',
				'label' => __('Offset', 'builder-mosaic'),
				'help' => __('Number of post to displace or pass over.', 'builder-mosaic')
			),
			array(
				'id' => 'orderby_products',
				'type' => 'select',
				'label' => __('Order By', 'builder-mosaic'),
				'options' => array(
					'date' => __('Date', 'builder-mosaic'),
					'price' => __('Price', 'builder-mosaic'),
					'sales' => __('Sales', 'builder-mosaic'),
					'id' => __('ID', 'builder-mosaic'),
					'title' => __('Title', 'builder-mosaic'),
					'rand' => __('Random', 'builder-mosaic'),
				)
			),
			array(
				'id' => 'order_products',
				'type' => 'select',
				'label' => __('Order', 'builder-mosaic'),
				'help' => __('Descending = show newer posts first', 'builder-mosaic'),
				'order' =>true
			),
		);
	}

	function get_items( $settings, $limit, $paged ) {
		global $post;

		$settings = wp_parse_args( $settings, array(
			'query_products' => 'all',
			'category_products' => '',
			'hide_child_products' => 'no',
			'free_products' => 'show',
			'outofstock_products' => 'show',
			'offset_products' => 0,
			'order_products' => 'ASC',
			'orderby_products' => 'title',
		));
		$query_args = array(
			'post_type' => 'product',
			'posts_per_page' => $limit,
			'order' => $settings['order_products'],
			'meta_query'=>array()
		);
	
		$query_args['offset'] = ( ( $paged - 1 ) * $limit ) + $settings['offset_products'];

		$query_args['meta_query'][] = WC()->query->stock_status_meta_query();
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] );
		Themify_Builder_Model::parseTermsQuery($query_args,$settings['category_products'],'product_cat');
		if(!empty($query_args['tax_query']) && $settings['hide_child_products'] === 'yes' && isset($query_args['tax_query'][0]['operator'])){
			$query_args['tax_query'][0]['include_children']=false;
		}
		if ( $settings['query_products'] === 'onsale' ) {
			$product_ids_on_sale = wc_get_product_ids_on_sale();
			$product_ids_on_sale[] = 0;
			$query_args['post__in'] = $product_ids_on_sale;
		} 
		elseif( $settings['query_products'] === 'featured' ) {
			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'product_visibility',
				'field'		=> 'name',
				'terms'		=> 'featured',
				'operator'	=> 'IN'
			);
		}

		switch ( $settings['orderby_products'] ) {
			case 'price' :
				$query_args['meta_key'] = '_price';
				$query_args['orderby']  = 'meta_value_num';
				break;
			case 'sales' :
				$query_args['meta_key'] = 'total_sales';
				$query_args['orderby']  = 'meta_value_num';
				break;
			default :
				$query_args['orderby']  = $settings['orderby_products'] ;
		}

		if ( $settings['free_products']  === 'hide' ) {
			$query_args['meta_query'][] = array(
				'key'     => '_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'DECIMAL',
			);
		}
		if ( $settings['outofstock_products'] === 'hide' ) {
			$outofstock_term = get_term_by( 'name', 'outofstock', 'product_visibility' );

			if ( ! empty( $outofstock_term ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy'	=> 'product_visibility',
					'field'		=> 'term_taxonomy_id',
					'terms'		=> array( $outofstock_term->term_taxonomy_id ),
					'operator'	=> 'NOT IN'
				);
			}
		}

		$query = new WP_Query( apply_filters( 'themify_builder_mosaic_query', $query_args, $settings ) );
	
		$items = array();
		if ( $query->have_posts() ){ 
			
			global $ThemifyBuilder;
			$isLoop=$ThemifyBuilder->in_the_loop===true;
			$ThemifyBuilder->in_the_loop = true;
			if ( is_object( $post ) ){
				$saved_post = clone $post;
			}
			while( $query->have_posts() ){
				$query->the_post();
				global $product;

				if ( $price = $product->get_price_html() ) {
					$price = sprintf( '<span class="price">%s</span>', $price );
				}
				$add_to_cart = sprintf( '<a href="%s" class="add_to_cart_button">%s</a>', $product->add_to_cart_url(), $product->add_to_cart_text() );
				$badge = $product->is_on_sale() ? __( 'Sale!', 'builder-mosaic' ) : '';

				$text =
					$price
					. $add_to_cart
					. '<p>'
						. get_the_excerpt()
					. '</p>';
				$items[] = array(
					'title' => get_the_title(),
					'image' => has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_id(), 'full' ) : '',
					'text' => $text,
					'link' => get_permalink(),
					'css_classes' => str_replace( 'post ', '', join( ' ', get_post_class() ) ),
					'badge' => $badge,
				);
			}
			$ThemifyBuilder->in_the_loop = $isLoop;
			if(isset(Themify_Builder_Component_Module::$isFirstModule) && Themify_Builder_Component_Module::$isFirstModule===true){
				Themify_Builder_Model::loadCssModules('tbm_products',Builder_Mosaic::$url . 'assets/modules/product.css',Builder_Mosaic::get_version());
			}
			if ( isset( $saved_post ) && is_object( $saved_post ) ) {
				$post = $saved_post;
				setup_postdata( $saved_post );
			}
		}

		wp_reset_postdata();
		return array(
			'items' => $items,
			'total_items' => $query->found_posts - (int) $settings['offset_products'],
		);
	}
}