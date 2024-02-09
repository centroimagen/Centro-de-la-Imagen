
<?php if ( $generate === 1 || $generate === 3 ) :
	$singular_name = PTB_Utils::get_label( $cpt->singular_label );
	$plural_name = PTB_Utils::get_label( $cpt->plural_label );
	$sanitized_name = str_replace( '-', '_', $post_type );
	?>
/**
 * Register <?php echo $post_type; ?> post type
 *
 * @hooked to init
 */
function <?php echo $prefix; ?>ptb_register_<?php echo $sanitized_name; ?>() {
	register_post_type( '<?php echo $post_type; ?>', [
		'labels' => [
			'name'               => __( '<?php echo $plural_name; ?>', 'text-domain' ),
			'singular_name'      => __( '<?php echo $singular_name; ?>', 'text-domain' ),
			'add_new'            => __( '<?php printf( PTB_Utils::get_label( $cpt->add_new ), $singular_name ); ?>', 'text-domain' ),
			'add_new_item'       => __( '<?php printf( PTB_Utils::get_label( $cpt->add_new_item ), $singular_name ); ?>', 'text-domain' ),
			'edit_item'          => __( '<?php printf( PTB_Utils::get_label( $cpt->edit_item ), $singular_name ); ?>', 'text-domain' ),
			'new_item'           => __( '<?php printf( PTB_Utils::get_label( $cpt->new_item ), $singular_name ); ?>', 'text-domain' ),
			'all_items'          => __( '<?php printf( PTB_Utils::get_label( $cpt->all_items ), $plural_name ); ?>', 'text-domain' ),
			'view_item'          => __( '<?php printf( PTB_Utils::get_label( $cpt->view_item ), $singular_name ); ?>', 'text-domain' ),
			'search_items'       => __( '<?php printf( PTB_Utils::get_label( $cpt->search_items ), $plural_name ); ?>', 'text-domain' ),
			'not_found'          => __( '<?php echo PTB_Utils::get_label( $cpt->not_found ); ?>', 'text-domain' ),
			'not_found_in_trash' => __( '<?php echo PTB_Utils::get_label( $cpt->not_found_in_trash ); ?>', 'text-domain' ),
			'parent_item_colon'  => __( '<?php printf( PTB_Utils::get_label( $cpt->parent_item_colon ), $singular_name ); ?>', 'text-domain' ),
			'menu_name'          => __( '<?php printf( PTB_Utils::get_label( $cpt->menu_name ), $singular_name ); ?>', 'text-domain' ),
		],
		'supports' => <?php $this->export_var( $cpt->supports ) ?>,
		'taxonomies' => <?php $this->export_var( $cpt->taxonomies ) ?>,
		'has_archive' => <?php $this->export_var( $cpt->has_archive ); ?>,
		'rewrite' => [
			'slug' => '<?php echo empty( $cpt->ad_rewrite_slug ) ? $post_type : $cpt->ad_rewrite_slug; ?>',
			'with_front' => <?php $this->export_var( $cpt->with_front ); ?> 
		],
		'hierarchical' => <?php $this->export_var( $cpt->is_hierarchical ); ?>,
		'exclude_from_search' => <?php $this->export_var( $cpt->ad_exclude_from_search ); ?>,
		'can_export' => <?php $this->export_var( $cpt->ad_can_export ); ?>,
		'publicly_queryable' => <?php $this->export_var( $cpt->ad_publicly_queryable ); ?>,
		'show_ui' => <?php $this->export_var( $cpt->ad_show_ui ); ?>,
		'show_in_menu' => <?php $this->export_var( $cpt->ad_show_in_menu ); ?>,
		'show_in_nav_menus' => <?php $this->export_var( $cpt->ad_show_in_nav_menus ); ?>,
		'show_in_rest' => <?php $this->export_var( $cpt->ad_show_in_rest ); ?>,
		'menu_position' => <?php echo $cpt->ad_menu_position; ?>,
		'menu_icon' => '<?php echo $cpt->ad_menu_icon; ?>',
		'capability_type' => '<?php echo $cpt->ad_capability_type; ?>',
	] );
}
add_action( 'init', '<?php echo $prefix; ?>ptb_register_<?php echo $sanitized_name; ?>' );

	<?php foreach ( $cpt->taxonomies as $tax ) {
		$tax = $this->options->get_custom_taxonomy( $tax );
		$attached_types = empty( $tax->attach_to ) ? [] : $tax->attach_to;
		if ( empty( $tax ) || in_array( $tax->slug, $exported_taxonomies, true ) ) {
			continue;
		}
		$exported_taxonomies[] = $tax->slug;
		$singular_name = PTB_Utils::get_label( $tax->singular_label );
		$plural_name = PTB_Utils::get_label( $tax->plural_label );
		$sanitized_tax_name = str_replace( '-', '_', $tax->slug );
		?>

function <?php echo $prefix; ?>ptb_register_<?php echo $sanitized_tax_name; ?>() {
	register_taxonomy( '<?php echo $tax->slug; ?>', <?php $this->export_var( $attached_types ) ?>, [
		'labels' => [
			'name'                       => __( '<?php echo $plural_name; ?>', 'text-domain' ),
			'singular_name'              => __( '<?php echo $singular_name; ?>', 'text-domain' ),
			'menu_name'                  => __( '<?php printf( PTB_Utils::get_label( $tax->menu_name ), $plural_name ); ?>', 'text-domain' ),
			'all_items'                  => __( '<?php printf( PTB_Utils::get_label( $tax->all_items ), $plural_name ); ?>', 'text-domain' ),
			'edit_item'                  => __( '<?php printf( PTB_Utils::get_label( $tax->edit_item ), $singular_name ); ?>', 'text-domain' ),
			'update_item'                => __( '<?php printf( PTB_Utils::get_label( $tax->update_item ), $singular_name ); ?>', 'text-domain' ),
			'add_new_item'               => __( '<?php printf( PTB_Utils::get_label( $tax->add_new_item ), $singular_name ); ?>', 'text-domain' ),
			'new_item_name'              => __( '<?php printf( PTB_Utils::get_label( $tax->new_item_name ), $singular_name ); ?>', 'text-domain' ),
			'parent_item'                => __( '<?php printf( PTB_Utils::get_label( $tax->parent_item ), $singular_name ); ?>', 'text-domain' ),
			'parent_item_colon'          => __( '<?php printf( PTB_Utils::get_label( $tax->parent_item_colon ), $singular_name ); ?>', 'text-domain' ),
			'search_items'               => __( '<?php printf( PTB_Utils::get_label( $tax->search_items ), $plural_name ); ?>', 'text-domain' ),
			'popular_items'              => __( '<?php printf( PTB_Utils::get_label( $tax->popular_items ), $plural_name ); ?>', 'text-domain' ),
			'separate_items_with_commas' => __( '<?php printf( PTB_Utils::get_label( $tax->separate_items_with_commas ), $plural_name ); ?>', 'text-domain' ),
			'add_or_remove_items'        => __( '<?php printf( PTB_Utils::get_label( $tax->add_or_remove_items ), $plural_name ); ?>', 'text-domain' ),
			'choose_from_most_used'      => __( '<?php printf( PTB_Utils::get_label( $tax->choose_from_most_used ), $plural_name ); ?>', 'text-domain' ),
		],
		'hierarchical' => <?php $this->export_var( $tax->is_hierarchical ); ?>,
		'rewrite' => [
			'slug' => '<?php echo empty( $tax->rewrite_slug ) ? $tax->slug : $tax->rewrite_slug; ?>',
			'with_front' => <?php $this->export_var( $tax->with_front ); ?> 
		],
		'publicly_queryable' => <?php $this->export_var( $tax->ad_publicly_queryable ); ?>,
		'show_ui' => <?php $this->export_var( $tax->ad_show_ui ); ?>,
		'show_in_rest' => <?php $this->export_var( $tax->ad_show_in_rest ); ?>,
		'show_tagcloud' => <?php $this->export_var( $tax->ad_show_tag_cloud ); ?>,
		'show_admin_column' => <?php $this->export_var( $tax->ad_show_admin_column ); ?> 
	] );
}
add_action( 'init', '<?php echo $prefix; ?>ptb_register_<?php echo $sanitized_tax_name; ?>' );

		<?php
	}
	?>

<?php endif; ?>

<?php if ( $generate === 2 || $generate === 3 ) :
	$fields = $this->options->get_cpt_cmb_options( $post_type );
	if ( empty( $fields ) ) {
		return;
	}
?>

/**
 * Add custom fields to <?php echo $post_type; ?> post type
 *
 * @return array
 */
function <?php echo $prefix; ?>ptb_filter_cmb_body_<?php echo $post_type; ?>( $fields, $post_type ) {
	if ( $post_type !== '<?php echo $post_type; ?>' ) {
		return $fields;
	}

	$fields = array_merge( $fields, <?php $this->export_var( $fields ); ?> );

	return $fields;
}
add_filter( 'ptb_filter_cmb_body', '<?php echo $prefix; ?>ptb_filter_cmb_body_<?php echo $post_type; ?>', 10, 2 );

<?php endif; ?>
