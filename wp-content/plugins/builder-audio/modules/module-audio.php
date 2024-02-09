<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Module Name: Audio
 */

class TB_Audio_Module extends Themify_Builder_Component_Module {

    public function __construct() {
        if(method_exists('Themify_Builder_Model', 'add_module')){
            parent::__construct('audio');
        }
        else{//backward
             parent::__construct(array(
                'name' =>$this->get_name(),
                'slug' => 'audio',
                'category' =>$this->get_group()
            ));
        }
    }


    public function get_name(){
        return  __('Audio', 'builder-audio');
    }

    public function get_icon(){
	return 'music-alt';
    }

    public function get_group() {
        return array('addon');
    }

    public function get_assets() {
        $url=Builder_Audio::$url . 'assets/';
        return array(
	    'async'=>true,
            'css' => $url. 'style',
            'js' => $url. 'script',
            'ver' => Builder_Audio::$version
        );
    }



    public function get_options() {
        return array(
	    array(
		'id' => 'mod_title_playlist',
		'type' => 'title'
	    ),
        array(
            'id' => 'music_playlist',
            'type' => 'builder',
            'options' => array(
                array(
                    'id' => 'audio_name',
                    'type' => 'text',
                    'label' => __('Audio Name', 'builder-audio'),
                    'class' => 'large',
                    'control' => array(
                        'selector'=>'.track-title>span'
                    )
                ),
                array(
                    'id' => 'audio_url',
                    'type' => 'audio',
                    'label' => __('Audio File URL', 'builder-audio')
                ),
                array(
                    'id' => 'audio_buy_button_text',
                    'type' => 'text',
                    'label' => __('Buy Button', 'builder-audio'),
                    'class' => 'large',
                    'control' => array(
                        'selector'=>'.track-buy'
                    )
                ),
                array(
                    'id' => 'audio_buy_button_link',
                    'type' => 'url',
                    'label' => __('Buy Link', 'builder-audio'),
                    'class' => 'large'
                )
            )
        ),
        array(
            'id' => 'hide_download_audio',
            'type' => 'toggle_switch',
            'label' => __( 'Download Link', 'builder-audio' )
	),
        array(
            'id' => 'buy_button_new_window',
            'type' => 'checkbox',
			'control'=>false,
            'label' => __( 'Buy Button', 'builder-audio' ),
            'options' => array(
                array( 'name' => 'buy_button_target', 'value' => __('Open link in new tab', 'builder-audio') )
            )
        ),

        array(
            'id' => 'auto_play_audio',
            'type' => 'checkbox',
            'label' => __( 'Auto Play', 'builder-audio' ),
            'options' => array(
                array( 'name' => 'auto_play_audio', 'value' => __('Auto play next songs', 'builder-audio') )
            )
        ),
			array( 'type' => 'custom_css_id', 'custom_css' => 'add_css_audio' ),
        );
    }

    public function get_live_default() {
        return array(
	    'hide_download_audio'=>'yes',
            'music_playlist' => array(array(
                    'audio_name' => __('Song Title', 'builder-audio'),
                    'audio_url' => 'https://themify.me/demo/themes/themes/wp-content/uploads/addon-samples/sample-song.mp3'
                ))
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
			    self::get_color(array(' .album-playlist', ' a', ' .tf_auido_play'),'f_c_gen'),
			    self::get_font_size(),
			    self::get_font_style( '', 'f_fs_g', 'f_fw_g' ),
			    self::get_line_height(),
			    self::get_letter_spacing(),
				self::get_text_shadow(),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('', 'f_f', 'h'),
			    self::get_color(array(' .track:hover a', ' .album-playlist .track:hover .tf_auido_play', ' .track:hover .tf_audio_current_time'),'f_c_gen_h',null,null,''),
			    self::get_font_size('', 'f_s', '', 'h'),
				self::get_font_style( '', 'f_fs_g', 'f_fw_g', 'h' ),
			    self::get_line_height('', 'l_h', 'h'),
			    self::get_letter_spacing('', 'l_s', 'h'),
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
			    self::get_padding('', 'p','h'),
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
			    self::get_margin('', 'm','h')
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
			    self::get_border('', 'b','h')
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

        $song_title = array(
            // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .album-playlist .tracklist .track-title','f_f_s_t'),
			self::get_color(' .album-playlist .tracklist .track-title','f_c_s_t'),
			self::get_font_size(' .album-playlist .tracklist .track-title', 'f_s_s_t'),
			self::get_line_height(' .album-playlist .tracklist .track-title', 'l_h_s_t'),
			self::get_letter_spacing(' .album-playlist .tracklist .track-title', 'l_s_s_t'),
			self::get_text_align(' .album-playlist .tracklist .track-title', 't_a_s_t'),
			self::get_text_transform(' .album-playlist .tracklist .track-title', 't_t_s_t'),
			self::get_font_style(' .album-playlist .tracklist .track-title', 'f_sy_s_t', 'f_b_s_t'),
            self::get_text_shadow(' .album-playlist .tracklist .track-title','t_sh_t'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .album-playlist .tracklist .track-title', 'f_f_s_t', 'h'),
			self::get_color(' .album-playlist .tracklist .track-title','f_c_s_t',null,null,'h'),
			self::get_font_size(' .album-playlist .tracklist .track-title', 'f_s_s_t','','h'),
			self::get_line_height(' .album-playlist .tracklist .track-title', 'l_h_s_t','h'),
			self::get_letter_spacing(' .album-playlist .tracklist .track-title', 'l_s_s_t','h'),
			self::get_text_align(' .album-playlist .tracklist .track-title', 't_a_s_t','h'),
			self::get_text_transform(' .album-playlist .tracklist .track-title', 't_t_s_t','h'),
			self::get_font_style(' .album-playlist .tracklist .track-title', 'f_sy_s_t', 'f_b_s_t','h'),
            self::get_text_shadow(' .album-playlist .tracklist .track-title','t_sh_t','h'),
		    )
		)
	    ))
        );

        $button_link = array(
            // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .track .track-buy', 'audio_button_background_color', 'bg', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .track .track-buy:hover', 'audio_button_hover_background_color', 'bg', 'background-color')
			)
		    )
		))
	    )),
            // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .track .track-buy', 'audio_link_color'),
			    self::get_text_decoration(' .track .track-buy', 'audio_text_decoration')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .track .track-buy:hover', 'audio_link_color_hover'),
			    self::get_text_decoration(' .track .track-buy', 'a_t_d','h')
			)
		    )
		))
	    )),
            // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .track .track-buy', 'button_link_padding')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .track .track-buy', 'b_l_p','h'),
			)
		    )
		))
	    )),

            // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .track .track-buy', 'button_link_margin')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .track .track-buy', 'b_l_m','h')
			)
		    )
		))
	    )),
            // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .track .track-buy', 'button_link_border')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .track .track-buy', 'b_l_b','h')
			)
		    )
		))
	    ))
        );

        $audio_time = array(
            // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .tf_audio_current_time','f_f_a_t'),
				self::get_font_size(' .tf_audio_current_time', 'f_s_a_t'),
			    self::get_color(' .tf_audio_current_time','f_c_a_t'),
			    self::get_line_height(' .tf_audio_current_time', 'l_h_a_t'),
			    self::get_letter_spacing(' .tf_audio_current_time', 'l_s_a_t'),
			    self::get_text_align(' .tf_audio_current_time', 't_a_a_t'),
			    self::get_text_transform(' .tf_audio_current_time', 't_t_a_t'),
			    self::get_font_style(' .tf_audio_current_time', 'f_sy_a_t', 'f_b_a_t'),
				self::get_text_shadow(' .tf_audio_current_time','t_sh_a_t'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .tf_audio_current_time','f_f_a_t','','h'),
				self::get_font_size(' .tf_audio_current_time', 'f_s_a_t','','h'),
			    self::get_color(' .tf_audio_current_time','f_c_a_t',null,null,'h'),
			    self::get_line_height(' .tf_audio_current_time', 'l_h_a_t','h'),
			    self::get_letter_spacing(' .tf_audio_current_time', 'l_s_a_t','h'),
			    self::get_text_align(' .tf_audio_current_time', 't_a_a_t','h'),
			    self::get_text_transform(' .tf_audio_current_time', 't_t_a_t','h'),
			    self::get_font_style(' .tf_audio_current_time', 'f_sy_a_t', 'f_b_a_t','h'),
				self::get_text_shadow(' .tf_audio_current_time','t_sh_a_t','h'),
			)
		    )
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
                    's' => array(
                        'label' => __('Song Title', 'themify'),
                        'options' => $song_title
                    ),
                    'b' => array(
                        'label' => __('Buy Button', 'themify'),
                        'options' => $button_link
                    ),
                    'a' => array(
                        'label' => __('Audio Time', 'themify'),
                        'options' => $audio_time
                    )
                )
        );
    }

    protected function _visual_template() {
        $module_args = self::get_module_args('mod_title_playlist');
        ?>
        <div class="module module-<?php echo $this->slug; ?> {{ data.add_css_audio }}">
            <# if( data.mod_title_playlist ) { #><?php echo $module_args['before_title']; ?>{{{ data.mod_title_playlist }}}<?php echo $module_args['after_title']; ?>
            <# }
	    const arr=data.music_playlist || [];
            if( arr.length>0 ) { #>
		<div class="album-playlist">
		    <ol class="tracklist tf_rel tf_w">
			<# for(var i=0,len=arr.length;i<len;++i){
			    let item=arr[i],
				name=item.audio_name || '',
				download=api.Helper.getIcon('fa-download').outerHTML;
			#>
			<li class="track is-playable<# if(data.auto_play_audio){#> auto_play<#}#> tf_rel tf_lazy">
			    <# if( item.audio_buy_button_link || item.audio_buy_button_text ) { #>
			    <a class="ui builder_button default track-buy" contenteditable="false" data-name="audio_buy_button_text" data-repeat="music_playlist" href="{{ item.audio_buy_button_link || '#' }}">{{ item.audio_buy_button_text || 'Buy Now' }}</a>
			    <# } #>

			    <a class="track-title" href="#" itemprop="url">
				<# if( name ) { #>
				<span itemprop="name" contenteditable="false" data-name="audio_name" data-repeat="music_playlist">{{{ name}}}</span>
				<# } #>
			    </a>
			    <# if( 'no' == data.hide_download_audio && item.audio_url ) { #>
				<a href="{{ item.audio_url }}" class="builder-audio-download" download><# print(download)#><span class="screen-reader-text"><?php _e('Download','builder-audio'); ?></span></a>
			    <# } #>
			    <# if( item.audio_url ) { #>
				<audio src="{{ item.audio_url }}"></audio>
			    <# } #>
			</li>
			<# } #>
		    </ol>
		</div>
            <# } #>
        </div>
        <?php
    }

}


if(method_exists('Themify_Builder_Model', 'add_module')){
    new TB_Audio_Module();
}
else{
    Themify_Builder_Model::register_module('TB_Audio_Module');
}
