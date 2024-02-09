<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Mosaic
 */
class TB_Mosaic_Module extends Themify_Builder_Component_Module {
    
	public function __construct() {
            if(method_exists('Themify_Builder_Model', 'add_module')){
                parent::__construct('mosaic');
            }
            else{//backward
                 parent::__construct(array(
                    'name' =>$this->get_name(),
                    'slug' => 'mosaic',
                    'category' =>$this->get_group()
                ));
            }
	}
        
        public function get_name(){
            return __('Mosaic', 'builder-mosaic');
        }
        
        public function get_icon(){
            themify_get_icon('layout-column3','ti');
            themify_get_icon('angle-down','ti');
	    return 'view-grid';
	}
        
	public function get_group() {
            return array('addon');
        }
        
	public function get_assets() {
		$url=Builder_Mosaic::$url . 'assets/';
		return array(
			'async'=>true,
			'css' => $url . 'styles',
			'js' => $url . 'scripts',
			'ver'=>Builder_Mosaic::get_version(),
			'url' => $url
		);
	}
        
	public function get_options() {
		$providers = Builder_Data_Provider::get_providers();
		$providers_settings = $providers_list = array();
		foreach ( $providers as $id => $instance ) {
			$providers_list[ $id ] = $instance->get_label();

			$providers_settings[] = array(
				'type' => 'group',
				'options' => $instance->get_options(),
				'wrap_class' => 'group_' . $id,
			);
		}
		$presets = include( Builder_Mosaic::$dir . 'sample/presets.php' );
		return array(
			array(
				'id' => 'mod_title',
				'type' => 'title'
			),
			self::get_seperator( __( 'Template', 'builder-mosaic' )),
			array(
				'id' => 'template',
				'type' => 'tile_grid',
				'options'=>$presets
			),
			array(
				'id' => 'tiled_posts_display',
				'type' => 'select',
				'label' => __( 'Content Display', 'builder-mosaic' ),
				'options' => $providers_list,
			),
			array(
				'type' => 'group',
				'options' => $providers_settings,
				'wrap_class' => 'tb_mosaic_tabs'
			),
			array(
				'id' => 'hide_empty',
				'type' => 'toggle_switch',
				'label' => __('Hide Empty Module', 'builder-mosaic'),
				'help' => __('Hide the module when there is no items to show.', 'builder-mosaic'),
				'options' => 'simple',
			),
			array(
				'type' => 'group',
				'display' => 'accordion',
				'label' => __( 'Appearance', 'builder-mosaic' ),
				'options' => array(
					array(
						'id' => 'show_as',
						'type' => 'radio',
						'label' => __( 'Mosaic Style', 'builder-mosaic' ),
						'options' => array(
							array( 'name' => __( 'Grid', 'builder-mosaic' ), 'value'=> 'grid' ),
							array( 'name' => __( 'Slider', 'builder-mosaic' ), 'value' => 'slider' ),
						),
						'option_js' => true
					),
					array(
						'id' => 'slides_count',
						'type' => 'number',
						'label' => __( 'Number of Slides', 'builder-mosaic' ),
						'help' => __( 'Maximum number slides displayed in the slider.', 'builder-mosaic' ),
						'wrap_class' => 'tb_group_element_slider'
					),
					array(
						'id' => 'min_width',
						'type' => 'number',
						'label' => __( 'Mobile Trigger Point', 'builder-mosaic' ),
						'after' => '(px)',
						'help' => __( 'Switch to one-column mode if screen size gets smaller than this value.', 'builder-mosaic' )
					),
					array(
						'id' => 'gutter',
						'type' => 'number',
						'label' => __( 'Gutter', 'builder-mosaic' ),
						'after' => '(px)'
					),
					array(
						'id' => 'base_height',
						'type' => 'number',
						'label' => __( 'Base Height', 'builder-mosaic' ),
						'class' => 'xsmall',
						'after' => __( '(px)', 'builder-mosaic' ),
					),
					array(
						'id' => 'effect',
						'type' => 'select',
						'label' => __( 'Hover Effect', 'builder-mosaic' ),
						'options' => array(
							'lily' => __( 'Lily', 'builder-mosaic' ),
							'sadie' => __( 'Sadie', 'builder-mosaic' ),
							'roxy' => __( 'Roxy', 'builder-mosaic' ),
							'bubba' => __( 'Bubba', 'builder-mosaic' ),
							'romeo' => __( 'Eomeo', 'builder-mosaic' ),
							'layla' => __( 'Layla', 'builder-mosaic' ),
							'honey' => __( 'Honey', 'builder-mosaic' ),
							'oscar' => __( 'Oscar', 'builder-mosaic' ),
							'marley' => __( 'Marley', 'builder-mosaic' ),
							'ruby' => __( 'Ruby', 'builder-mosaic' ),
							'milo' => __( 'Milo', 'builder-mosaic' ),
							'dexter' => __( 'Dexter', 'builder-mosaic' ),
							'sarah' => __( 'Sarah', 'builder-mosaic' ),
							'zoe' => __( 'Zoe', 'builder-mosaic' ),
							'chico' => __( 'Chico', 'builder-mosaic' ),
						)
					),
					array(
						'id' => 'entrance_effect',
						'type' => 'select',
						'label' => __( 'Entrance Effect', 'builder-mosaic' ),
						'options' => array(
							'fadeIn' => __( 'FadeIn', 'builder-mosaic' ),
							'fadeInUp' => __( 'fadeInUp', 'builder-mosaic' ),
							'fadeInLeft' => __( 'fadeInLeft', 'builder-mosaic' ),
							'fadeInRight' => __( 'fadeInRight', 'builder-mosaic' ),
							'fadeInDown' => __( 'fadeInDown', 'builder-mosaic' ),
							'bounceInUp' => __( 'bounceInUp', 'builder-mosaic' ),
							'bounceInDown' => __( 'bounceInDown', 'builder-mosaic' ),
							'bounceInLeft' => __( 'bounceInLeft', 'builder-mosaic' ),
							'bounceInRight' => __( 'bounceInRight', 'builder-mosaic' ),
							'rotateIn' => __( 'rotateIn', 'builder-mosaic' ),
							'rotateInDownLeft' => __( 'rotateInDownLeft', 'builder-mosaic' ),
							'rotateInDownRight' => __( 'rotateInDownRight', 'builder-mosaic' ),
							'rotateInUpLeft' => __( 'rotateInUpLeft', 'builder-mosaic' ),
							'rollIn' => __( 'rollIn', 'builder-mosaic' ),
							'slideInUp' => __( 'slideInUp', 'builder-mosaic' ),
							'slideInDown' => __( 'slideInDown', 'builder-mosaic' ),
							'slideInLeft' => __( 'slideInLeft', 'builder-mosaic' ),
							'slideInRight' => __( 'slideInRight', 'builder-mosaic' ),
							'flipInX' => __( 'flipInX', 'builder-mosaic' ),
							'flipInY' => __( 'flipInY', 'builder-mosaic' ),
							'zoomInUp' => __( 'zoomInUp', 'builder-mosaic' ),
							'zoomInLeft' => __( 'zoomInLeft', 'builder-mosaic' ),
							'zoomInRight' => __( 'zoomInRight', 'builder-mosaic' ),
							'zoomInDown' => __( 'zoomInDown', 'builder-mosaic' ),
						),
					),
					array(
						'id' => 'hide_title',
						'type' => 'toggle_switch',
						'label' => __( 'Item Title', 'builder-mosaic' )
					),
					array(
						'id' => 'hide_caption',
						'type' => 'toggle_switch',
						'label' => __( 'Item Caption', 'builder-mosaic' )
					),
					array(
						'id' => 'hide_badge',
						'type' => 'toggle_switch',
						'label' => __( 'Item Badge', 'builder-mosaic' )
					),
					array(
						'id' => 'caption_length',
						'type' => 'number',
						'label' => __( 'Caption Length', 'builder-mosaic' ),
						'after' => __( 'words', 'builder-mosaic' )
					),
					array(
						'id' => 'pagination',
						'type' => 'select',
						'label' => __('Pagination', 'builder-mosaic'),
						'options' => array(
							'disabled' => __('No Pagination', 'builder-mosaic'),
							'infinite-scroll' => __('Infinite Scroll', 'builder-mosaic'),
							'links' => __('Pagination Links', 'builder-mosaic'),
							'load-more' => __('Load More Button', 'builder-mosaic'),
						),
						'wrap_class' => 'tb_group_element_grid'
					),
					array(
						'type'=>'group',
						'wrap_class' => 'tb_group_element_slider',
						'options'=>array(
						array(
							'type' => 'separator',
							'label' => __( 'Slider Settings', 'builder-mosaic' )
						),
						array(
							'id' => 'slider_auto_scroll',
							'type' => 'select',
							'options' => array(
								'off' => __( 'Off', 'builder-mosaic' ),
								1 => __( '1 sec', 'builder-mosaic' ),
								2 => __( '2 sec', 'builder-mosaic' ),
								3 => __( '3 sec', 'builder-mosaic' ),
								4 => __( '4 sec', 'builder-mosaic' ),
								5 => __( '5 sec', 'builder-mosaic' ),
								6 => __( '6 sec', 'builder-mosaic' ),
								7 => __( '7 sec', 'builder-mosaic' ),
								8 => __( '8 sec', 'builder-mosaic' ),
								9 => __( '9 sec', 'builder-mosaic' ),
								10 => __( '10 sec', 'builder-mosaic' ),
								15 => __( '15 sec', 'builder-mosaic' ),
								20 => __( '20 sec', 'builder-mosaic' ),
							),
							'label' => __('Auto Scroll', 'builder-mosaic')
						),
						array(
							'id' => 'slider_speed',
							'type' => 'select',
							'options' => array(
								'normal' => __('Normal', 'builder-mosaic'),
								'fast' => __('Fast', 'builder-mosaic'),
								'slow' => __('Slow', 'builder-mosaic')
							),
							'label' => __('Speed', 'builder-mosaic')
						),
						array(
							'id' => 'slider_effect',
							'type' => 'select',
							'options' => array(
								'scroll' => __('Slide', 'builder-mosaic'),
								'fade' => __('Fade', 'builder-mosaic'),
								'crossfade' => __('Cross Fade', 'builder-mosaic'),
								'cover' => __('Cover', 'builder-mosaic'),
								'cover-fade' => __('Cover Fade', 'builder-mosaic'),
								'uncover' => __('Uncover', 'builder-mosaic'),
								'uncover-fade' => __('Uncover Fade', 'builder-mosaic'),
								'continuously' => __('Continuously', 'builder-mosaic')
							),
							'label' => __('Slider Effect', 'builder-mosaic')
						),
						array(
							'id' => 'slider_pause',
							'type' => 'toggle_switch',
							'options' => array(
								'on' => array('name'=>'resume', 'value' =>'y'),
								'off' => array('name'=>'false', 'value' =>'no')
							),
							'label' => __('Pause On Hover', 'builder-mosaic')
						),
						array(
							'id' => 'slider_wrap',
							'type' => 'toggle_switch',
							'label' => __('Wrap', 'builder-mosaic'),
							'options' => 'simple'
						),
						array(
							'id' => 'slider_pagination',
							'type' => 'toggle_switch',
							'label' => __('Slider Pagination', 'builder-mosaic'),
							'options' => 'simple'
						),
						array(
							'id' => 'slider_arrows',
							'type' => 'toggle_switch',
							'label' => __('Slider Arrows', 'builder-mosaic'),
							'options' => 'simple'
						),
						)
					),
				),
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_class' ),
		);
	}

	public function get_live_default() {
		return array(
			'slides_count' => 3,
			'base_height' => 150,
			'min_width' => 600,
			'slider_pause'=>'resume',
			'slider_pagination'=>'yes',
			'slider_wrap'=>'yes',
			'slider_arrows'=>'yes',
			'free_products' => 'show',
			'outofstock_products'=>'show',
			'gutter'=> 10,
			'tiled_posts_display' => 'posts',
			'template'=>'[{"x":0,"y":0,"width":7,"height":4},{"x":7,"y":0,"width":5,"height":2},{"x":7,"y":2,"width":5,"height":2},{"x":0,"y":4,"width":5,"height":2},{"x":5,"y":4,"width":7,"height":2}]'
		);
	}

	public function get_animation() {
		return array();
	}

	public function get_styling() {
		$general = array(
			// Background
			self::get_expand('bg', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_color('', 'background_color', 'bg_c', 'background-color')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_color('', 'bg_c', 'bg_c', 'background-color', 'h')
				    )
				)
			    ))
			)),
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(),
					self::get_color(array('', ' .tbm_title', ' .tbm_caption', ' .tbm_caption p'),'font_color'),
					self::get_font_size(),
					self::get_font_style( '', 'f_fs_g', 'f_fw_g' ),
					self::get_line_height(),
					self::get_text_align(array('', ' .tbm_title', ' .tbm_caption')),
					self::get_text_shadow(),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family('','f_f','h'),
					self::get_color(array('', ' .tbm_title', ' .tbm_caption', ' .tbm_caption p'),'f_c',null,null,'h'),
					self::get_font_size('','f_s','f_w','','h'),
					self::get_font_style( '', 'f_fs_g', 'f_fw_g', 'h' ),
					self::get_text_shadow('','t_sh','h'),
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
					self::get_margin('', 'm', 'h'),
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
		);

		$mosaic_title = array(
			// Font
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family('.module figure .tbm_title' ,'f_f_m_t'),
					self::get_color( '.module figure .tbm_title' ,'f_c_m_t'),
					self::get_font_size('.module figure .tbm_title', 'f_s_m_t'),
					self::get_line_height('.module figure .tbm_title', 'l_h_m_t'),
					self::get_letter_spacing('.module figure .tbm_title', 'l_s_m_t'),
					self::get_text_align('.module figure .tbm_title', 't_a_m_t'),
					self::get_text_transform('.module figure .tbm_title', 't_t_m_t'),
					self::get_font_style('.module figure .tbm_title', 'f_sy_m_t','f_t_b'),
					self::get_text_shadow('.module figure .tbm_title' ,'t_sh_t'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family('.module figure .tbm_title' ,'f_f_m_t','h'),
					self::get_color( '.module figure .tbm_title' ,'f_c_m_t',null,null,'h'),
					self::get_font_size('.module figure .tbm_title', 'f_s_m_t','','h'),
					self::get_font_style('.module figure .tbm_title', 'f_sy_m_t','f_t_b','h'),
					self::get_text_shadow('.module figure .tbm_title' ,'t_sh_t','h'),
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding('.module figure .tbm_title','m_t_p')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding('.module figure .tbm_title','m_t_p','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_margin('.module figure .tbm_title','m_t_m')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin('.module figure .tbm_title','m_t_m','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border('.module figure .tbm_title','m_t_b')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border('.module figure .tbm_title','m_t_b','h')
				    )
				)
			    ))
			))
		);

		$mosaic_caption = array(
			self::get_expand('bg', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_color(' figure .tbm_caption', 'b_c_cn','bg_c','background-color')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_color(' figure .tbm_caption', 'b_c_cn','bg_c','background-color','h')
				    )
				)
			    ))
			)),
			// Font
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(' figure .tbm_caption' ,'f_f_m_cn'),
					self::get_color(' figure .tbm_caption' ,'f_c_m_cn'),
					self::get_font_size(' figure .tbm_caption', 'f_s_m_cn'),
					self::get_line_height(' figure .tbm_caption', 'l_h_m_cn'),
					self::get_letter_spacing(' figure .tbm_caption', 'l_s_m_cn'),
					self::get_text_align(' figure .tbm_caption', 't_a_m_cn'),
					self::get_text_transform(' figure .tbm_caption', 't_t_m_cn'),
					self::get_font_style(' figure .tbm_caption', 'f_sy_m_cn','f_cn_b'),
					self::get_text_shadow(' figure .tbm_caption' ,'t_sh_c'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family(' figure .tbm_caption' ,'f_f_m_cn','h'),
					self::get_color(' figure .tbm_caption' ,'f_c_m_cn',null,null,'h'),
					self::get_font_size(' figure .tbm_caption', 'f_s_m_cn','','h'),
					self::get_font_style(' figure .tbm_caption', 'f_sy_m_cn','f_cn_b','h'),
					self::get_text_shadow(' figure .tbm_caption' ,'t_sh_c','h'),
				    )
				)
			    ))
			)),
			// Font
			self::get_expand('l', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_color(' figure .tbm_caption a', 'l_c_m_cn'),
					self::get_text_decoration(' figure .tbm_caption a')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_color('  figure .tbm_caption a', 'l_c_hv_m_cn',null,null,'h'),
					self::get_text_decoration('  figure .tbm_caption a','t_a','h')
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' figure .tbm_caption','m_cn_p')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' figure .tbm_caption','m_cn_p','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_margin(' figure .tbm_caption','m_cn_m'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin(' figure .tbm_caption','m_cn_m','h'),
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' figure .tbm_caption','m_cn_b')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' figure .tbm_caption','m_cn_b','h')
				    )
				)
			    ))
			))
		);

		$mosaic_overlay = array(
			//bacground
			self::get_expand('bg', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_image('.module figure::before','background_image','b_c_ov')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_image('.module figure:hover::before', 'b_i_hv_ov','b_c_hv_ov','background-image')
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding('.module figure figcaption','m_ov_p')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding('.module figure figcaption','m_ov_p','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_margin('.module figure::before','m_ov_m')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin('.module figure::before','m_ov_m','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border('.module figure::before','m_ov_b')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border('.module figure::before','m_ov_b','h')
				    )
				)
			    ))
			))
		);

		$mosaic_tile_container = array(
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .mosaic-container','m_tc_p')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .mosaic-container','m_tc_p','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .mosaic-container','m_tc_b')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .mosaic-container','m_tc_b','h')
				    )
				)
			    ))
			))
		);
		
		$mosaic_pagination = array(
			self::get_expand(__( 'Load More', 'builder-mosaic' ), array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_color(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 'b_c_pg','bg_c','background-color')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_color(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 'b_c_pg','bg_c','background-color','h')
				    )
				)
			    ))
			)),
			// Font
			self::get_expand('l', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'f_f_m_pg'),
					self::get_color(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'f_c_m_pg'),
					self::get_font_size(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 'f_s_m_pg'),
					self::get_line_height(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 'l_h_m_pg'),
					self::get_letter_spacing(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 'l_s_m_pg'),
					self::get_text_align(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 't_a_m_pg'),
					self::get_text_transform(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 't_t_m_pg'),
					self::get_font_style(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 'f_sy_m_pg','f_pg_b'),
					self::get_text_shadow(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'t_sh_p'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'f_f_m_pg','h'),
					self::get_color(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'f_c_m_pg',null,null,'h'),
					self::get_font_size(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 'f_s_m_pg','','h'),
					self::get_font_style(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'), 'f_sy_m_pg','f_pg_b','h'),
					self::get_text_shadow(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'t_sh_p','h'),
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'m_pg_p')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'m_pg_p','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'m_pg_b')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(array(' .tbm_wrap_more .tbm_more', ' .pagenav a', ' .pagenav span'),'m_pg_b','h')
				    )
				)
			    ))
			))
		);

		return array(	'type' => 'tabs',
				'options' => array(
					'g' => array(
						'options' => $general
					),
					'm_t_c' => array(
						'label' => __('Tile Container', 'builder-mosaic'),
						'options' => $mosaic_tile_container
					),
					'm_t' => array(
						'label' => __('Title', 'builder-mosaic'),
						'options' => $mosaic_title
					),
					'm_c' => array(
						'label' => __('Caption', 'builder-mosaic'),
						'options' => $mosaic_caption
					),
					'm_o' => array(
						'label' => __('Overlay', 'builder-mosaic'),
						'options' => $mosaic_overlay
					),
					'm_p' => array(
						'label' => __('Pagination', 'builder-mosaic'),
						'options' => $mosaic_pagination
					)
				)
		);
	}
		
	public static function pagination_links( $limit, $total ) {
		$paged = ! empty( $_GET['builder-mosaic'] ) ? (int) $_GET['builder-mosaic'] : 1;
		$max_page = ceil( $total / $limit );
		return $max_page>0 && method_exists(__CLASS__,'get_pagination')?self::get_pagination('','','builder-mosaic',0,$max_page,$paged):'';
	}
}
if(method_exists('Themify_Builder_Model', 'add_module')){
    new TB_Mosaic_Module();
}
else{
    Themify_Builder_Model::register_module('TB_Mosaic_Module');
}
