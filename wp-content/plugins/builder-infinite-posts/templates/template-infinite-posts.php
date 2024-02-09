<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Infinite Posts
 * 
 * Access original fields: $args['mod_settings']
 */
$fields_default = array(
    'mod_title' => '',
    'post_type_post' => 'post',
    'type_query_post' => 'category',
    'category_post' => '',
	'term_type'=> 'category',
    'query_slug_post' => '',
    'post_per_page_post' => '',
    'offset_post' => '',
    'order_post' => 'desc',
    'orderby_post' => 'date',
    'background_style' => 'builder-parallax-scrolling',
    'pagination' => 'infinite-scroll',
    'layout' => 'parallax',
    'post_layout' => 'grid4',
    'masonry' => 'enabled',
    'gutter' => 'default',
    'permalink' => 'default',
    'hide_post_image' => '',
    'image_size' => '',
    'img_width' => '',
    'img_height' => '',
    'read_more_text' => __('Read More', 'builder-infinite-posts'),
    'color_button' => 'red',
    'row_height' => 'height-default',
    'overlay_color' => '000000_0.30',
    'text_color' => 'ffffff',
    'animation_effect' => '',
    'display_content' => 'excerpt',
    'hide_post_title' => 'no',
    'title_tag' => 'h2',
    'hide_post_date' => 'yes',
    'read_more_size' => 'small',
    'unlink_image' => 'yes',
    'unlink_post_title' => 'no',
    'hide_post_meta' => 'yes',
    'buttons_style' => 'colored',
    'hide_read_more_button' => 'no',
    'css_post' => '',
	'hide_empty' => 'no',
);

if (isset($args['mod_settings']['category_post'])) {
    $args['mod_settings']['category_post'] = self::get_param_value($args['mod_settings']['category_post']);
}
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$container_class = array('module', 'module-' . $args['mod_name'], $args['module_ID'], $fields_args['css_post'], 'pagination-' . $fields_args['pagination'], 'layout-' . $fields_args['layout']);

$loops_wrapper_class = array('builder-infinite-posts-wrap');
$grid='';
if ($fields_args['layout'] === 'parallax') {
    $container_class[] = $fields_args['row_height'];
}
elseif ($fields_args['post_layout'] !== 'grid-1' && ($fields_args['layout'] === 'grid' || $fields_args['layout'] === 'overlay')) {
    $grid=str_replace('-','',$fields_args['post_layout']);
    $loops_wrapper_class[]='loops-wrapper';
    if($fields_args['gutter']==='narrow' || $fields_args['gutter']==='none'){
	$loops_wrapper_class[]=$fields_args['gutter']==='none'?'no-gutter':'gutter-' . $fields_args['gutter'];
    }
    if('enable' === $fields_args['masonry']){
		$container_class[] = 'masonry-enabled';
		$loops_wrapper_class[]='masonry';
    }
}
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
global $post;
/**
 * Do not use the global $paged variable, some pages (404 for example) don't support this
 * which breaks the pagination; the $_GET[tb-infinite] is used instead
 */

// The Query
$paged =!empty($_GET['tb-infinite'])?(int) $_GET['tb-infinite']:1;
$limit = $fields_args['post_per_page_post'];
$type_query_post = $fields_args['type_query_post'];
$terms = isset($fields_args["{$type_query_post}_post"]) ? $fields_args["{$type_query_post}_post"] : $fields_args['category_post'];
// deal with how category fields are saved
$terms = preg_replace('/\|[multiple|single]*$/', '', $terms);

$temp_terms = explode(',', $terms);
$new_terms = array();
$is_string = false;
foreach ($temp_terms as $t) {
    if (!is_numeric($t)) {
	$is_string = true;
    }
    if ('' !== $t) {
	array_push($new_terms, trim($t));
    }
}

if('rand' === $fields_args['orderby_post'] && 1 === $paged){
    set_transient('themify_infinite_post_'.$args['module_ID'],rand(),HOUR_IN_SECONDS);
}
$query_args = array(
    'post_status' => 'publish',
    'posts_per_page' => $limit,
    'orderby' => 'rand' !== $fields_args['orderby_post'] ? $fields_args['orderby_post'] : 'RAND('.get_transient('themify_infinite_post_'.$args['module_ID']).')',
    'suppress_filters' => false,
    'paged' => $paged,
    'post_type' => $fields_args['post_type_post'],
	'ignore_sticky_posts' => true,
);
    if('rand' !== $fields_args['orderby_post']){
	$query_args['order'] = $fields_args['order_post'];
    }

    if (!empty($new_terms) && 'post_slug' !== $fields_args['term_type'] && !in_array('0', $new_terms) ) {
    $query_args['tax_query'] = array(
	array(
	    'taxonomy' => $type_query_post,
	    'field' => $is_string ? 'slug' : 'id',
	    'terms' => $new_terms,
	    'operator' => ( '-' === substr($terms, 0, 1) ) ? 'NOT IN' : 'IN'
	)
    );
}

    if (!empty($fields_args['query_slug_post']) && 'post_slug' === $fields_args['term_type']) {
    $query_args['post__in'] = Themify_Builder_Model::parse_slug_to_ids($fields_args['query_slug_post'], $query_args['post_type']);
}

// add offset posts
if ($fields_args['offset_post'] !== '') {
    if (empty($limit)) {
	$limit = get_option('posts_per_page');
    }
    $query_args['offset'] = ( ( $paged - 1 ) * $limit ) + $fields_args['offset_post'];
}

if ( method_exists( 'Themify_Builder_Model', 'parse_query_filter' ) ) {
	Themify_Builder_Model::parse_query_filter( $fields_args, $query_args );
}

$query = new WP_Query($query_args);

if ( ! $query->have_posts() && $fields_args['hide_empty'] === 'yes' && Themify_Builder::$frontedit_active === false ) {
	return;
}

$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args, array(
	    'id' => $args['module_ID'],
	    'class' => implode(' ', apply_filters('themify_builder_module_classes', $container_class, $args['mod_name'], $args['module_ID'], $fields_args)),
)), $fields_args, $args['mod_name'], $args['module_ID']);

if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
if($grid!==''){
    $loops_wrapper_class=apply_filters( 'themify_loops_wrapper_class', $loops_wrapper_class,$query_args['post_type'],$grid,'builder',$fields_args,$args['mod_name']);
}

unset($query_args);
?>

<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null;
	
		if(method_exists('Themify_Builder_Component_Base','add_inline_edit_fields')){
			echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title');
		}
		elseif ($fields_args['mod_title'] !== ''){
			echo $fields_args['before_title'] , apply_filters('themify_builder_module_title', $fields_args['mod_title'], $fields_args) , $fields_args['after_title'];
		}
		do_action('themify_builder_before_template_content_render'); 
	?>

    <div class="<?php echo implode(' ',$loops_wrapper_class); ?> tf_clearfix"<?php if(Themify_Builder::$frontedit_active===false):?> data-lazy="1"<?php endif;?>>

		<?php
		unset($loops_wrapper_class);
		global $ThemifyBuilder;
		$isLoop=$ThemifyBuilder->in_the_loop===true;
		$ThemifyBuilder->in_the_loop = true;
		$template = self::locate_template("infinite-posts-{$fields_args['post_type_post']}.php");
		if ("infinite-posts-{$fields_args['post_type_post']}.php" === $template) {
			// use default template for Post post type to render
			$template = self::locate_template('infinite-posts-post.php');
		}
		include( $template );
		$ThemifyBuilder->in_the_loop = $isLoop;
		?>
    </div><!-- .builder-infinite-posts-wrap -->

    <?php if ($fields_args['pagination'] === 'links') : ?>

		<?php echo TB_Infinite_Posts_Module::get_infinity_pagination($query, $fields_args['offset_post'],$paged); ?>

    <?php elseif ($fields_args['pagination'] === 'load-more' || $fields_args['pagination'] === 'infinite-scroll') : ?>

	<?php
		if ($query->max_num_pages > $paged) :
			?>
			<div class="infinite-posts-load-more-wrap tf_textc tf_clear">
				<a class="ui rounded glossy load-more-button tf_rel" href="<?php echo add_query_arg('tb-infinite', $paged + 1) ?>">
					<?php _e('Load More', 'builder-infinite-posts'); ?>
				</a>
			</div><!-- .infinite-posts-load-more-wrap -->
		<?php endif; ?>

    <?php endif; ?>
<?php do_action('themify_builder_after_template_content_render'); ?>
</div>
