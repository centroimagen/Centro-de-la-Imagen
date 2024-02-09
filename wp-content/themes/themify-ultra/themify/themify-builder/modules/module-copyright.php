<?php
defined('ABSPATH') || exit;

/**
 * Module Name: Copyright
 * Description: Display copyright text
 */
class TB_Copyright_Module extends Themify_Builder_Component_Module {

	public function __construct() {
		parent::__construct('copyright');
	}

	public function get_name() {
		return __('Copyright', 'themify');
	}

	public function get_title($module) {
		return isset($module['mod_settings']['text']) ? wp_trim_words($module['mod_settings']['text'], 100) : '';
	}

	public function get_icon() {
		return false;
	}

	public function get_assets() {
		return [];
	}

	public function get_options() {
		return array(
			array(
				'id' => 'title',
				'type' => 'title'
			),
			array(
				'id' => 'text',
				'type' => 'textarea',
				'label' => __('Copyright Text', 'themify'),
			),
			array(
				'type' => 'template_fields',
				'target' => 'text',
				'fields' => ['%site_name%', '%site_description%', '%site_url%', '%year%'],
				'title' => __('Available Fields', 'themify'),
			),
			array('type' => 'custom_css_id', 'custom_css' => 'add_css_text'),
		);
	}

	public function get_live_default() {
		return array(
			'text' => '© <a href="%site_url%">%site_name%</a> %year%',
		);
	}

	public function get_styling() {
		$general = array(
			//background
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
							self::get_color_type(),
							self::get_font_size(),
							self::get_font_style('', 'f_fs_g', 'f_fw_g'),
							self::get_line_height(),
							self::get_text_align(),
							self::get_text_shadow(),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family('', 'f_f', 'h'),
							self::get_color_type('', 'h'),
							self::get_font_size('', 'f_s', '', 'h'),
							self::get_font_style('', 'f_fs_g', 'f_fw_g', 'h'),
							self::get_text_shadow('', 't_sh', 'h'),
						)
					)
				))
			)),
			// Link
			self::get_expand('l', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_color(' a', 'link_color'),
							self::get_text_decoration(' a')
						)
					),
					'h' => array(
						'options' => array(
							self::get_color(' a', 'link_color', null, null, 'hover'),
							self::get_text_decoration(' a', 't_d', 'h')
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
				self::get_width('', 'w')
			)),
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

		return array(
			'type' => 'tabs',
			'options' => array(
				'g' => array(
					'options' => $general
				),
				'm_t' => array(
					'options' => $this->module_title_custom_style()
				),
			)
		);
	}

	protected function _visual_template() {
		$module_args = self::get_module_args('title');
		?>
		<#
		const vars = {
			site_name : '<?php echo get_bloginfo('name'); ?>',
			site_description : '<?php echo get_bloginfo('description'); ?>',
			site_url : '<?php echo home_url(); ?>',
			year : '<?php echo wp_date('Y'); ?>'
		};
		for ( let [key, value] of Object.entries( vars ) ) {
			data.text = data.text.replace( '%' + key + '%', value );
		}
		#>
		<div class="module module-<?php echo $this->slug; ?> {{ data.add_css_text }}">
			<# if ( data.title ) { #>
				<?php echo $module_args['before_title']; ?>{{{ data.title }}}<?php echo $module_args['after_title']; ?>
			<# } #>
			<div class="tb_copyright">{{{ data.text }}}</div>
		</div>
		<?php
	}
}

new TB_Copyright_Module();
