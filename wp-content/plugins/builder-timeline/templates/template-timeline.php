<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Template Timeline
 * 
 * Access original fields: $args['mod_settings']
 */
$fields_default = array(
    'mod_title_timeline' => '',
    'source_timeline' => 'post',
    'template_timeline' => 'graph',
    'add_css_timeline' => '',
    'order_timeline' => 'asc',
    'start_at_end' => 'no',
    'animation_effect' => ''
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$element_id=$args['module_ID'];
$container_class = apply_filters('themify_builder_module_classes', array(
    'module tf_hidden tf_lazy', 'module-' . $args['mod_name'], $element_id, 'layout-' . $fields_args['template_timeline'],$fields_args['add_css_timeline'] 
		), $args['mod_name'], $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'id' => $args['module_ID'],
    'class' => implode(' ', $container_class),
	    )), $fields_args, $args['mod_name'], $element_id
    );
if(Themify_Builder::$frontedit_active===false){
	$container_props['data-lazy']=1;
}
    // get items
$fields_args['items'] = Builder_Timeline::get_sources($fields_args['source_timeline'])->get_items($fields_args);

if ( empty( $fields_args['items'] ) && Themify_Builder::$frontedit_active === false ) {
	return;
}

?>
<!-- module timeline -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php 
	$container_props=$container_class=null;
	if(method_exists('Themify_Builder_Component_Base','add_inline_edit_fields')){
		echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_timeline');
	}
	elseif ($fields_args['mod_title_timeline'] !== ''){
		echo $fields_args['before_title'] , apply_filters('themify_builder_module_title', $fields_args['mod_title_timeline'], $fields_args) , $fields_args['after_title'];
	}
	do_action('themify_builder_before_template_content_render');
    if(!empty($fields_args['items'])){
		// render the template
		$fields_args['items'] = apply_filters( 'themify_builder_timeline_'.$fields_args['template_timeline'] .'_items', $fields_args['items'], $fields_args );
		self::retrieve_template('template-' . $args['mod_name'] . '-' . $fields_args['template_timeline']  . '.php', array(
			'module_ID' => $element_id,
			'mod_name' => $args['mod_name'],
			'settings' => $fields_args
			), __DIR__);
    }
	do_action('themify_builder_after_template_content_render');
	?>
</div>
<!-- /module timeline -->