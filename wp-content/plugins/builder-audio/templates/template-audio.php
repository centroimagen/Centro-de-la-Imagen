<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Music Playlist
 * 
 * Access original fields: $args['mod_settings']
*/

$fields_default = array(
    'mod_title_playlist' => '',
    'music_playlist' => array(),
    'hide_download_audio' => 'yes',
    'add_css_audio' => '',
    'animation_effect' => '',
    'audio_buy_button_text' => '',
    'audio_buy_button_link' => '',
    'buy_button_new_window' => '',
    'auto_play_audio'=> ''
);

$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$container_class = apply_filters('themify_builder_module_classes', array(
    'module', 'module-'.$args['mod_name'],'tf_lazy',$args['module_ID'], $fields_args['add_css_audio']
		), $args['mod_name'], $args['module_ID'], $fields_args);
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
    $container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args, array(
	    'id' => $args['module_ID'],
    'class' => implode(' ', $container_class),
    )), $fields_args, $args['mod_name'], $args['module_ID']);
    if(Themify_Builder::$frontedit_active===false){
	    $container_props['data-lazy']=1;
}
$is_inline_edit_supported=method_exists('Themify_Builder_Component_Base','add_inline_edit_fields');
?>
<!-- module audio -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null;?>
    <?php do_action('themify_builder_before_template_content_render');
		if($is_inline_edit_supported===true){
			echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_playlist');
		}
		elseif ($fields_args['mod_title_playlist'] !== ''){
			echo $fields_args['before_title'] , apply_filters('themify_builder_module_title', $fields_args['mod_title_playlist'], $fields_args) , $fields_args['after_title'];
		}
	?>
    <?php if (!empty($fields_args['music_playlist'])): ?>
	<div class="album-playlist">
	    <ol class="tracklist tf_rel tf_w">
		<?php $default_text = __('Buy now', 'builder-audio'); ?>
		<?php foreach ($fields_args['music_playlist'] as $item) : ?>
		    <li class="track is-playable<?php echo !empty($fields_args['auto_play_audio'])? ' auto_play' : '' ?> tf_rel tf_lazy" itemprop="track" itemscope="" itemtype="https://schema.org/MusicRecording">
			<?php if (!empty($item['audio_buy_button_link']) || !empty($item['audio_buy_button_text'])) : ?>
								<a <?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('audio_buy_button_text',true,false,'music_playlist'); }?> class="ui builder_button default track-buy"
									href="<?php echo !empty($item['audio_buy_button_link']) ? $item['audio_buy_button_link'] : '#' ?>"
									<?php echo ! empty( $fields_args['buy_button_new_window'] ) ? 'target="_blank"' : ''; ?>>
									<?php echo !empty($item['audio_buy_button_text']) ? $item['audio_buy_button_text'] : $default_text; ?>
			    </a>
			<?php endif; ?>
			<a class="track-title" href="#" itemprop="url"><span itemprop="name"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('audio_name',true,false,'music_playlist'); }?>><?php echo isset($item['audio_name']) ? $item['audio_name'] : ''; ?></span></a>
			<?php if ($fields_args['hide_download_audio'] !== 'yes') : ?>
			    <a href="<?php echo $item['audio_url']; ?>" class="builder-audio-download" download>
		<?php echo themify_get_icon('fa-download','fa'); ?>
		<span class="screen-reader-text"><?php _e('Download','builder-audio'); ?></span>
	    </a>
			    <?php endif; ?>
			    <?php echo !empty($item['audio_url'])?wp_audio_shortcode(array('src' => $item['audio_url'],'preload'=>'none')):''; ?>
		    </li>
		<?php endforeach; ?>
	    </ol>
	</div><!-- .album-playlist -->
    <?php endif; ?>
    <?php do_action('themify_builder_after_template_content_render'); ?>
</div>
<!-- /module audio -->