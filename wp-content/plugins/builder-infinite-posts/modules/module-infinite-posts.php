<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Module Name: Infinite Posts
 */

class TB_Infinite_Posts_Module extends Themify_Builder_Component_Module {

    public function __construct() {
        if(method_exists('Themify_Builder_Model', 'add_module')){
            parent::__construct('infinite-posts');
        }
        else{//backward
             parent::__construct(array(
                'name' =>$this->get_name(),
                'slug' => 'infinite-posts',
                'category' =>$this->get_group()
            ));
        }
    }
    
    public function get_name(){
        return  __('Infinite Posts', 'builder-infinite-posts');
    }
    
    public function get_icon(){
	return 'layers';
    }
    
    public function get_group() {
        return array('addon');
    }
    
    public function get_assets() {
		$url=Builder_Infinite_Posts::$url . 'assets/';
		$res=array(
			'async'=>true,
			'css' => $url. 'style',
			'ver' => Builder_Infinite_Posts::$version
		);
		if(!Themify_Builder_Model::is_front_builder_activate()){
			$res['js']=$url . 'scripts';
		}
		return $res;
    }
    
    
    
    
    public function get_options() {

	return array(
	    array(
		'id' => 'mod_title',
		'type' => 'title'
	    ),
	    array(
		'type' => 'query_posts',
		'id' => 'post_type_post',
		'tax_id' => 'type_query_post',
		'term_id' => '#tmp_id#_post', //backward compatibility
		'slug_id' => 'query_slug_post',
		'query_filter' => true,
	    ),
	    array(
		'id' => 'post_per_page_post',
		'type' => 'number',
		'label' => __('Posts Per Page', 'builder-infinite-posts'),
		'help' => __('Number of posts to show.', 'builder-infinite-posts'),
	    ),
	    array(
		'id' => 'offset_post',
		'type' => 'number',
		'label' => __('Offset', 'builder-infinite-posts'),
		'help' => __('Number of post to displace or pass over.', 'builder-infinite-posts'),
	    ),
	    array(
		'id' => 'order_post',
		'type' => 'select',
		'label' => __('Order', 'builder-infinite-posts'),
		'help' => __('Sort posts in ascending or descending order (descending = show newer posts first).', 'builder-infinite-posts'),
		'order' =>true
	    ),
	    array(
		'id' => 'orderby_post',
		'type' => 'select',
		'label' => __('Order By', 'builder-infinite-posts'),
		'options' => array(
		    'date' => __('Date', 'builder-infinite-posts'),
		    'id' => __('ID', 'builder-infinite-posts'),
		    'author' => __('Author', 'builder-infinite-posts'),
		    'title' => __('Title', 'builder-infinite-posts'),
		    'name' => __('Name', 'builder-infinite-posts'),
		    'modified' => __('Modified', 'builder-infinite-posts'),
		    'rand' => __('Random', 'builder-infinite-posts'),
		    'comment_count' => __('Comment Count', 'builder-infinite-posts')
		)
	    ),
	    array(
		'id' => 'layout',
		'type' => 'radio',
		'label' => __('Layout', 'builder-infinite-posts'),
		'options' => array(
		    array('name' =>__('Parallax', 'builder-infinite-posts'), 'value' =>'parallax'),
		    array('name' => __('List', 'builder-infinite-posts'), 'value' => 'list'),
		    array('name' =>__('Grid', 'builder-infinite-posts'), 'value' =>'grid'),
		    array('name' => __('Overlay', 'builder-infinite-posts'), 'value' => 'overlay')
		),
		    'wrap_class' => 'tb_compact_radios',
		'option_js' => true
	    ),
	    array(
		'id' => 'post_layout',
		'type' => 'layout',
		'mode' => 'sprite',
		'label' => __('Post Layout', 'builder-infinite-posts'),
		'control'=>array(
		    'classSelector'=>'.builder-infinite-posts-wrap'
		),
		'options' => array(
		    array('img' => 'grid2', 'value' => 'grid-2', 'label' => __('Grid 2', 'builder-infinite-posts')),
		    array('img' => 'grid3', 'value' => 'grid-3', 'label' => __('Grid 3', 'builder-infinite-posts')),
		    array('img' => 'grid4', 'value' => 'grid-4', 'label' => __('Grid 4', 'builder-infinite-posts')),
			array('img' => 'grid5', 'value' => 'grid5', 'label' => __('Grid 5', 'builder-infinite-posts')),
			array('img' => 'grid6', 'value' => 'grid6', 'label' => __('Grid 6', 'builder-infinite-posts'))
		),
		'wrap_class' => 'tb_group_element_grid tb_group_element_overlay'
	    ),
	    array(
		'id' => 'image_size',
		'type' => 'select',
		'label' => __('Image Size', 'builder-infinite-posts'),
		'hide' => !Themify_Builder_Model::is_img_php_disabled(),
		'image_size' => true,
		'wrap_class' => 'tb_group_element_grid tb_group_element_list tb_group_element_overlay'
	    ),
	    array(
		'id' => 'img_width',
		'type' => 'number',
		'label' => __('Image Width', 'builder-infinite-posts'),
		'wrap_class' => 'tb_group_element_list tb_group_element_grid tb_group_element_overlay',
	    ),
	    array(
		'id' => 'img_height',
		'type' => 'number',
		'label' => __('Image Height', 'builder-infinite-posts'),
		'wrap_class' => 'tb_group_element_list tb_group_element_grid tb_group_element_overlay',
	    ),
	    array(
		'id' => 'row_height',
		'type' => 'select',
		'label' => __('Post Height', 'builder-infinite-posts'),
		'options' => array(
		    'height-default' => __('Default', 'builder-infinite-posts'),
		    'fullheight' => __('Fullheight', 'builder-infinite-posts'),
		),
		'wrap_class' => 'tb_group_element_parallax'
	    ),
	    array(
		'id' => 'background_style',
		'type' => 'select',
		'label' => __('Background Style', 'builder-infinite-posts'),
		'options' => array(
		    'builder-parallax-scrolling' => __('Parallax Scrolling', 'builder-infinite-posts'),
		    'fullcover' => __('Full Cover', 'builder-infinite-posts'),
		),
		'wrap_class' => 'tb_group_element_parallax'
	    ),
	    array(
		'id' => 'overlay_color',
		'type' => 'color',
		'label' => __('Overlay Color', 'builder-infinite-posts'),
		'class' => 'small',
		'wrap_class' => 'tb_group_element_parallax'
	    ),
	    array(
		'id' => 'masonry',
		'type' => 'toggle_switch',
		'label' => __('Masonry Layout', 'builder-infinite-posts'),
		'options' => array(
		    'on' => array('name'=>'enable', 'value' =>'en'),
		    'off' => array('name'=>'disable', 'value' =>'dis')
		),
		'wrap_class' => 'tb_group_element_grid tb_group_element_overlay'
	    ),
	    array(
		'id' => 'gutter',
		'type' => 'select',
		'label' => __('Gutter Spacing', 'builder-infinite-posts'),
		'options' => array(
		    'default' => __('Default', 'builder-infinite-posts'),
		    'narrow' => __('Narrow', 'builder-infinite-posts'),
		    'none' => __('None', 'builder-infinite-posts'),
		),
		'wrap_class' => 'tb_group_element_grid tb_group_element_overlay'
	    ),
	    array(
		'id' => 'pagination',
		'type' => 'select',
		'label' => __('Pagination', 'builder-infinite-posts'),
		'options' => array(
		    'infinite-scroll' => __('Infinite Scroll', 'builder-infinite-posts'),
		    'links' => __('Pagination Links', 'builder-infinite-posts'),
		    'load-more' => __('Load More Button', 'builder-infinite-posts'),
		    'disabled' => __('No Pagination', 'builder-infinite-posts'),
		)
	    ),
	    self::get_seperator(),
	    array(
		'id' => 'display_content',
		'type' => 'select',
		'label' => __('Display', 'builder-infinite-posts'),
		'options' => array(
		    'excerpt' => __('Excerpt', 'builder-infinite-posts'),
		    'content' => __('Content', 'builder-infinite-posts'),
		    'none' => __('None', 'builder-infinite-posts'),
		)
	    ),
	    array(
		    'id' => 'hide_post_image',
		    'type' => 'toggle_switch',
		    'default'=>'on',
		    'label' => __('Featured Image', 'builder-infinite-posts'),
		    'options' => array(
			'on' => array('name'=>'', 'value' =>'s'),
			'off' => array('name'=>'yes', 'value' =>'hi')
			   
		    ),
		    'binding' => array(
			'checked' => array(
				'show' => array('image_size', 'img_width', 'img_height')
			),
			'not_checked' => array(
				'hide' => array('image_size', 'img_width', 'img_height')
			)
		    ),
		    'wrap_class' => 'tb_group_element_grid tb_group_element_list'
	    ),
	    array(
		'id' => 'unlink_image',
		'type' => 'toggle_switch',
		'label' => __('Unlink Featured Image', 'builder-infinite-posts'),
		'options' => 'simple',
		'wrap_class' => 'tb_group_element_grid tb_group_element_list'
	    ),
	    array(
		'id' => 'hide_post_title',
		'type' => 'toggle_switch',
		'label' => __('Post Title', 'builder-infinite-posts'),
            'binding' => array(
                'checked' => array(
                    'show' => array('unlink_post_title','title_tag')
                ),
                'not_checked' => array(
                    'hide' =>array('unlink_post_title','title_tag')
                )
            )
	    ),
        array(
            'id' => 'title_tag',
            'type' => 'select',
            'label' => __('Post Title Tag', 'builder-infinite-posts'),
            'h_tags' => true,
            'default' => 'h2'
        ),
	    array(
		'id' => 'unlink_post_title',
		'type' => 'toggle_switch',
		'label' => __('Unlink Post Title', 'builder-infinite-posts'),
		'options' => 'simple'
	    ),
	    array(
		'id' => 'hide_post_date',
		'type' => 'toggle_switch',
		'label' => __('Post Date', 'builder-infinite-posts')
	    ),
	    array(
		'id' => 'hide_post_meta',
		'type' => 'toggle_switch',
		'label' => __('Post Meta', 'builder-infinite-posts')
	    ),
	    array(
			'id' => 'hide_empty',
			'type' => 'toggle_switch',
			'label' => __('Hide Empty Module', 'themify'),
			'help' => __('Hide the module when there is no posts.', 'themify'),
			'options' => 'simple',
			'binding' => [
				'checked' => [ 'hide' => 'no_posts_group' ],
				'not_checked' => [ 'show' => 'no_posts_group' ],
			],
	    ),
		array(
			'id' => 'no_posts_group',
			'options' => array(
				array(
					'id' => 'no_posts',
					'type' => 'toggle_switch',
					'label' => __( 'No Posts Message', 'themify' ),
					'options'   => array(
						'off' => array( 'name' => '', 'value' => 'dis' ),
						'on'  => array( 'name' => '1', 'value' => 'en' ),
					),
					'binding' => array(
						'checked' => array( 'show' => 'no_posts_msg' ),
						'not_checked' => array( 'hide' => 'no_posts_msg' ),
					)
				),
				array(
					'id' => 'no_posts_msg',
					'type' => 'textarea',
					'label' => ' ',
				),
			),
			'type' => 'group',
		),
	    array(
			'type' => 'group',
			'display' => 'accordion',
			'label' => __('Read More Button', 'builder-infinite-posts'),
			'options' => array(
				array(
				'id' => 'hide_read_more_button',
				'type' => 'toggle_switch',
				'label' => __('Read More', 'builder-infinite-posts'),
				),
				array(
				'id' => 'read_more_text',
				'type' => 'text',
				'label' => __('Button Text', 'builder-infinite-posts'),
				'value' => __('Read More', 'builder-infinite-posts'),
				),
				array(
				'id' => 'permalink',
				'type' => 'select',
				'label' => __('Open Link In', 'builder-infinite-posts'),
				'options' => array(
					'default' => __('Same Window', 'builder-infinite-posts'),
					'lightboxed' => __('Lightbox', 'builder-infinite-posts'),
					'newwindow' => __('New Window', 'builder-infinite-posts'),
				),
				),
				array(
				'id' => 'buttons_style',
				'type' => 'radio',
				'label' => __('Button Style', 'builder-infinite-posts'),
				'options' => array(
					array('name' => __('Colored', 'builder-infinite-posts'), 'value' => 'colored'),
					array('name' => __('Outlined', 'builder-infinite-posts'), 'value' => 'outline')
				)
				),
				array(
				'id' => 'color_button',
				'type' => 'layout',
				'mode' => 'sprite',
				'class' => 'tb_colors',
				'label' => __('Button Color', 'builder-infinite-posts'),
				'color' => true,
				'transparent' => true
				),
				array(
				'id' => 'read_more_size',
				'type' => 'radio',
				'label' => __('Button Size', 'builder-infinite-posts'),
				'wrap_class' => 'tb_compact_radios',
				'options' => array(
					array('name' => __('Small', 'builder-infinite-posts'), 'value' => 'small'),
					array('name' => __('Normal', 'builder-infinite-posts'), 'value' => 'normal'),
					array('name' => __('Large', 'builder-infinite-posts'), 'value' => 'large'),
					array('name' => __('xLarge', 'builder-infinite-posts'), 'value' => 'xlarge')
				)
				),
			),
		),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_post' ),
	);
    }

    public function get_live_default() {
	return array(
	    'post_per_page_post' => 3,
	    'masonry'=>'enable',
	    'hide_post_date'=>'yes',
	    'hide_post_meta'=>'yes',
	    'overlay_color' => '000000_0.30'
	);
    }

    public function get_styling() {
	$general = array(
	    //bacground
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
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(),
			    self::get_color_type(array(' .post-title a',' .bip-post-content',' .post-date')),
			    self::get_font_size(),
			    self::get_font_style( '', 'f_fs_g', 'f_fw_g' ),
			    self::get_line_height(),
			    self::get_text_align(),
				self::get_text_shadow(),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(''),
			    self::get_color_type(array(' .post:hover .post-title a',' .post:hover .bip-post-content',' .post:hover .post-date'),'f_c_h','f_c_g_h',''),
			    self::get_font_size('', 'f_f', '', 'h'),
				self::get_font_style( '', 'f_fs_g', 'f_fw_g', 'h' ),
			    self::get_line_height('', 'l_h', 'h'),
			    self::get_text_align('', 't_a', 'h'),
				self::get_text_shadow('','t_sh','h'),
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' a:not(.builder_button)', 'link_color'),
			    self::get_text_decoration(' a:not(.builder_button)')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' a:not(.builder_button)', 'link_color',null,null,'hover'),
			    self::get_text_decoration(' a:not(.builder_button)','t_d','h')
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
			    self::get_padding('','p','h')
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
			    self::get_margin('','m','h')
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
			    self::get_border('','b','h')
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

	$post_title = array(
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family('.module .post-title', 'font_family_post_title'),
			self::get_color(array('.module .post-title', '.module .post-title a'), 'font_color_post_title'),
			self::get_font_size('.module .post-title', 'font_size_post_title'),
			self::get_line_height('.module .post-title', 'line_height_post_title'),
			self::get_letter_spacing('.module .post-title', 'l_s_t'),
			self::get_text_transform('.module .post-title', 't_t_t'),
			self::get_font_style('.module .post-title', 'f_sy_t', 'f_t_b'),
			self::get_text_shadow('.module .post-title', 't_sh_p_t'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family('.module .post-title', 'f_f_p_t','h'),
			self::get_color(array('.module .post-title', '.module .post-title a'), 'f_c_p_t',null,null,'h'),
			self::get_font_size('.module .post-title', 'f_s_p_t','','h'),
			self::get_line_height('.module .post-title', 'l_h_p_t','h'),
			self::get_letter_spacing('.module .post-title', 'l_s_t','h'),
			self::get_text_transform('.module .post-title', 't_t_t','h'),
			self::get_font_style('.module .post-title', 'f_sy_t', 'f_t_b','h'),
			self::get_text_shadow('.module .post-title', 't_sh_p_t','h'),
		    )
		)
	    ))
	);

	$post_date = array(
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .post-date', 'font_family_post_date'),
			self::get_color(' .post-date', 'font_color_post_date'),
			self::get_font_size(' .post-date', 'font_size_post_date'),
			self::get_font_style(' .post-date', 'f_fs_d', 'f_fw_d'),
			self::get_line_height(' .post-date', 'line_height_post_date'),
			self::get_text_shadow(' .post-date', 't_sh_p_d'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .post-date', 'f_f_p_d','h'),
			self::get_color(' .post-date', 'f_c_p_d',null,null,'h'),
			self::get_font_size(' .post-date', 'f_s_p_d','','h'),
			self::get_font_style(' .post-date', 'f_fs_d', 'f_fw_d', 'h'),
			self::get_line_height(' .post-date', 'l_h_p_d','h'),
			self::get_text_shadow(' .post-date', 't_sh_p_d','h'),
		    )
		)
	    ))
	);

	$post_meta = array(
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .post-meta', 'font_family_post_meta'),
			self::get_color(array(' .post-meta', ' .post-meta a'), 'font_color_post_meta'),
			self::get_font_size(' .post-meta', 'font_size_post_meta'),
			self::get_font_style(' .post-meta', 'f_fs_p', 'f_fw_p'),
			self::get_line_height(' .post-meta', 'line_height_post_meta'),
			self::get_text_shadow(' .post-meta', 't_sh_p_m'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .post-meta', 'f_f_p_m','h'),
			self::get_color(array(' .post-meta', ' .post-meta a'), 'f_c_p_m',null,null,'h'),
			self::get_font_size(' .post-meta', 'f_s_p_m','','h'),
			self::get_font_style(' .post-meta', 'f_fs_p', 'f_fw_p', ''),
			self::get_line_height(' .post-meta', 'l_h_p_m','h'),
			self::get_text_shadow(' .post-meta', 't_sh_p_m','h'),
		    )
		)
	    ))
	);

	$post_content = array(
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .bip-post-content', 'font_family_post_content'),
			self::get_color(' .bip-post-content', 'font_color_post_content'),
			self::get_font_size(' .bip-post-content', 'font_size_post_content'),
			self::get_font_style(' .bip-post-content', 'f_fs_c', 'f_fw_c'),
			self::get_line_height(' .bip-post-content', 'line_height_post_content'),
			self::get_text_shadow(' .bip-post-content', 't_sh_p_c'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .bip-post-content', 'f_f_p_c','h'),
			self::get_color(' .bip-post-content', 'f_c_p_c',null,null,'h'),
			self::get_font_size(' .bip-post-content', 'f_s_p_c','','h'),
			self::get_font_style(' .bip-post-content', 'f_fs_c', 'f_fw_c', 'h'),
			self::get_line_height(' .bip-post-content', 'l_h_p_c','h'),
			self::get_text_shadow(' .bip-post-content', 't_sh_p_c','h'),
		    )
		)
	    ))
	);

	$read_more_button = array(
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' a.read-more-button', 'background_color_read_more','bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' a.read-more-button', 'bg_c_r_m','bg_c', 'background-color','h')
			)
		    )
		))
	    )),
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' a.read-more-button', 'font_family_read_more'),
			    self::get_color(' a.read-more-button', 'font_color_read_more'),
			    self::get_font_size(' a.read-more-button', 'font_size_read_more'),
			    self::get_font_style(' a.read-more-button', 'f_fs_r', 'f_fw_r'),
			    self::get_text_align(' .read-more-button-wrap', 'text_align_read_more'),
				self::get_text_shadow(' a.read-more-button', 't_sh_r_b'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' a.read-more-button', 'f_f_r_m','h'),
			    self::get_color(' a.read-more-button', 'f_c_r_m',null,null,'h'),
			    self::get_font_size(' a.read-more-button', 'f_s_r_m','','h'),
				self::get_font_style(' a.read-more-button', 'f_fs_r', 'f_fw_r', 'h'),
			    self::get_text_align(' .read-more-button-wrap', 't_a_r_m','h'),
				self::get_text_shadow(' a.read-more-button', 't_sh_r_b','h'),
			)
		    )
		))
	    )),
		// Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' a.read-more-button', 'r_m_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' a.read-more-button', 'r_m_p','h'),
			)
		    )
		))
		)),
		// Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' a.read-more-button', 'r_m_m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' a.read-more-button', 'r_m_m','h')
			)
		    )
		))
	    )),
		// Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' a.read-more-button', 'r_m_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' a.read-more-button', 'r_m_b','h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' a.read-more-button', 'r_m_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' a.read-more-button', 'r_m_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' a.read-more-button', 'r_m_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' a.read-more-button', 'r_m_b_sh', 'h')
					)
				)
			))
		))
	);

	$load_more_button = array(
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .infinite-posts-load-more-wrap a', 'b_c_l_m', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			   self::get_color(' .infinite-posts-load-more-wrap a', 'b_c_l_m', 'bg_c', 'background-color','h')
			)
		    )
		))
	    )),
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .infinite-posts-load-more-wrap a', 'f_f_l_m'),
			    self::get_color(' .infinite-posts-load-more-wrap a', 'f_c_l_m'),
			    self::get_font_size(' .infinite-posts-load-more-wrap a', 'f_s_l_m'),
			    self::get_font_style(' .infinite-posts-load-more-wrap a', 'f_st_l_m', 'f_fw_l_m'),
			    self::get_text_align(' .infinite-posts-load-more-wrap', 't_a_l_m'),
				self::get_text_shadow(' .infinite-posts-load-more-wrap a', 't_sh_l_b'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .infinite-posts-load-more-wrap a', 'f_f_l_m','h'),
			    self::get_color(' .infinite-posts-load-more-wrap a', 'f_c_l_m',null,null,'h'),
			    self::get_font_size(' .infinite-posts-load-more-wrap a', 'f_s_l_m','','h'),
				self::get_font_style(' .infinite-posts-load-more-wrap a', 'f_st_l_m', 'f_fw_l_m', 'h'),
			    self::get_text_align(' .infinite-posts-load-more-wrap', 't_a_l_m','h'),
				self::get_text_shadow(' .infinite-posts-load-more-wrap a', 't_sh_l_b','h'),
			)
		    )
		))
	    )),
		// Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .infinite-posts-load-more-wrap a', 'l_m_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .infinite-posts-load-more-wrap a', 'l_m_p','h'),
			)
		    )
		))
		)),
		// Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .infinite-posts-load-more-wrap a', 'l_m_m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .infinite-posts-load-more-wrap a', 'l_m_m','h')
			)
		    )
		))
	    )),
		// Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .infinite-posts-load-more-wrap a', 'l_m_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .infinite-posts-load-more-wrap a', 'l_m_b','h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .infinite-posts-load-more-wrap a', 'l_m_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .infinite-posts-load-more-wrap a', 'l_m_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .infinite-posts-load-more-wrap a', 'l_m_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .infinite-posts-load-more-wrap a', 'l_m_b_sh', 'h')
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
		't' => array(
		    'label' => __('Post Title', 'builder-infinite-posts'),
		    'options' => $post_title
		),
		'm' => array(
		    'label' => __('Post Meta', 'builder-infinite-posts'),
		    'options' => $post_meta
		),
		'd' => array(
		    'label' => __('Post Date', 'builder-infinite-posts'),
		    'options' => $post_date
		),
		'c' => array(
		    'label' => __('Post Content', 'builder-infinite-posts'),
		    'options' => $post_content
		),
		'r' => array(
		    'label' => __('Read More Button', 'builder-infinite-posts'),
		    'options' => $read_more_button
		),
		'l' => array(
		    'label' => __('Load More Button', 'builder-infinite-posts'),
		    'options' => $load_more_button
		)
	    )
	);
    }

    public static function get_infinity_pagination($query, $offset = 0,$paged=1) {
		$numposts = $query->found_posts;
		// $query->found_posts does not take offset into account, we need to manually adjust that
		if ($offset>0) {
			$numposts-=(int) $offset;
		}
		$max_page = ceil($numposts / $query->query_vars['posts_per_page']);
		return method_exists(__CLASS__,'get_pagination')?self::get_pagination('','','tb-infinite',0,$max_page,$paged):'';
    }

}
if(method_exists('Themify_Builder_Model', 'add_module')){
    new TB_Infinite_Posts_Module();
}
else{
    Themify_Builder_Model::register_module('TB_Infinite_Posts_Module');
}

