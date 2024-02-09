<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Template Field Types
 * 
 * Access original fields: $args['mod_settings']
 */
/* set the default options for the module */
$fields_default = array(
    'mod_title_slider' => '',
    'builder_ps_triggers_position' => 'standard',
    'builder_ps_wrap' => '',
    'builder_ps_triggers_type' => 'circle',
    'builder_ps_aa' => 'off',
    'pause_last_slide' => '',
    'builder_ps_hover_pause' => 'pause',
    'builder_ps_timer' => 'no',
    'builder_ps_width' => '',
    'builder_ps_height' => '',
    'builder_ps_thumb_width' => 30,
    'builder_ps_thumb_height' => 30,
    'builder_slider_pro_slides' => array(),
    'my_text_option' => '',
    'touch_swipe_desktop' => 'yes',
    'touch_swipe_mob' => 'yes',
    'css_slider_pro' => '',
);
/* default options for each slide */
$slide_defaults = array(
    'builder_ps_slide_type' => 'Image',
    'builder-ps-bg-image' => '',
    'builder_ps_tranzition' => 'slideTop',
    'builder_ps_layout' => 'bsp-slide-content-left',
    'builder_ps_tranzition_duration' => 'normal',
    'builder-ps-bg-color' => '',
    'builder-ps-slide-image' => '',
    's_c_w' => '',
    's_c_h' => '',
    'builder_ps_heading' => '',
    'builder_ps_text' => '',
    'builder_ps_text_color' => '',
    'builder_ps_text_link_color' => '',
    'builder_ps_button_action_type' => '',
    'builder_ps_button_text' => '',
    'builder_ps_button_link' => '',
    'builder_ps_button_icon' => '',
    'l_w' => '',
    'l_w_unit' => 'px',
    'l_h' => '',
    'l_h_unit' => 'px',
    'builder_ps_h3s_timer' => 'shortTop',
    'builder_ps_h3e_timer' => 'shortTop',
    'builder_ps_ps_timer' => 'shortTop',
    'builder_ps_pe_timer' => 'shortTop',
    'builder_ps_as_timer' => 'shortTop',
    'builder_ps_ae_timer' => 'shortTop',
    'builder_ps_imgs_timer' => 'shortTop',
    'builder_ps_imge_timer' => 'shortTop',
    'builder_ps_button_color' => '',
    'builder_ps_button_bg' => '',
);

/* setup element transition fallbacks */
$timer_translation = array(
    'disable' => 'disable',
    'shortTop' => 'up',
    'shortTopOut' => 'up',
    'longTop' => 'up',
    'longTopOut' => 'up',
    'shortLeft' => 'left',
    'shortLeftOut' => 'left',
    'longLeft' => 'left',
    'longLeftOut' => 'left',
    'skewShortLeft' => 'left',
    'skewShortLeftOut' => 'left',
    'skewLongLeft' => 'left',
    'skewLongLeftOut' => 'left',
    'shortBottom' => 'down',
    'shortBottomOut' => 'down',
    'longBottom' => 'down',
    'longBottomOut' => 'down',
    'shortRight' => 'right',
    'shortRightOut' => 'right',
    'longRight' => 'right',
    'longRightOut' => 'right',
    'skewShortRight' => 'right',
    'skewShortRightOut' => 'right',
    'skewLongRight' => 'right',
    'skewLongRightOut' => 'right',
    /* fallbacks: replace all non-existent effects with up */
    'fade' => 'up',
    'fadeOut' => 'up'
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$element_id = $args['module_ID'];
$container_class = apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $args['mod_name'], $element_id, 'pager-' . $fields_args['builder_ps_triggers_position'], 'pager-type-' . $fields_args['builder_ps_triggers_type'], $fields_args['css_slider_pro']
	), $args['mod_name'], $element_id, $fields_args);
if (!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active === false) {
    $container_class[] = $fields_args['global_styles'];
}
$styles = array();
$container_props = apply_filters('themify_builder_module_container_props', array(
    'class' => implode(' ', $container_class),
    'data-loop' => $fields_args['builder_ps_wrap']==='yes'?'1':'0',
    'data-thumbnail-width' => $fields_args['builder_ps_thumb_width'],
    'data-thumbnail-height' => $fields_args['builder_ps_thumb_height'],
    'data-autoplay' => $fields_args['builder_ps_aa'],
    'data-hover-pause' => $fields_args['builder_ps_hover_pause'],
    'data-timer-bar' => $fields_args['builder_ps_timer'],
    'data-slider-width' => isset($fields_args['builder_ps_fullscreen']) && $fields_args['builder_ps_fullscreen'] === 'fullscreen' ? '100%' : $fields_args['builder_ps_width'],
    'data-slider-height' => isset($fields_args['builder_ps_fullscreen']) && $fields_args['builder_ps_fullscreen'] === 'fullscreen' ? '100vh' : $fields_args['builder_ps_height'],
    'data-touch-swipe-desktop' => $fields_args['touch_swipe_desktop'],
    'data-touch-swipe-mobile' => $fields_args['touch_swipe_mob'],
	), $fields_args, $args['mod_name'], $args['module_ID']);

if (Themify_Builder::$frontedit_active === false) {
    $container_props['data-lazy'] = 1;
}
if ($fields_args['builder_ps_aa'] !== 'off' && $fields_args['pause_last_slide'] === 'yes') {
    $container_props['data-pause-last'] = 1;
}
if(isset(Themify_Builder_Component_Module::$isFirstModule) && Themify_Builder_Component_Module::$isFirstModule===true){
    $assets_url = Builder_Pro_Slider::$url . 'assets/';
    $v = Builder_Pro_Slider::$version;
    Themify_Enqueue_Assets::addPrefetchJs($assets_url.'jquery.sliderPro.js','1.2.1');
    $assets_url.='modules/';
}
$is_inline_edit_supported=method_exists('Themify_Builder_Component_Base','add_inline_edit_fields');
?>
<!-- Slider Pro module -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
    <?php
    $container_props = $container_class = null;
	if($is_inline_edit_supported===true){
		echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_slider');
	}
	elseif ($fields_args['mod_title_slider'] !== ''){
		echo $fields_args['before_title'] , apply_filters('themify_builder_module_title', $fields_args['mod_title_slider'], $fields_args) , $fields_args['after_title'];
	}
    do_action('themify_builder_before_template_content_render');
    ?>
	<?php if (!empty($fields_args['builder_slider_pro_slides'])): ?>
	<div class="slider-pro tf_rel tf_hidden tf_lazy">
	    <?php if ($fields_args['builder_ps_timer'] === 'yes' && $fields_args['builder_ps_aa']!=='off'): ?>
	    <div class="bsp-timer-bar"></div>
	    <?php endif; ?>
	    <div class="sp-slides-container tf_rel">
			<div class="sp-mask tf_rel tf_overflow">
				<div class="sp-slides tf_rel">
					<?php foreach ($fields_args['builder_slider_pro_slides'] as $i => $slide) : ?>
						<?php
						$slide = wp_parse_args($slide, $slide_defaults);

						$is_empty_slide = ( $slide['builder_ps_slide_type'] === 'Image' && empty($slide['builder-ps-bg-image']) ) || ( $slide['builder_ps_slide_type'] === 'Video' && empty($slide['builder_ps_vbg_option']) );
						$slide_background='';
						if($is_empty_slide === false && $slide['builder_ps_slide_type'] === 'Image' ){
							if($fields_args['builder_ps_width'] === '' && $fields_args['builder_ps_height'] === ''){
								themify_generateWebp($slide['builder-ps-bg-image']);
								$slide_background=$slide['builder-ps-bg-image'];
							}
							else{
								$slide_background=themify_get_image(array('src' => $slide['builder-ps-bg-image'], 'w' => $fields_args['builder_ps_width'], 'h' => $fields_args['builder_ps_height'], 'urlonly' => true));
							}
							$slide_background = $i===0 && isset($assets_url)?sprintf(' style="background-image:url(%s)"', $slide_background):sprintf(' data-bg="%s"', $slide_background);
						}
						// slide styles
						if (!empty($slide['builder-ps-bg-color'])){
						$styles[] = sprintf('.sp-slide-%s:before{background-color:%s}', $i, Themify_Builder_Stylesheet::get_rgba_color($slide['builder-ps-bg-color']));
						}
						if ('' !== $slide['builder_ps_text_color']){
						$styles[] = explode(',', sprintf('.sp-slide-%1$s .bsp-slide-excerpt,.sp-slide-%1$s .bsp-slide-excerpt p,.sp-slide-%1$s .sp-slide-text .bsp-slide-post-title{color:%2$s}', $i, Themify_Builder_Stylesheet::get_rgba_color($slide['builder_ps_text_color'])));
						}
						if ('' !== $slide['builder_ps_text_link_color']){
						$styles[] = explode(',', sprintf('.sp-slide-%1$s .bsp-slide-excerpt a,.sp-slide-%1$s .bsp-slide-excerpt p a{color:%2$s}', $i, Themify_Builder_Stylesheet::get_rgba_color($slide['builder_ps_text_link_color'])));
						}
						if ('' !== $slide['builder_ps_button_color']){
						$styles[] = sprintf('.sp-slide-%1$s a.bsp-slide-button{color:%2$s}', $i, Themify_Builder_Stylesheet::get_rgba_color($slide['builder_ps_button_color']));
						}
						if ('' !== $slide['builder_ps_button_bg']){
						$styles[] = sprintf('.sp-slide-%1$s a.bsp-slide-button{background-color:%2$s}', $i, Themify_Builder_Stylesheet::get_rgba_color($slide['builder_ps_button_bg']));
						}
						?>
					<div class="sp-slide sp-slide-<?php echo $i; ?> sp-slide-type-<?php echo $slide['builder_ps_slide_type']; ?> <?php if($i===0):?> sp-selected<?php endif;?> <?php echo $slide['builder_ps_layout']. ($slide['builder_ps_layout'] === 'bsp-slide-content-center'?' tf_textc':''); ?> <?php if ($is_empty_slide === true) echo ' bsp-no-background'; ?> tf_w" data-transition="<?php echo $slide['builder_ps_tranzition']; ?>" data-duration="<?php echo Builder_Pro_Slider::get_speed($slide['builder_ps_tranzition_duration']); ?>" <?php echo $slide_background; ?>>
						<?php
						if ($is_empty_slide === false) {
							/* slider thumbnail */
							if ($fields_args['builder_ps_triggers_type'] === 'thumb') {
							$url = themify_get_image(array('src' => $slide['builder-ps-bg-image'], 'w' => $fields_args['builder_ps_thumb_width'], 'h' => $fields_args['builder_ps_thumb_height'], 'urlonly' => true));
							$alt = Themify_Builder_Model::get_alt_by_url($slide['builder-ps-bg-image']);
							if(isset($assets_url)){
								Themify_Builder_Model::loadCssModules('bsp_thumbnails',$assets_url.'thumbnails.css',$v);
							}
							?>
							<img class="sp-thumbnail" data-tf-not-load <?php if($i===0 && isset($assets_url)):?>data-tf-done="1" src="<?php echo $url; ?>"<?php else:?>src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?php echo $url; ?>"<?php endif;?> alt="<?php echo $alt; ?>">
							<?php if($i>0 || !isset($assets_url)):?>
								<noscript>
									<img data-no-script class="sp-thumbnail" src="<?php echo $url; ?>" alt="<?php echo $alt; ?>">
								</noscript>
							<?php endif;?>
							<?php
							}
							if ($slide['builder_ps_slide_type'] === 'Video') {
							if (strpos($slide['builder_ps_vbg_option'], 'youtube.com') !== false || strpos($slide['builder_ps_vbg_option'], 'vimeo.com') !== false):
								?>
								<div class="bsp_frame" data-url="<?php echo $slide['builder_ps_vbg_option'] ?>"></div>
							<?php else: ?>
								<video data-tf-not-load decoding="async" class="tf_abs tf_w tf_h sp-video" playsinline muted<?php echo $fields_args['builder_ps_aa'] === 'off' ? ' loop' : ''; ?> preload="none">
									<source src="<?php echo $slide['builder_ps_vbg_option'] ?>" type="video/<?php echo pathinfo($slide['builder_ps_vbg_option'], PATHINFO_EXTENSION) ?>"/>
								</video>
							<?php
							endif;
							if(isset($assets_url)){
								Themify_Builder_Model::loadCssModules('bsp_video',$assets_url.'video.min.css',$v);
							}
							}
						}
						?>
						<div class="bsp-layers-overlay<?php $slide['builder_ps_slide_type'] === 'Video' ? ' tf_abs' : ' tf_rel' ?> tf_w tf_vmiddle">
							<div class="sp-slide-wrap">
								<?php if (!empty($slide['builder-ps-slide-image'])) : ?>
									<div class="sp-layer sp-slide-image<?php if ($slide['builder_ps_layout'] === 'bsp-slide-content-center'): ?> tf_textc tf_clearfix<?php endif; ?> tf_box tf_left"
									<?php if ('disable' !== $timer_translation[$slide['builder_ps_imgs_timer']]): ?>
									 data-show-transition="<?php echo $timer_translation[$slide['builder_ps_imgs_timer']]; ?>"
									 data-hide-transition="<?php echo $timer_translation[$slide['builder_ps_imge_timer']]; ?>"
									 data-show-duration="1000"
									 data-show-delay="0"
									 data-hide-duration="1000"
									 data-hide-delay="0"
										 <?php endif; ?>
									> 	
									<?php
                                        $img_attr = array('src' => $slide['builder-ps-slide-image'], 'urlonly' => true);
                                        if(!empty($slide['s_c_w'])){
                                            $img_attr['w']=$slide['s_c_w'];
                                        }
                                        if(!empty($slide['s_c_h'])){
                                            $img_attr['h']=$slide['s_c_h'];
                                        }
										$url = themify_get_image($img_attr);
                                        $img_attr=null;
										$alt = esc_attr(Themify_Builder_Model::get_alt_by_url($slide['builder-ps-slide-image'])); 
									?>
										<img data-tf-not-load <?php echo !empty($slide['s_c_w'])?'width="'.$slide['s_c_w'].'"':''; ?><?php echo !empty($slide['s_c_h'])?' height="'.$slide['s_c_h'].'"':''; ?> class="bsp-content-img" <?php if($i===0 && isset($assets_url)):?>data-tf-done="1" src="<?php echo $url; ?>"<?php else:?>data-src="<?php echo $url; ?>"<?php endif;?> alt="<?php echo $alt; ?>">
										<?php if($i>0 || !isset($assets_url)):?>
											<noscript>
												<img data-no-script class="bsp-content-img" src="<?php echo $url; ?>" alt="<?php echo $alt; ?>">
											</noscript>
										<?php endif;?>
									</div>
									<?php 
									if(isset($assets_url)){
										Themify_Builder_Model::loadCssModules('bsp_image',$assets_url.'image.css',$v);
									}
									?>
									<?php endif; ?>
								<div class="sp-slide-text tf_box">
									<?php if (!empty($slide['builder_ps_heading'])) : ?>
									<h3 class="sp-layer bsp-slide-post-title"
									<?php if ('disable' !== $timer_translation[$slide['builder_ps_h3s_timer']]): ?>
										data-show-transition="<?php echo $timer_translation[$slide['builder_ps_h3s_timer']]; ?>"
										data-hide-transition="<?php echo $timer_translation[$slide['builder_ps_h3e_timer']]; ?>"
										data-show-duration="1000"
										data-show-delay="300"
										data-hide-duration="1000"
										data-hide-delay="0"
									<?php endif; ?>
										<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('builder_ps_heading',true,false,'builder_slider_pro_slides',$i); }?>><?php echo $slide['builder_ps_heading']; ?>
									</h3>
									<?php endif; ?>

									<?php if (!empty($slide['builder_ps_text'])) : ?>
									<div class="sp-layer bsp-slide-excerpt"
									<?php if ('disable' !== $timer_translation[$slide['builder_ps_ps_timer']]): ?>
										 data-show-transition="<?php echo $timer_translation[$slide['builder_ps_ps_timer']]; ?>"
										 data-hide-transition="<?php echo $timer_translation[$slide['builder_ps_pe_timer']]; ?>"
										 data-show-duration="1000"
										 data-show-delay="600"
										 data-hide-duration="1000"
										 data-hide-delay="0"
									<?php endif; ?>
										<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('builder_ps_text',true,true,'builder_slider_pro_slides',$i); }?>><?php echo apply_filters('themify_builder_module_content', $slide['builder_ps_text']); ?>
									</div>
									<?php 
									if(isset($assets_url)){
										Themify_Builder_Model::loadCssModules('bsp_excerpt',$assets_url.'excerpt.css',$v);
									}
									?>
									<?php endif; ?>

									<?php
                                    if('' !== $slide['builder_ps_button_text']):
                                    $link_attr = array('class'=>'sp-layer bsp-slide-button');
                                    switch($slide['builder_ps_button_action_type']){
                                        case 'next_slide':
                                            $link_attr['href']='#next-slide';
                                            break;
                                        case 'prev_slide':
                                            $link_attr['href']='#prev-slide';
                                            break;
                                        case 'custom':
                                            $link_attr['href']=esc_url($slide['builder_ps_button_link']);
					    if(isset($slide['builder_ps_new_tab']) && $slide['builder_ps_new_tab']==='yes'){
						$link_attr['target']='_blank';
					    }
                                            break;
                                        case 'lightbox':
                                            $link_attr['href']=esc_url($slide['builder_ps_button_link']);
                                            $link_attr['class'] .= ' themify_lightbox';
                                            if ($slide['l_w'] !== '' || $slide['l_h'] !== '') {
                                                $lightbox_settings = array();
                                                if ($slide['l_w'] !== '') {
                                                    $lightbox_settings[] = $slide['l_w'] . $slide['l_w_unit'];
                                                }
                                                if ($slide['l_h'] !== '') {
                                                    $lightbox_settings[] = $slide['l_h'] . $slide['l_h_unit'];
                                                }
                                                $link_attr['data-zoom-config'] = implode('|', $lightbox_settings);
                                                unset($lightbox_settings);
                                            }
                                            break;
                                    }
									?>
									<?php if (isset($link_attr['href'])) : ?>
									<a <?php echo themify_get_element_attributes($link_attr) ?>
									<?php if ('disable' !== $timer_translation[$slide['builder_ps_as_timer']]): ?>
									   data-show-transition="<?php echo $timer_translation[$slide['builder_ps_as_timer']]; ?>"
									   data-hide-transition="<?php echo $timer_translation[$slide['builder_ps_ae_timer']]; ?>"
									   data-show-duration="1000"
									   data-show-delay="900"
									   data-hide-duration="1000"
									   data-hide-delay="0"
										   <?php endif; ?>
									   >
										<?php if ('' !== $slide['builder_ps_button_icon']): ?>
										<i><?php echo themify_get_icon($slide['builder_ps_button_icon']) ?></i>
										<?php endif; ?>
										<span<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('builder_ps_button_text',true,false,'builder_slider_pro_slides',$i); }?>><?php echo $slide['builder_ps_button_text']; ?></span>
									</a>
									<?php 
										if(isset($assets_url)){
										Themify_Builder_Model::loadCssModules('bsp_button',$assets_url.'button.css',$v);
										}
									?>
									<?php endif; ?>
									<?php endif; ?>
								</div>
								<!-- /sp-slide-text -->
							</div><!-- .sp-slide-wrap -->
						</div><!-- .bsp-layers-overlay -->
					</div><!-- .sp-slide -->
					<?php endforeach; ?>
				</div><!-- .sp-slides -->
			</div>
	    </div>
	    <?php if($i>1 && $fields_args['builder_ps_triggers_position']!=='none' && $fields_args['builder_ps_triggers_type']!=='thumb'):?>
		<div class="sp-buttons tf_rel tf_w tf_textc">
		    <?php for($j=$i;$j>-1;--$j):?>
				<div class="sp-button tf_box tf_tf_inline_block tf_vmiddle<?php if($j===$i):?> sp-selected-button<?php endif;?>"></div>
		    <?php endfor;?>
		</div>
	    <?php endif;?>
	</div><!-- .slider-pro -->
    <?php endif; ?>
    <?php
    $slide_defaults=$timer_translation=null;
    do_action('themify_builder_after_template_content_render');

    // add styles
    if (!empty($styles)) {
	echo "<style>\n";
	foreach ($styles as $style) {
	    if (is_array($style)) {
		echo '.' , $element_id, ' ', implode(',.' . $element_id . ' ', $style), "\n";
	    } else {
		echo '.', $element_id, ' ', $style, "\n";
	    }
	}
	echo '</style>';
    }
    ?>
</div>
<!-- /Slider Pro module -->
