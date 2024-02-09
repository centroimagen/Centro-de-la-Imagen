<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Module Name: Slider Pro
 */

class TB_Pro_Slider_Module extends Themify_Builder_Component_Module {

    public function __construct() {
        if(method_exists('Themify_Builder_Model', 'add_module')){
            parent::__construct('pro-slider');
        }
        else{//backward
             parent::__construct(array(
                'name' =>$this->get_name(),
                'slug' => 'pro-slider',
                'category' =>$this->get_group()
            ));
        }
    }

    public function get_name(){
        return  __('Slider Pro', 'builder-slider-pro');
    }

    public function get_icon(){
	return 'layout-slider';
    }

    public function get_group() {
        return array('addon');
    }

    public function get_assets() {
		$url=Builder_Pro_Slider::$url . 'assets/';
        return array(
            'css' => $url. 'style',
	    'js' => $url. 'scripts',
	    'async'=>true,
            'ver' => Builder_Pro_Slider::$version,
	    'url' => $url
	    );
    }

    public function get_options() {
	$speed =array(
		    'fast' => __('Fast', 'builder-slider-pro'),
		    'normal' => __('Normal', 'builder-slider-pro'),
		    'slow' => __('Slow', 'builder-slider-pro')
		);
	$timer = array(
		    // 'fade' => __('Fade', 'builder-slider-pro'),
		    'disable' => __('Disable', 'builder-slider-pro'),
		    'shortTop' => __('From Top', 'builder-slider-pro'),
		    'shortBottom' => __('From Bottom', 'builder-slider-pro'),
		    'shortLeft' => __('From Left', 'builder-slider-pro'),
		    'shortRight' => __('From Right', 'builder-slider-pro'),
		);
	$timer2 = array(
		    // 'fadeOut' => __('Fade Out', 'builder-slider-pro'),
		    'shortTopOut' => __('To Top', 'builder-slider-pro'),
		    'shortBottomOut' => __('To Bottom', 'builder-slider-pro'),
		    'shortLeftOut' => __('To Left', 'builder-slider-pro'),
		    'shortRightOut' => __('To Right', 'builder-slider-pro'),
		);
        return array(
            array(
                'id' => 'mod_title_slider',
                'type' => 'title'
            ),
            array(
                'id' => 'builder_slider_pro_slides',
                'type' => 'builder',
                'options' => array(
                    array(
                        'id' => 'builder_ps_layout',
                        'type' => 'layout',
                        'mode'=>'sprite',
                        'label' => __('Slide Layout', 'builder-slider-pro'),
                        'options' => array(
                            array('img' => 'image_left', 'value' => 'bsp-slide-content-left', 'label' => __(' Text Right', 'builder-slider-pro')),
                            array('img' => 'image_center', 'value' => 'bsp-slide-content-center', 'label' => __('Text Center', 'builder-slider-pro')),
                            array('img' => 'image_right', 'value' => 'bsp-slide-content-right', 'label' => __('Text Left', 'builder-slider-pro')),
                        )
                    ),
                    array(
                        'id' => 'builder_ps_slide_type',
                        'type' => 'select',
                        'label' => __('Background Type', 'builder-slider-pro'),
                        'options' => array(
                            'Image' => __('Image', 'builder-slider-pro'),
                            'Video' => __('Video', 'builder-slider-pro'),
                        ),
                        'option_js' => true,
                        'binding' => array(
                            'Image' => array(
                                'show' => 'tb_group_element_Image',
                                'hide' =>'tb_group_element_Video'
							),
                            'Video' => array(
                                'show' => 'tb_group_element_Video',
                                'hide' => 'tb_group_element_Image'
							),
                        ),
                    ),
                    array(
                        'type' => 'multi',
                        'label' => __('Slide Background', 'builder-slider-pro'),
                        'options' => array(
                            array(
                                'id' => 'builder-ps-bg-color',
                                'type' => 'color'
                            ),
                            array(
                                'id' => 'builder-ps-bg-image',
                                'type' => 'image',
                                'class' => 'large',
                                'wrap_class' => 'tb_group_element_Image'
                            )
                        )
                    ),
                    array(
                        'id' => 'builder_ps_vbg_option',
                        'type' => 'url',
                        'label' => __('Video URL', 'builder-slider-pro'),
                        'help' => __('YouTube, Vimeo, etc. video <a href="https://themify.me/docs/video-embeds" target="_blank">embed link</a>', 'builder-slider-pro'),
                        'wrap_class' => 'tb_group_element_Video'
                    ),
                    array(
                        'id' => 'builder-ps-slide-image',
                        'type' => 'image',
                        'label' => __('Slide Content Image', 'builder-slider-pro'),
                        'help' => __('Image will appear on the right/left side (depending of slide layout) Slider container', 'builder-slider-pro')
                    ),
                    array(
                        'type' => 'multi',
                        'label' => __('Image Size', 'builder-slider-pro'),
                        'options' => array(
                            array(
                                'id' => 's_c_w',
                                'type' => 'number',
                                'label' => __('Width', 'builder-slider-pro'),
                                'after' => 'px'
                            ),
                            array(
                                'id' => 's_c_h',
                                'type' => 'number',
                                'label' => __('Height', 'builder-slider-pro'),
                                'after' =>'px'
                            ),
                        )
                    ),
                    array(
                        'id' => 'builder_ps_heading',
                        'type' => 'text',
                        'label' => __('Slide Heading', 'builder-slider-pro'),
                        'control' => array(
                            'selector'=>'.bsp-slide-post-title',
							'rep'=>'.sp-slide'
                        )
                    ),
                    array(
                        'id' => 'builder_ps_text',
                        'type' => 'wp_editor',
                        'control' => array(
                            'selector'=>'.bsp-slide-excerpt',
							'rep'=>'.sp-slide'
                        )
                    ),
                    array(
                        'type' => 'multi',
                        'label' => __('Slide Text', 'builder-slider-pro'),
                        'options' => array(
                            array(
                                'id' => 'builder_ps_text_color',
                                'type' => 'color',
                                'label' => __('Color', 'builder-slider-pro')
                            ),
                            array(
                                'id' => 'builder_ps_text_link_color',
                                'type' => 'color',
                                'label' => __('Link Color', 'builder-slider-pro')
                            )
                        )
                    ),
                    array(
						'type' => 'group',
						'display' => 'accordion',
						'label' => __('Button', 'builder-slider-pro'),
						'options' => array(
							array(
								'id' => 'builder_ps_button_text',
								'type' => 'text',
								'label' => __('Button Text', 'builder-slider-pro'),
								'control' => array(
									'selector' => '.bsp-slide-button'
								)
							),
							array(
								'id' => 'builder_ps_button_action_type',
								'type' => 'select',
								'label' => __('Button Action', 'builder-slider-pro'),
								'options' => array(
									'custom' => __('Go to link', 'builder-slider-pro'),
									'next_slide' => __('Next slide', 'builder-slider-pro'),
									'prev_slide' => __('Previous slide', 'builder-slider-pro'),
									'lightbox' => __('Lightbox link', 'builder-slider-pro'),
								),
								'binding' => array(
									'custom' => array(
										'show' => array('builder_ps_button_link','builder_ps_new_tab'),
										'hide' => 'tb_group_element_lightbox'
									),
									'lightbox' => array(
										'show' => array('builder_ps_button_link','tb_group_element_lightbox')
									),
									'not_empty' => array(
										'hide' =>array('builder_ps_button_link','tb_group_element_lightbox','builder_ps_new_tab')
									)
								),
							),
							array(
								'id' => 'builder_ps_button_link',
								'type' => 'url',
								'label' => __('Link', 'builder-slider-pro')
							),
							array(
								'id' => 'builder_ps_new_tab',
								'type' => 'toggle_switch',
								'label' => __('Open Link In New Tab', 'builder-slider-pro'),
								'options' => 'simple'
							),
							array(
								'type' => 'multi',
								'label' => __( 'Lightbox Dimension', 'builder-slider-pro' ),
								'options' => array(
									array(
										'id' => 'l_w',
										'type' => 'range',
										'label' => 'w',
										'control' => false,
										'units' => array(
											'px' => array(
												'max' => 3000
											),
											'%' => ''
										)
									),
									array(
										'id' => 'l_h',
										'label' => 'ht',
										'control' => false,
										'type' => 'range',
										'units' => array(
											'px' => array(
												'max' => 3000
											),
											'%' => ''
										)
									)
								),
								'wrap_class' => 'tb_group_element_lightbox'
							),
							array(
								'type' => 'multi',
								'label' => __('Button', 'builder-slider-pro'),
								'options' => array(
									array(
										'id' => 'builder_ps_button_icon',
										'type' => 'icon',
										'label' => __('Icon', 'builder-slider-pro')
									),
									array(
										'id' => 'builder_ps_button_color',
										'type' => 'color',
										'label' => __('Color', 'builder-slider-pro')
									),
									array(
										'id' => 'builder_ps_button_bg',
										'type' => 'color',
										'label' => __('Background', 'builder-slider-pro')
									)
								)
							),
						),
					),
					array(
						'type' => 'group',
						'display' => 'accordion',
						'label' => __('Transitions', 'builder-slider-pro'),
						'options' => array(
							array(
								'type' => 'multi',
								'label' => __('Slide Transition', 'builder-slider-pro'),
								'options' => array(
									array(
										'id' => 'builder_ps_tranzition',
										'type' => 'select',
										'options' => array(
											'slideTop' => __('Slide to Top', 'builder-slider-pro'),
											'slideBottom' => __('Slide to Bottom', 'builder-slider-pro'),
											'slideLeft' => __('Slide to Left', 'builder-slider-pro'),
											'slideRight' => __('Slide to Right', 'builder-slider-pro'),
											'slideTopFade' => __('Fade and Slide from Top', 'builder-slider-pro'),
											'slideBottomFade' => __('Fade and Slide from Bottom', 'builder-slider-pro'),
											'slideLeftFade' => __('Fade and Slide from Left', 'builder-slider-pro'),
											'slideRightFade' => __('Fade and Slide from Right', 'builder-slider-pro'),
											'fade' => __('Fade', 'builder-slider-pro'),
											'zoomOut' => __('Zoom', 'builder-slider-pro'),
											'zoomTop' => __('Zoom and slide from Top', 'builder-slider-pro'),
											'zoomBottom' => __('Zoom and slide from Bottom', 'builder-slider-pro'),
											'zoomLeft' => __('Zoom and slide from Left', 'builder-slider-pro'),
											'zoomRight' => __('Zoom and slide from Right', 'builder-slider-pro'),
										)
									),
									array(
										'id' => 'builder_ps_tranzition_duration',
										'type' => 'select',
										'options' => $speed
									)
								)
							),
							self::get_seperator(),
							array(
								'type' => 'multi',
								'label' =>__('Slide Title', 'builder-slider-pro'),
								'options' => array(
									array(
										'id' => 'builder_ps_h3s_timer',
										'type' => 'select',
										'tooltip' => __('start Animation ', 'builder-slider-pro'),
										'options' => $timer,
										'binding' => array(
											'disable' => array(
												'hide' => 'builder_ps_h3e_timer'
											),
											 'not_empty' => array(
												'show' => 'builder_ps_h3e_timer'
											)
										),
									),
									array(
										'id' => 'builder_ps_h3e_timer',
										'type' => 'select',
										'tooltip' => __('End Animation ', 'builder-slider-pro') ,
										'options' =>$timer2
									),
								)
							),
							array(
								'type' => 'multi',
								'label' => __('Slide Text', 'builder-slider-pro'),
								'options' => array(
									array(
										'id' => 'builder_ps_ps_timer',
										'type' => 'select',
										'tooltip' => __('start Animation ', 'builder-slider-pro'),
										'options' => $timer,
										'binding' => array(
											'disable' => array(
												'hide' =>'builder_ps_pe_timer'
											),
											'not_empty' => array(
												'show' =>'builder_ps_pe_timer'
											)
										)
									),
									array(
										'id' => 'builder_ps_pe_timer',
										'type' => 'select',
										'tooltip' => __('End Animation ', 'builder-slider-pro') ,
										'options' =>$timer2
									),
								)
							),
							array(
								'type' => 'multi',
								'label' => __('Slide Action Button', 'builder-slider-pro'),
								'options' => array(
									array(
										'id' => 'builder_ps_as_timer',
										'type' => 'select',
										'tooltip' => __('start Animation ', 'builder-slider-pro'),
										'options' =>$timer,
										'binding' => array(
											'disable' => array(
												'hide' => 'builder_ps_ae_timer'
											),
											'not_empty' => array(
												'show' => 'builder_ps_ae_timer'
											)
										)
									),
									array(
										'id' => 'builder_ps_ae_timer',
										'type' => 'select',
										'tooltip' => __('End Animation ', 'builder-slider-pro') ,
										'options' => $timer2
									),
								)
							),
							array(
								'type' => 'multi',
								'label' => __('Slide Content Image', 'builder-slider-pro'),
								'options' => array(
									array(
										'id' => 'builder_ps_imgs_timer',
										'type' => 'select',
										'tooltip' => __('start Animation ', 'builder-slider-pro'),
										'options' => $timer,
										'binding' => array(

											'disable' => array(
												'hide' => 'builder_ps_imge_timer'
											),
											'not_empty' => array(
												'show' =>'builder_ps_imge_timer'
											)
										)
									),
									array(
										'id' => 'builder_ps_imge_timer',
										'type' => 'select',
										'tooltip' => __('End Animation ', 'builder-slider-pro') ,
										'options' => $timer2
									),
								)
							),
						),
					),
                )
            ),
            array(
				'type' => 'group',
				'label' => __('Slider Options', 'builder-slider-pro'),
				'display' => 'accordion',
				'options' => array(
					array(
						'id' => 'builder_ps_triggers_position',
						'type' => 'radio',
						'label' => __('Slider Pager', 'builder-slider-pro'),
						'wrap_class' => 'tb_compact_radios',
						'options' => array(
							array('value'=>'standard','name'=>__('Default (overlap)', 'builder-slider-pro')),
							array('value'=>'below','name'=>__('Below', 'builder-slider-pro')),
							array('value'=>'none','name'=>__('No pager', 'builder-slider-pro'))
						)
					),
					array(
						'id' => 'builder_ps_wrap',
						'label' => __('Wrap', 'builder-slider-pro'),
						'type' => 'toggle_switch',
						'options' => 'simple',
						'default' => 'off',
					),
					array(
						'id' => 'builder_ps_triggers_type',
						'type' => 'radio',
						'label' => __('Pager Design', 'builder-slider-pro'),
						'options' => array(
							array('value'=>'circle','name'=>__('Circle', 'builder-slider-pro')),
							array('value'=>'thumb','name'=>__('Photo Thumb', 'builder-slider-pro')),
							array('value'=>'square','name'=>__('Square', 'builder-slider-pro'))
						),
						'wrap_class' => 'tb_compact_radios',
						'option_js' => true
					),
					array(
						'type' => 'multi',
						'label' => __('Thumbnail Size', 'builder-slider-pro'),
						'options' => array(
							array(
								'id' => 'builder_ps_thumb_width',
								'type' => 'number',
								'label' => __('Thumbnail Width', 'builder-slider-pro'),
								'after' => 'px',
								'class' => 'medium'
							),
							array(
								'id' => 'builder_ps_thumb_height',
								'type' => 'number',
								'label' => __('Thumbnail Height', 'builder-slider-pro'),
								'after' =>'px',
								'class' => 'medium'
							),
						),
						'wrap_class' => 'tb_group_element_thumb'
					),
					array(
						'id' => 'builder_ps_aa',
						'type' => 'select',
						'label' => __('Auto Slide', 'builder-slider-pro'),
						'options' => array(
							'off' => __('Off', 'builder-slider-pro'),
							2000 => __('2 Seconds', 'builder-slider-pro'),
							3000 => __('3 Seconds', 'builder-slider-pro'),
							4000 => __('4 Seconds', 'builder-slider-pro'),
							5000 => __('5 Seconds', 'builder-slider-pro'),
							6000 => __('6 Seconds', 'builder-slider-pro'),
							7000 => __('7 Seconds', 'builder-slider-pro'),
							8000 => __('8 Seconds', 'builder-slider-pro'),
							9000 => __('9 Seconds', 'builder-slider-pro'),
							10000 => __('10 Seconds', 'builder-slider-pro'),
							11000 => __('11 Seconds', 'builder-slider-pro'),
							12000 => __('12 Seconds', 'builder-slider-pro'),
							13000 => __('13 Seconds', 'builder-slider-pro'),
							14000 => __('14 Seconds', 'builder-slider-pro'),
							15000 => __('15 Seconds', 'builder-slider-pro'),
						),
						'binding' => array(
							'off' => array(
								'hide' => 'pause_last_slide'
							),
							'select' => array(
								'value' => array(2000,3000,4000,5000,6000,7000,8000,9000,10000,11000,12000,13000,14000,15000),
								'show' => 'pause_last_slide'
							)
						),
					),
					array(
						'id' => 'pause_last_slide',
						'type' => 'checkbox',
						'label' => '',
						'options' => array(
							array('name' => 'yes', 'value' => __('Pause on last slide', 'builder-slider-pro')),
						)
					),
					array(
						'id' => 'builder_ps_hover_pause',
						'type' => 'select',
						'label' => __('On Hover', 'builder-slider-pro'),
						'options' => array(
							'none' => __('Continue autoplay', 'builder-slider-pro'),
							'pause' => __('Pause autoplay', 'builder-slider-pro'),
							'stop' => __('Stop autoplay', 'builder-slider-pro'),
						)
					),
					array(
						'id' => 'builder_ps_timer',
						'type' => 'checkbox',
						'label' => '',
						'options' => array(
							array('name' => 'yes', 'value' => __('Show timer bar', 'builder-slider-pro')),
						)
					),
					array(
						'type' => 'multi',
						'label' => __('Slider Size', 'builder-slider-pro'),
						'wrap_class' => 'tb_checkbox_element tb_checkbox_element_fullscreen',
						'options' => array(
							array(
								'id' => 'builder_ps_width',
								'type' => 'number',
								'label' => __('Slider Width', 'builder-slider-pro'),
								'after' => 'px'
							),
							array(
								'id' => 'builder_ps_height',
								'type' => 'number',
								'label' => __('Slider Height', 'builder-slider-pro'),
								'after' =>'px'
							),
					),
						'help'=> __('Default slider is auto fullwidth, so it displays the slider fullwidth and scales it proportionally. To achieve custom dimension, enter the slider width and height (e.g. enter width=1160px and height=600px).', 'builder-slider-pro')
					),
				array(
					'id' => 'builder_ps_fullscreen',
					'type' => 'checkbox',
					'label' => '',
					'options' => array(
						array('name' => 'fullscreen', 'value' => __('Enable fullscreen slider (100% width & height)', 'builder-slider-pro')),
					),
					'option_js' => true,
					'reverse' => true
				),
				array(
					'id' => 'touch_swipe_desktop',
					'type' => 'toggle_switch',
					'label' => __('Desktop Swipe', 'builder-slider-pro'),
					'options' => array(
						'on' => array( 'name' => 'yes', 'value' => 'en' ),
						'off' => array( 'name' => 'no', 'value' => 'dis' ),
					),
					'help'=> __( 'Swipe allows viewer to slide left/right by dragging the slider using mouse', 'builder-slider-pro' ),
				),
				array(
					'id' => 'touch_swipe_mob',
					'type' => 'toggle_switch',
					'label' => __('Mobile Swipe', 'builder-slider-pro'),
					'options' => array(
						'on' => array( 'name' => 'yes', 'value' => 'en'),
						'off' => array( 'name' => 'no', 'value' =>'dis'),
					),
					'help'=> __( 'Swipe allows viewer to slide left/right by swiping on touch display', 'builder-slider-pro' ),
				),
				),
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_slider_pro' ),

        );
    }

    public function get_live_default() {
        return array(
            'builder_ps_triggers_position' => 'standard',
            'builder_ps_triggers_type' => 'circle',
            'builder_ps_aa' => 'off',
            'pause_last_slide' => '',
            'builder_ps_hover_pause' => 'pause',
            'builder_ps_timer' => 'no',
            'builder_ps_thumb_width' => 30,
            'builder_ps_thumb_height' => 30,
            'builder_slider_pro_slides' => array(array(
                    'builder_ps_layout' => 'bsp-slide-content-right',
                    'builder_ps_slide_type' => 'Image',
                    'builder_ps_text_color' => 'ffffff_1',
                    'builder-ps-bg-image' => 'https://themify.me/demo/themes/themes/wp-content/uploads/addon-samples/slider-pro-bg-image.jpg',
                    'builder-ps-slide-image' => 'https://themify.me/demo/themes/themes/wp-content/uploads/addon-samples/slider-pro-content-image.png',
                    'builder_ps_heading' => esc_html__('Slide Heading', 'builder-slider-pro'),
                    'builder_ps_text' => esc_html__('Slide content', 'builder-slider-pro'),
                    'builder_ps_tranzition' => 'slideTop',
                    'builder_ps_tranzition_duration' => 'normal',
                    'builder_ps_h3s_timer' => 'shortTop',
                    'builder_ps_h3e_timer' => 'shortTop',
                    'builder_ps_ps_timer' => 'shortTop',
                    'builder_ps_pe_timer' => 'shortTop',
                    'builder_ps_as_timer' => 'shortTop',
                    'builder_ps_ae_timer' => 'shortTop',
                    'builder_ps_imgs_timer' => 'shortTop',
                    'builder_ps_imge_timer' => 'shortTop',
                )),
			'touch_swipe_mob' => 'yes',
			'touch_swipe_desktop' => 'yes',
        );
    }

    public function get_styling() {
	    $general = array(
		// Background
		self::get_expand('bg', array(
		   self::get_tab(array(
		       'n' => array(
			   'options' => array(
				self::get_image()
			   )
		       ),
		       'h' => array(
			   'options' => array(
			       self::get_image('', 'b_i','bg_c','b_r','b_p', 'h')
			   )
		       )
		   ))
	       )),
	       // Padding
	       self::get_expand('p', array(
		   self::get_tab(array(
		       'n' => array(
			   'options' => array(
			       self::get_padding()
			   )
		       ),
		       'h' => array(
			   'options' => array(
			       self::get_padding('', 'p', 'h')
			   )
		       )
		   ))
	       )),
	       // Margin
	       self::get_expand('m', array(
		   self::get_tab(array(
		       'n' => array(
			   'options' => array(
			       self::get_margin()
			   )
		       ),
		       'h' => array(
			   'options' => array(
			       self::get_margin('', 'm', 'h')
			   )
		       )
		   ))
	       )),
	       // Border
	       self::get_expand('b', array(
		   self::get_tab(array(
		       'n' => array(
			   'options' => array(
			       self::get_border()
			   )
		       ),
		       'h' => array(
			   'options' => array(
			       self::get_border('', 'b', 'h')
			   )
		       )
		   ))
	       )),
			// Width
			self::get_expand('w', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_width('', 'w')
						)
					),
					'h' => array(
						'options' => array(
							self::get_width('', 'w', 'h')
						)
					)
				))
			)),
				// Height & Min Height
				self::get_expand('ht', array(
						self::get_height(),
						self::get_min_height(),
						self::get_max_height()
					)
				),
			// Rounded Corners
			self::get_expand('r_c', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_border_radius()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius('', 'r_c', 'h')
							)
						)
					))
				)
			),
			// Shadow
			self::get_expand('sh', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_box_shadow()
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow('', 'sh', 'h')
							)
						)
					))
				)
			),
			// Display
			self::get_expand('disp', self::get_display())
        );

		$slide_text_contr = array(
		   self::get_tab(array(
		       'n' => array(
			   'options' => array(
				self::get_color(' .sp-slide-text', 's_t_c_b_c','bg_c', 'background-color'),
				self::get_padding(' .sp-slide-text','s_t_c_p'),
				self::get_border(' .sp-slide-text','s_t_c_b')
			   )
		       ),
		       'h' => array(
			   'options' => array(
				self::get_color(' .sp-slide-text', 's_t_c_b_c','bg_c', 'background-color','h'),
				self::get_padding(' .sp-slide-text','s_t_c_p','h'),
				self::get_border(' .sp-slide-text','s_t_c_b','h')
			   )
		       )
	       )),
		);
		$slide_title = array(
		   self::get_tab(array(
		       'n' => array(
			   'options' => array(
				self::get_font_family('.module .bsp-slide-post-title', 'title_font_family'),
				self::get_color('.module .sp-slide-text .bsp-slide-post-title' ,'f_c_title'),
				self::get_font_size('.module .bsp-slide-post-title', 'font_size_title'),
				self::get_line_height('.module .bsp-slide-post-title', 'title_line_height'),
				self::get_letter_spacing('.module .bsp-slide-post-title', 'letter_spacing_title'),
				self::get_text_align('.module .bsp-slide-post-title', 't_a_title'),
				self::get_text_transform('.module .bsp-slide-post-title', 'text_transform_title'),
				self::get_font_style('.module .bsp-slide-post-title', 'font_style_title','font_style_blod_title'),
               self::get_text_shadow('.module .bsp-slide-post-title', 't_sh_s_t'),
			   )
		       ),
		       'h' => array(
			   'options' => array(
				self::get_font_family('.module .bsp-slide-post-title', 't_f_f','h'),
				self::get_color('.module .sp-slide-text .bsp-slide-post-title' ,'f_c_t',null,null,'h'),
				self::get_font_size('.module .bsp-slide-post-title', 'f_s_t','','h'),
				self::get_line_height('.module .bsp-slide-post-title', 't_l_h','h'),
				self::get_letter_spacing('.module .bsp-slide-post-title', 'l_s_t','h'),
				self::get_text_align('.module .bsp-slide-post-title', 't_a_t','h'),
				self::get_text_transform('.module .bsp-slide-post-title', 't_t_t','h'),
				self::get_font_style('.module .bsp-slide-post-title', 'f_st_t','f_s_b_t','h'),
               self::get_text_shadow('.module .bsp-slide-post-title', 't_sh_s_t','h'),
			   )
		       )
	       )),
		);
		$slide_text = array(
		   self::get_tab(array(
		       'n' => array(
			   'options' => array(
				self::get_font_family(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'text_font_family'),
				self::get_color(array(' .bsp-slide-excerpt', '.module .bsp-slide-excerpt p', ' .bsp-slide-excerpt h1', ' .bsp-slide-excerpt h2', ' .bsp-slide-excerpt h3', ' .bsp-slide-excerpt h4', ' .bsp-slide-excerpt h5', ' .bsp-slide-excerpt h6'),'f_c_text'),
				self::get_font_size(array(' .bsp-slide-excerpt'), 'text_font_size'),
				self::get_line_height(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'text_line_height'),
				self::get_letter_spacing(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'letter_spacing_text'),
				self::get_text_align(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 't_a_text'),
				self::get_text_transform(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'text_transform_text'),
				self::get_font_style(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'font_style_text','font_style_blod_text'),
               self::get_text_shadow(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 't_sh_s_e'),
			   )
		       ),
		       'h' => array(
			   'options' => array(
				self::get_font_family(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'te_f_f','h'),
				self::get_color(array(' .bsp-slide-excerpt', '.module .bsp-slide-excerpt p', ' .bsp-slide-excerpt h1', ' .bsp-slide-excerpt h2', ' .bsp-slide-excerpt h3', ' .bsp-slide-excerpt h4', ' .bsp-slide-excerpt h5', ' .bsp-slide-excerpt h6'),'f_c_te',null,null,'h'),
				self::get_font_size(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'te_f_s','','h'),
				self::get_line_height(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'te_l_h','h'),
				self::get_letter_spacing(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'l_s_te','h'),
				self::get_text_align(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 't_a_te','h'),
				self::get_text_transform(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 't_t_te','h'),
				self::get_font_style(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 'f_st_te','f_s_b_te','h'),
               self::get_text_shadow(array(' .bsp-slide-excerpt', ' .bsp-slide-excerpt p'), 't_sh_s_e','h'),
			   )
		       )
	       )),
		);

        $controls = array(
		self::get_expand(__('Timer Bar', 'builder-slider-pro'), array(
		    self::get_tab(array(
			'n' => array(
			    'options' => array(
			       self::get_color(' .bsp-timer-bar', 'timer_bar_background_color', 'bg_c', 'background-color')
			    )
			),
			'h' => array(
			    'options' => array(
			       self::get_color(' .bsp-timer-bar', 't_b_b_c', 'bg_c', 'background-color','h')
			    )
			)
		    ))
		)),
		//Arrow
		self::get_expand(__('Arrow', 'builder-slider-pro'), array(
		    self::get_tab(array(
			'n' => array(
			    'options' => array(
				self::get_color(' .sp-arrow', 'b_c_arrow','bg_c', 'background-color'),
				self::get_color(' .sp-arrow', 'color'),
				self::get_padding(' .sp-arrow','p_arrow')
			    )
			),
			'h' => array(
			    'options' => array(
				self::get_color(' .sp-arrow', 'b_c_a','bg_c', 'background-color','h'),
				self::get_color(' .sp-arrow', 'c',null,null,'h'),
				self::get_padding(' .sp-arrow','p_a','h')
			    )
			)
		    ))
		)),
		 //Pagination
		self::get_expand(__('Pagination', 'builder-slider-pro'), array(
		    self::get_tab(array(
			'n' => array(
			    'options' => array(
				self::get_color('  .sp-button:not(.sp-selected-button)', 'pagination_color'),
				self::get_color('  .sp-selected-button', 'pagination_active_color', __('Active Color', 'builder-slider-pro'))
			    )
			),
			'h' => array(
			    'options' => array(
			       self::get_color('  .sp-button', 'p_c',null,null,'h')
			    )
			)
		    ))
		))
	    );
        $action_button = array(
		    self::get_expand('bg', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_color(' .bsp-slide-button', 'b_c_b', 'bg_c', 'background-color'),
				    self::get_color(' .bsp-slide-button', 'c_b' )
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_color(' .bsp-slide-button', 'b_c_b', 'bg_c', 'background-color','h'),
				    self::get_color(' .bsp-slide-button', 'c_b',null,null,'h' )
				)
			    )
			))
		    )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(' .bsp-slide-button', 'button_font_family'),
				    self::get_font_size(' .bsp-slide-button', 'font_size_button'),
				    self::get_line_height(' .bsp-slide-button', 'line_height_button'),
				    self::get_letter_spacing(' .bsp-slide-button', 'l_s_b'),
				    self::get_text_transform(' .bsp-slide-button', 't_t_b'),
				    self::get_font_style(' .bsp-slide-button', 'f_sy_b','f_b_b'),
					self::get_text_align(' .bsp-slide-button', 't_a_btn'),
					self::get_text_shadow(' .bsp-slide-button', 't_sh_a_b'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(' .bsp-slide-button', 'b_f_f','h'),
				    self::get_font_size(' .bsp-slide-button', 'f_s_b','','h'),
				    self::get_line_height(' .bsp-slide-button', 'l_h_b','h'),
				    self::get_letter_spacing(' .bsp-slide-button', 'l_s_b','h'),
				    self::get_text_transform(' .bsp-slide-button', 't_t_b','h'),
				    self::get_font_style(' .bsp-slide-button', 'f_sy_b','f_b_b','h'),
					self::get_text_align(' .bsp-slide-button', 't_a_btn','h'),
					self::get_text_shadow(' .bsp-slide-button', 't_sh_a_b','h'),
				)
			    )
			))
		    )),
		    // Padding
		   self::get_expand('p', array(
		       self::get_tab(array(
			   'n' => array(
			       'options' => array(
				  self::get_padding(' .bsp-slide-button','p_b')
			       )
			   ),
			   'h' => array(
			       'options' => array(
				   self::get_padding(' .bsp-slide-button','p_b','h')
			       )
			   )
		       ))
		   )),
		   // Margin
		   self::get_expand('m', array(
		       self::get_tab(array(
			   'n' => array(
			       'options' => array(
				   self::get_margin(' .bsp-slide-button','margin_button')
			       )
			   ),
			   'h' => array(
			       'options' => array(
				   self::get_margin(' .bsp-slide-button','m_b','h')
			       )
			   )
		       ))
		   )),
		   // Border
		   self::get_expand('b', array(
		       self::get_tab(array(
			   'n' => array(
			       'options' => array(
				    self::get_border(' .bsp-slide-button','b_b')
			       )
			   ),
			   'h' => array(
			       'options' => array(
				   self::get_border(' .bsp-slide-button','b_b','h')
			       )
			   )
		       ))
		   )),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .bsp-slide-button', 'a_b_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .bsp-slide-button', 'a_b_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .bsp-slide-button', 'a_b_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .bsp-slide-button', 'a_b_b_sh', 'h')
						)
					)
				))
			))
		);

		return array(
				'type' => 'tabs',
				'options' => array(
					'g' => array(
						'options' => $general
					),
					'm_t' => array(
						'options' => $this->module_title_custom_style()
					),
					'a' => array(
						'label' => __('Action Button', 'builder-slider-pro'),
						'options' => $action_button
					),
					's_t_cr' => array(
						'label' => __('Slide Text Container', 'builder-slider-pro'),
						'options' => $slide_text_contr
					),
					's_ti' => array(
						'label' => __('Slide Title', 'builder-slider-pro'),
						'options' => $slide_title
					),
					's_tx' => array(
						'label' => __('Slide Text', 'builder-slider-pro'),
						'options' => $slide_text
					),
					'c' => array(
						'label' => __('Slider Controls', 'builder-slider-pro'),
						'options' => $controls
					),
				)
		);
    }

    public function get_animation() {
        return false;
    }

}
if(method_exists('Themify_Builder_Model', 'add_module')){
    new TB_Pro_Slider_Module();
}
else{
    Themify_Builder_Model::register_module('TB_Pro_Slider_Module');
}
