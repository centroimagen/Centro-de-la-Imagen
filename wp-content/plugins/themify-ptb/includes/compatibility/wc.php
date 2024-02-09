<?php
/**
 * Provide compatibility with WooCommerce plugin
 *
 */

class PTB_Product_Price_Template_Item extends PTB_Template_Item {
	function get_id() {
		return 'product_price';
	}

	function get_title() {
		return __( 'Product Price', 'ptb' );
	}

	function get_post_type() {
		return 'product';
	}

	function render( $args, $settings, $meta_data, $lang, $is_single, $grid_index ) {
		woocommerce_template_single_price();
	}

	function available_in_ptb_search() {
		return true;
	}

	function search_form( $id, $context, $args, $module, $post_support, $languages ) {
		?>
		<div class="ptb_back_active_module_row">
			<?php PTB_CMB_Base::module_multi_text( $id, $module, $languages, 'label', __( 'Label', 'ptb' ) ); ?>
		</div>
		<?php
	}

	function search_frontend( $post_type, $id, $args, $module, $value, $label, $lang, $languages ) {
		add_filter( 'ptb_search_keys', [ $this, 'ptb_search_keys' ] );
		?>

		<?php if ( $label ) : ?>
			<div class="ptb_search_label">
				<label for="<?php echo $id ?>"><?php echo esc_attr( $label ); ?></label>
			</div>
		<?php endif;

		$module['show_as'] = 'slider';
		$args = [
			'name' => 'product_price',
			'key' => $id
		];
		PTB_Search_Public::get_instance()->render( 'number', 'product', $id, $args, $module, null, $lang, $languages, null );
	}

	function ptb_search_filter_by_slug( $data, $post_id, $options, $cmb_options, $post_support, $post_taxonomies ) {
		if ( isset( $cmb_options['product_price'] ) ) {
			$prefix = PTB_Search_Public::$prefix;
			if ( isset( $_REQUEST[ $prefix . 'product_price-to' ] ) ) {
				$data['product_price']['to'] = $_REQUEST[ $prefix . 'product_price-to' ];
			}
			if ( isset( $_REQUEST[ $prefix . 'product_price-from' ] ) ) {
				$data['product_price']['from'] = $_REQUEST[ $prefix . 'product_price-from' ];
			}
		}

		return $data;
	}

	function ptb_search_by( $post_id, $post_type, $value, $args, $meta_key, $post_taxonomies ) {
		$query = get_posts( [
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
			'nopaging' => 1,
			'post_type' => 'product',
			'include' => ! empty( $post_id ) ? implode( ',', array_keys( $post_id ) ) : '',
			'meta_query' => [
				array(
					'key' => '_price',
					'value' => array( $value['from'], $value['to'] ),
					'compare' => 'BETWEEN',
					'type' => 'DECIMAL'
				)
			],
		] );
		if ( empty( $query ) ) {
			/* no products matching the price range, disable the post display */
			return [];
		}

		return array_fill_keys( $query, '1' );
	}

	function ptb_search_keys( $keys ) {
		$keys[ key($keys) ]['product_price'] = [ 'type' => 'product_price' ];

		return $keys;
	}

	function ptb_search_set_values( $result, $data, $slug ) {
		foreach ( $data as $key => $value ) {
			if ( $key === 'product_price' ) {
				$id = 'ptb_' . $slug . '_' . $key;
				list( $min, $max ) = $this->get_min_max_price();
				if ( empty( $max ) ) {
					$result[ $id ] = 1;
				} else {
					$result[ $id ] = [ 'min' => empty( $min ) ? 0 : floor( floatval( $min ) ), 'max' => $max ];
				}
			}
		}

		return $result;
	}

	public function get_min_max_price() {
		/**
		 * Note, the result is not filtered by language, this returns min & max prices from products in ALL languages
		 */
		$query_args = array(
			'post_type' => array( 'product_variation', 'product' ),
			'post_status' => 'publish',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'meta_key' => '_price',
			'posts_per_page' => 1,
			'no_found_rows' => true,
		);
		$max_query = get_posts( $query_args );
		if ( $max_query && isset( $max_query[0]->ID ) ) {
			$max = get_post_meta( $max_query[0]->ID, '_price', true );

			$query_args['order'] = 'ASC';
			$min_query = get_posts( $query_args );
			$min = get_post_meta( $min_query[0]->ID, '_price', true );

			return array( $min, $max );
		}

		return array( 0, 0 );
	}
}
new PTB_Product_Price_Template_Item;

class PTB_Product_Add_To_Cart_Template_Item extends PTB_Template_Item {
	function get_id() {
		return 'product_add_to_cart';
	}

	function get_title() {
		return __( 'Add To Cart', 'ptb' );
	}

	function get_post_type() {
		return 'product';
	}

	function render( $args, $settings, $meta_data, $lang, $is_single, $grid_index ) {
		woocommerce_template_loop_add_to_cart();
	}
}
new PTB_Product_Add_To_Cart_Template_Item;

class PTB_Product_OnSale_Template_Item extends PTB_Template_Item {
	function get_context() {
		return [];
	}

	function get_id() {
		return 'product_onsale';
	}

	function get_title() {
		return __( 'On Sale', 'ptb' );
	}

	function get_post_type() {
		return 'product';
	}

	function available_in_ptb_search() {
		return true;
	}

	function search_form( $id, $context, $args, $module, $post_support, $languages ) {
		?>
		<div class="ptb_back_active_module_row">
			<?php PTB_CMB_Base::module_multi_text( $id, $module, $languages, 'label', __( 'Label', 'ptb' ) ); ?>
		</div>
		<?php
	}

	function search_frontend( $post_type, $id, $args, $module, $value, $label, $lang, $languages ) {
		if ( $label ) : ?>
			<div class="ptb_search_label">
				<label for="<?php echo $id ?>"><?php echo esc_attr( $label ); ?></label>
			</div>
		<?php endif; ?>

		<label>
			<input id="ptb_product_onsale" type="checkbox" name="ptb_product_onsale" value="1">
			<?php _e( 'On Sale', 'ptb' ); ?>
		</label>

		<?php
	}

	function ptb_search_filter_by_slug( $data, $post_id, $options, $cmb_options, $post_support, $post_taxonomies ) {
		if ( isset( $cmb_options['product_onsale'] ) ) {
			if ( isset( $_REQUEST[ PTB_Search_Public::$prefix . 'product_onsale' ] ) ) {
				$data['product_onsale'] = 1;
			}
		}

		return $data;
	}

	function ptb_search_by( $post_id, $post_type, $value, $args, $meta_key, $post_taxonomies ) {
		$on_sale = wc_get_product_ids_on_sale();
		if ( empty( $on_sale ) ) {
			/* no products on sale, disable the post display */
			return [];
		}

		return array_fill_keys( $on_sale, '1' );
	}
}
new PTB_Product_OnSale_Template_Item;

class PTB_Product_InStock_Template_Item extends PTB_Template_Item {
	function get_context() {
		return [];
	}

	function get_id() {
		return 'product_instock';
	}

	function get_title() {
		return __( 'In Stock', 'ptb' );
	}

	function get_post_type() {
		return 'product';
	}

	function available_in_ptb_search() {
		return true;
	}

	function search_form( $id, $context, $args, $module, $post_support, $languages ) {
		?>
		<div class="ptb_back_active_module_row">
			<?php PTB_CMB_Base::module_multi_text( $id, $module, $languages, 'label', __( 'Label', 'ptb' ) ); ?>
		</div>
		<?php
	}

	function search_frontend( $post_type, $id, $args, $module, $value, $label, $lang, $languages ) {
		if ( $label ) : ?>
			<div class="ptb_search_label">
				<label for="<?php echo $id ?>"><?php echo esc_attr( $label ); ?></label>
			</div>
		<?php endif; ?>

		<label>
			<input id="ptb_product_instock" type="checkbox" name="ptb_product_instock" value="1">
			<?php _e( 'In Stock', 'ptb' ); ?>
		</label>

		<?php
	}

	function ptb_search_filter_by_slug( $data, $post_id, $options, $cmb_options, $post_support, $post_taxonomies ) {
		if ( isset( $cmb_options['product_instock'] ) ) {
			if ( isset( $_REQUEST[ PTB_Search_Public::$prefix . 'product_instock' ] ) ) {
				$data['product_instock'] = 1;
			}
		}

		return $data;
	}

	function ptb_search_by( $post_id, $post_type, $value, $args, $meta_key, $post_taxonomies ) {
		$query = get_posts( [
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
			'nopaging' => 1,
			'post_type' => 'product',
			'include' => ! empty( $post_id ) ? implode( ',', array_keys( $post_id ) ) : '',
			'meta_query' => [
				array(
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '=',
				)
			],
		] );
		if ( empty( $query ) ) {
			/* no products in stock, disable the post display */
			return [];
		}

		return array_fill_keys( $query, '1' );
	}
}
new PTB_Product_InStock_Template_Item;

if ( ! is_admin() ) {
	function themify_ptb_shortcode_attr_woocommerce( $attr ) {
		$attr['class'] .= ' woocommerce';

		return $attr;
	}
	add_filter( 'themify_ptb_shortcode_attr', 'themify_ptb_shortcode_attr_woocommerce' );
}