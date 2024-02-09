<?php

/**
 * Custom meta box class to create icon
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Icon extends PTB_CMB_Base {

	public function __construct($type, $plugin_name, $version) {
		parent::__construct($type, $plugin_name, $version);
		if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
			add_filter('ptb_meta_icon_exist', array($this,'icon_exist'),10,3);
		}
	}

    private $icons = array();
    private $icon_labels = array();

	public function get_assets() {
		return [
			'css' => [
				self::$plugin_name . '-' . $this->type => PTB::$uri . 'public/css/modules/icons.css',
			],
		];
	}

    /**
     * Adds the custom meta type to the plugin meta types array
     *
     * @since 1.0.0
     *
     * @param array $cmb_types Array of custom meta types of plugin
     *
     * @return array
     */
    public function filter_register_custom_meta_box_type($cmb_types) {

        $cmb_types[$this->get_type()] = array(
            'name' => __('Icon', 'ptb')
        );

        return $cmb_types;
    }

    /**
     * Renders the meta boxes for themplate
     *
     * @since 1.0.0
     *
     * @param string $id the metabox id
     * @param string $type the type of the page(Arhive or Single)
     * @param array $args Array of custom meta types of plugin
     * @param array $data saved data
     * @param array $languages languages array
     */
    public function action_them_themplate($id, $type, $args, $data = array(), array $languages = array()) {
        $sizes = array('small' => __('Small', 'ptb'), 'medium' => __('Medium', 'ptb'), 'large' => __('Large', 'ptb'));
        $links = array('lightbox' => __('Lightbox', 'ptb'), 'new_window' => __('New Window'), '0' => __('No', 'ptb'));
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[size]"><?php _e('Size', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[size]"
                            name="[<?php echo $id ?>][size]">
                                <?php foreach ($sizes as $s => $name): ?>
                            <option <?php if (isset($data['size']) && $data['size'] === $s): ?>selected="selected"<?php endif; ?>value="<?php echo $s ?>"><?php echo $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[lightbox]"><?php _e('Open in', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <?php foreach ($links as $l => $n): ?>
                    <input type="radio" id="ptb_<?php echo $id ?>_radio_<?php echo $l ?>"
                           name="[<?php echo $id ?>][icon_link]" value="<?php echo $l ?>"
                           <?php if ((!isset($data['icon_link']) && $l == '0') || ( isset($data['icon_link']) && $data['icon_link'] == "$l")): ?>checked="checked"<?php endif; ?>/>
                    <label for="ptb_<?php echo $id ?>_radio_<?php echo $l ?>"><?php echo $n ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * @param string $id the id template
     * @param array $languages
     */
    public function action_template_type($id, array $languages) {
        
    }

    /**
     * Renders the meta boxes on post edit dashboard
     *
     * @since 1.0.0
     *
     * @param WP_Post $post
     * @param string $meta_key
     * @param array $args
     */
    public function render_post_type_meta($object, $meta_key, $args) {
        $value = PTB_Utils::get_meta_value( $object, $meta_key );
        $icon_name = sprintf('%s[icon][]', $meta_key);
        $url_name = sprintf('%s[url][]', $meta_key);
        $title_name = sprintf('%s[label][]', $meta_key);
        $color_name = sprintf('%s[color][]', $meta_key);
        $plugin_dir = plugin_dir_url(dirname(__FILE__, 2));
        ?>
        <fieldset class="ptb_cmb_input">
            <ul id="<?php echo $meta_key; ?>_options_wrapper" class="ptb_cmb_options_wrapper">
                <?php $values = is_array($value) && isset($value['icon']) ? $value['icon'] : array($value); ?>
                <?php foreach ($values as $index => $v): ?>
                    <?php
                    $label = !empty($value['label'][$index]) ? esc_attr($value['label'][$index]) : '';
                    $url = !empty($value['url'][$index]) ? esc_url($value['url'][$index]) : '';
                    $color = !empty($value['color'][$index]) ? esc_attr($value['color'][$index]) : '';
                    ?>
                    <li class="<?php echo $meta_key; ?>_option_wrapper ptb_cmb_option" data-ptb_icon_picker_container>
                        <span class="ptb_cmb_option_sort"><?php echo PTB_Utils::get_icon( 'ti-split-v' ); ?></span>
                        <div class="ptb_post_cmb_image_wrapper">
                            <a <?php if ($color): ?>style="color:<?php echo $color; ?>"<?php endif; ?> title="<?php _e('Choose icon', 'ptb') ?>" href="#" class="ptb_icon_picker ptb_post_cmb_image <?php if ($v) echo $v; ?>" data-ptb_icon_picker data-ptb_icon_picker_preview>
                                <?php echo PTB_Utils::get_icon( $v ? $v : 'ti-plus' ); ?>
                            </a>
                        </div>
                        <input type="text" readonly value="<?php esc_attr_e($v); ?>" placeholder="<?php _e('Icon', 'ptb') ?>" class="ptb_extra_input_icon_holder" data-ptb_icon_picker data-ptb_icon_picker_value />
                        <input type="hidden" value="<?php esc_attr_e($v); ?>" name="<?php echo $icon_name ?>" class="ptb_extra_input_icon" data-ptb_icon_picker_value />
                        <input type="text" name="<?php echo $title_name; ?>" value="<?php esc_attr_e($label); ?>" placeholder="<?php _e('Label', 'ptb') ?>"  class="ptb_extra_row_margin" />
                        <input type="text" name="<?php echo $url_name; ?>" value="<?php echo esc_url_raw($url) ?>" placeholder="<?php _e('Link', 'ptb') ?>"/>
                        <input class="ptb_color_picker" value="<?php echo $color ?>" type="text" placeholder="<?php _e('Color', 'ptb') ?>" name="<?php echo $color_name; ?>" />
                        <span class="<?php echo $meta_key; ?>_remove remove"><?php echo PTB_Utils::get_icon( 'ti-close' ); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div id="<?php echo $meta_key; ?>_add_new" class="ptb_cmb_option_add">
                <?php echo PTB_Utils::get_icon( 'ti-plus' ); ?>
                <?php _e('Add new', 'ptb') ?>
            </div>
        </fieldset>

        <?php
    }

    public function ptb_submission_form($post_type, array $args, array $module, $post, $lang, $languages) {
		self::register_common_assets();

		PTB_Utils::enqueue_script( self::$plugin_name . '-submission-icon-color', PTB::$uri . 'admin/js/jquery/jquery.minicolors.min.js', array( 'ptb-submission' ), $this->get_plugin_version(), true );
        PTB_Utils::enqueue_script( self::$plugin_name . '-submission-icon', PTB_Submission::$url . 'public/js/field-icon.js', array( 'ptb-submission', self::$plugin_name . '-submission-icon-color', self::$plugin_name . '-common' ), $this->get_plugin_version(), true );

		/* @note: doesn't use PTB_Utils::enqueue_style because in Themify theme it causes an error with image path */
		wp_enqueue_style( self::$plugin_name . '-submission-icon-color', PTB::$uri . 'admin/css/jquery/jquery.minicolors.min.css' );
		PTB_Utils::enqueue_style( 'ptb-submission-icon', PTB_Submission::$url . 'public/css/field-icon.css', [ 'ptb-submission', self::$plugin_name . '-common' ] );

        $data = isset($post->ID) ? get_post_meta($post->ID, 'ptb_' . $args['key'], TRUE) : array();
        if (empty($data)) {
            $data = array('icon' => array(false));
            $title = array();
        } else {
            $title = $this->ptb_submission_lng_data($data['label'], $args['key'], 'label', $post->ID, $post_type, $languages);
        }
        $dir = plugin_dir_url(dirname(__FILE__, 2)) ;
        ?>
        <div class="ptb_back_active_module_input ptb-submission-multi-text ptb_extra_submission_images">
            <ul>
                <?php foreach ($data['icon'] as $k => $v): ?>
                    <?php
					$v = esc_attr($v);
                    $color = !empty($data['color'][$k]) ? esc_attr($data['color'][$k]) : '';
                    ?>
                    <li class="ptb-submission-text-option" data-ptb_icon_picker_container>
                        <span title="<?php _e('Sort', 'ptb') ?>" class="ptb-submission-option-sort"></span>
                        <div class="ptb_back_active_module_input ptb_icon_wrap">
                            <div>
                                <a title="<?php _e('Choose icon', 'ptb') ?>"  rel="nofollow" href="#" class="ptb_extra_submission_icon ptb_icon_picker" data-ptb_icon_picker data-ptb_icon_picker_preview>
									<span class="ptb_extra_submission_plus_icon"><?php echo PTB_Utils::get_icon( $v ? $v : 'ti-plus' ); ?></span>
                                </a>
                            </div>
                            <div>
                                <input type="text" readonly value="<?php echo $v; ?>" name="submission[<?php echo $args['key'] ?>][icon]" placeholder="<?php _e('Icon', 'ptb') ?>" data-ptb_icon_picker data-ptb_icon_picker_value />
                                <?php PTB_CMB_Base::module_language_tabs('submission', isset($title[$k]) ? $title[$k] : array(), $languages, $args['key'] . '_label', 'text', __('Label', 'ptb'), true); ?>
                                <input type="text" value="<?php echo !empty($data['url'][$k]) ? esc_url_raw($data['url'][$k]) : '' ?>" name="submission[<?php echo $args['key'] ?>][url]" placeholder="<?php _e('Link', 'ptb') ?>" />
                                <input class="ptb_extra_color_picker" value="<?php echo $color ?>" type="text" placeholder="<?php _e('Color', 'ptb') ?>" name="submission[<?php echo $args['key'] ?>][color]" />
                            </div>
                        </div>
                        <span title="<?php _e('Remove', 'ptb') ?>" class="ptb-submission-remove"></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="ptb-submission-option-add">
                <span class="ptb_submission_add_icon"></span>
                <?php _e('Add new', 'ptb') ?>                           
            </div>
            <?php if (isset($module['show_description'])): ?>
                <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function ptb_submission_validate(array $post_data, array $args, array $module, $post_type, $post_id, $lang, array $languages) {
        $key = $module['key'];
		$this->icons[$key] = array();
        if (isset($post_data[$key]['icon']) && is_array($post_data[$key]['icon'])) {
			$this->icons[$key] = $post_data[$key];
			$this->icon_labels[$key] = $post_data[$key . '_label'];
		}
        if (empty($this->icons[$key]) && isset($module['required'])) {
            return sprintf( __('%s is required', 'ptb'), PTB_Utils::get_label($args['name']) );
        }
        return $post_data;
    }

    public function ptb_submission_save(array $m, $key, array $post_data, $post_id, $lng) {
        if ( ! empty( $this->icons[$key]['icon'] ) ) {
			$m['value'] = [];
            foreach ( $this->icons[$key]['icon'] as $i => $icon ) {
                $m['value']['icon'][$i] = $icon;
                $m['value']['color'][$i] = isset( $this->icons[$key]['color'][ $i ] ) && strpos( $this->icons[$key]['color'][ $i ], '#') !== false ? sanitize_text_field( $this->icons[$key]['color'][ $i ] ) : '';
                $m['value']['url'][$i] = isset( $this->icons[$key]['url'][ $i ] ) ? sanitize_text_field( $this->icons[$key]['url'][ $i ] ) : '';
                $m['value']['label'][$i] = isset( $this->icon_labels[$key][ $lng ][ $i ] ) ? sanitize_text_field( $this->icon_labels[$key][ $lng ][ $i ] ) : '';
            }
        } else {
            return array();
        }
        return $m;
    }

	/**
	 * Filter empty fields
	 *
	 * @since 1.5.8
	 *
	 * @param array $meta_query
	 * @param string $origk
	 * @param array $cmb_option
	 *
	 * @return array
	 */
	public function icon_exist ( array $meta_query, $origk, array $cmb_option) {
		$meta_query['compare'] = 'NOT LIKE';
		$meta_query['value'] = '"icon";a:1:{i:0;s:0:"";}';

		return $meta_query;
	}

	public static function admin_column_display( $value ) {
		$output = '';
		if ( ! empty( $value['icon'] ) ) {
			foreach ( $value['icon'] as $i => $item ) {
				$style = ! empty( $value['color'][ $i ] ) ? sprintf( ' style="color: %s"', esc_attr( $value['color'][ $i ] ) ) : '';
				$output .= '<span' . $style . '>';
				$output .= PTB_Utils::get_icon( $item );
				if ( ! empty( $value['label'][ $i ] ) ) {
					$output .= ' ' . $value['label'][ $i ];
				}
				$output .= '</span><br>';
			}
		}

		return $output;
	}
}
