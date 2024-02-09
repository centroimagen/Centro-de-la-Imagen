<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Tile Slider
 * 
 * Access original fields: $args['mod_settings']
 */

global $paged, $wp, $post;
$fields_default = array(
	'mod_title' => '',
	'template'=>'[{"x":0,"y":0,"width":7,"height":4},{"x":7,"y":0,"width":5,"height":2},{"x":7,"y":2,"width":5,"height":2},{"x":0,"y":4,"width":5,"height":2},{"x":5,"y":4,"width":7,"height":2}]',
	'tiled_posts_display' => 'posts',
	'css_class' => '',
	'effect' => 'lily',
	'entrance_effect' => 'fadeIn',
	'hide_title' => 'no',
	'hide_caption' => 'no',
	'hide_badge' => 'no',
	'caption_length' => '',
	'min_width' => 600,
	'base_height' => 150,
	'gutter' => 10,
	'pagination' => 'disabled',
	'show_as' => 'grid',
	'slides_count' => 3,
	'slider_auto_scroll' => 'off',
	'slider_speed' => 'normal',
	'slider_effect' => 'scroll',
	'slider_pause' => 'resume',
	'slider_wrap' => 'yes',
	'slider_pagination' => 'yes',
	'slider_arrows' => 'yes',
	'animation_effect' => '',
	'fallback_image' => Builder_Mosaic::$url . 'assets/images/image-placeholder.jpg',
	'custom_css_id' => $args['module_ID'],
	'hide_empty' => 'no',
);
$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
unset( $args['mod_settings'] );
$fields_default=null;
$provider_instance = Builder_Data_Provider::get_providers( $fields_args['tiled_posts_display'] );
$container_class =  apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $args['mod_name'], $args['module_ID'], 'tf_clearfix', $fields_args['css_class'],
), $args['mod_name'], $args['module_ID'], $fields_args );
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
if($fields_args['show_as'] !== 'slider' ){
	 $container_class[] = 'pagination-' . $fields_args['pagination'];
}
$paged = ! empty( $_GET['builder-mosaic'] ) ? $_GET['builder-mosaic'] : 1;
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args, array(
	'id' => $args['module_ID'],
	'class' => implode( ' ', $container_class )
)), $fields_args, $args['mod_name'], $args['module_ID']);
if($provider_instance!==false){
    $template = is_string($fields_args['template'])?json_decode( $fields_args['template'], true ):$fields_args['template'];
    if ( ! is_array( $template ) ){
	$template=array();
    }
    $limit = count( $template );
    // produce the $items array & the $total_items var
    $result = $provider_instance->get_items( $fields_args, ($fields_args['show_as'] === 'slider' ?( $limit * $fields_args['slides_count'])  : $limit), $paged );
    /**
     * Filters the items returned from Builder_Data_Provider before they're displayed
     *
     * @param array|WP_Error $result multidimensional array of items on success, WP_Error otherwise
     * @param array $fields_args module settings
     */
    $result = apply_filters( 'builder_mosaic_items', $result, $fields_args );
}

if ( $provider_instance !== false && ! is_wp_error( $result ) && empty( $result['items'] ) && $fields_args['hide_empty'] === 'yes' && Themify_Builder::$frontedit_active === false ) {
	return;
}

if ( Themify_Builder::$frontedit_active===false){
	$container_props['data-lazy']=1;
}
?>
<div <?php echo self::get_element_attributes( self::sticky_element_props( $container_props, $fields_args ) ); ?>>
	<?php $container_props = $container_class = null;
		if(method_exists('Themify_Builder_Component_Base','add_inline_edit_fields')){
			echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title');
		}
		elseif ($fields_args['mod_title'] !== ''){
			echo $fields_args['before_title'] , apply_filters('themify_builder_module_title', $fields_args['mod_title'], $fields_args) , $fields_args['after_title'];
		}
		do_action( 'themify_builder_before_template_content_render' ); 
	?>


	<?php
	if ( $provider_instance!==false && ! is_wp_error( $result ) ) :
		if ( $fields_args['show_as'] === 'slider' ) : ?>
			<div class="tf_swiper-container tf_carousel tf_overflow tf_clearfix"
				data-auto="<?php echo $fields_args['slider_auto_scroll']!=='off'?$fields_args['slider_auto_scroll']:'0' ?>"
				data-speed="<?php echo $fields_args['slider_speed']; ?>"
				data-wrapvar="<?php echo $fields_args['slider_wrap']!== 'no'?'1':'0'; ?>"
				data-slider_nav="<?php echo $fields_args['slider_arrows']==='yes'?'1':'0'; ?>"
				data-pager="<?php echo $fields_args['slider_pagination']==='yes'?'1':'0'; ?>"
				data-effect="<?php echo $fields_args['slider_effect']; ?>" 
				data-pause_hover="<?php echo $fields_args['slider_pause']==='resume'?'1':'0'; ?>"
			>
				<div class="tf_swiper-wrapper tf_lazy tf_rel tf_w tf_h">
					<?php
					$result_clone = $result;
					for ( $slide = 0; $slide < $fields_args['slides_count']; ++$slide ) :
						$result['items'] = array_slice( $result_clone['items'], $slide * $limit, $limit );

						// no more items to show
						if ( empty( $result['items'] ) ) {
							break;
						}
					?>
						<div class="tf_swiper-slide tf_lazy">
							<?php self::retrieve_template( 'grid-stack.php', array(
								'settings' => $fields_args,
								'res' => $result,
								'template'=>$template,
								'module' => $args,
								), __DIR__); ?>
						</div>

					<?php endfor; ?>
				</div><!-- .tf_swiper-wrapper -->
			</div><!-- .tf_swiper-container -->

		<?php else : ?>

			<?php self::retrieve_template( 'grid-stack.php', array(
				'settings' => $fields_args,
				'res' => $result,
				'template' => $template,
				'module' => $args,
			), __DIR__); 
			?>

			<?php if ($fields_args['pagination'] === 'links' && $result['total_items'] > $limit ) : ?>
				<?php echo TB_Mosaic_Module::pagination_links( $limit, $result['total_items']  ); ?>
			<?php elseif ( ( $fields_args['pagination'] === 'infinite-scroll' || $fields_args['pagination'] === 'load-more' ) && $result['total_items'] > ( $limit * $paged ) ) : ?>
				<div class="tbm_wrap_more tf_textc tf_hidden">
					<a class="ui builder_button rounded glossy white load-more-button tbm_more" href="<?php echo add_query_arg( 'builder-mosaic', $paged + 1 ) ?>">
						<?php _e( 'Load More', 'builder-mosaic' ); ?>
					</a>
				</div><!-- .infinite-posts-load-more-wrap -->
			<?php endif; ?>

		<?php endif; ?>

		<?php if ( $fields_args['gutter'] !== '' ) : ?>
			<style>
				@media(min-width:<?php echo ($fields_args['min_width']+1)?>px) {
					#<?php echo $fields_args['custom_css_id']; ?> .builder_mosaic_item{padding:<?php echo $fields_args['gutter']; ?>px}
					#<?php echo $fields_args['custom_css_id']; ?> .grid-stack{width:calc(100% + <?php echo $fields_args['gutter'] * 2; ?>px);margin-left:-<?php echo $fields_args['gutter']; ?>px}
				}
			</style>
		<?php endif; ?>

	<?php elseif ( $provider_instance!==false && is_wp_error( $result ) && current_user_can( 'manage_options' ) ) : ?>
		<?php print_r( $result->get_error_message() ); ?>
	<?php endif; ?>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>

</div><!-- .module-mosaic -->
