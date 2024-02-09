<?php

/**
 * Custom meta box class to create gallery
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Gallery extends PTB_CMB_Base {

    private $data = array();

	public function __construct($type, $plugin_name, $version) {

		parent::__construct($type, $plugin_name, $version);
		if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
			add_filter('ptb_meta_gallery_exist', array($this,'gallery_exist'),10,3);
		}
	}

	public function get_assets() {
		return [
			'css' => [
				self::$plugin_name . '-' . $this->type => PTB::$uri . 'public/css/modules/gallery.css',
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
            'name' => __('Gallery', 'ptb')
        );

        return $cmb_types;
    }

    /**
     * Renders the meta boxes for themplates
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

        $default = empty($data);
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Gallery Layout', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_extra_gallery_mode">
                <input type="radio" id="ptb_<?php echo $id ?>_layout_grid"
                       name="[<?php echo $id ?>][layout]" value="grid"
                       <?php if ($default || ( isset($data['layout']) && $data['layout'] == 'grid' )): ?>checked="checked"<?php endif; ?>/>
                <label for="ptb_<?php echo $id ?>_layout_grid"><?php _e('Grid', 'ptb') ?></label>
                <input type="radio" id="ptb_<?php echo $id ?>_layout_showcase"
                       name="[<?php echo $id ?>][layout]" value="showcase"
                       <?php if (isset($data['layout']) && $data['layout'] == 'showcase'): ?>checked="checked"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>_layout_showcase"><?php _e('Showcase', 'ptb') ?></label>
                <input type="radio" id="ptb_<?php echo $id ?>_layout_lightbox"
                       name="[<?php echo $id ?>][layout]" value="lightbox"
                       <?php if (isset($data['layout']) && $data['layout'] == 'lightbox'): ?>checked="checked"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>_layout_lightbox"><?php _e('Lightbox', 'ptb') ?></label>
            </div>
        </div>
        <div class="ptb_back_active_module_row ptb_extra_gallery_columns">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[columns]"><?php _e('Columns', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[columns]" name="[<?php echo $id ?>][columns]">
                        <?php for ($i = 1; $i <= 9; ++$i): ?>
                            <option <?php if (isset($data['columns']) && $data['columns'] == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i ?>">
                                <?php echo $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
		<div class="ptb_back_active_module_row ptb_extra_gallery_layout">
            <div class="ptb_back_active_module_label">
                &nbsp;
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
				<input value="1" id="ptb_<?php echo $id ?>_masonry" type="checkbox"
					   name="[<?php echo $id ?>][masonry]"
					   <?php if (isset($data['masonry'])): ?>checked="checked"<?php endif; ?>  />
				<label for="ptb_<?php echo $id ?>_masonry"><?php _e('Enable masonry layout', 'ptb'); ?></label>
            </div>
        </div>
        <div class="ptb_back_active_module_row ptb_extra_gallery_size">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[size]"><?php _e('image dimension', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[size]" name="[<?php echo $id ?>][size]">
                        <?php foreach (get_intermediate_image_sizes() as $s): ?>
                            <?php
                            $width = get_option("{$s}_size_w");
                            $heigth = get_option("{$s}_size_h");
                            if ($width <= 0 || $heigth <= 0) {
                                continue;
                            }
                            ?>
                            <option <?php if (isset($data['size']) && $data['size'] == $s): ?>selected="selected"<?php endif; ?> value="<?php echo $s ?>">
                                <?php echo $s, ' ( ', $width, ' X ', $heigth, ' ) ' ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="f" <?php if (!isset($data['size']) || $data['size'] === 'f'): ?>selected="selected"<?php endif; ?>><?php _e('Full', 'ptb') ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Image Appearance', 'ptb') ?></label>
            </div>
            <?php
            if (!isset($data['assign']) || !is_array($data['assign'])) {
                $data['assign'] = !isset($data['assign']) ? array() : array($data['assign']);
            }
            ?>
            <div class="ptb_back_active_module_input ptb_back_text">
                <?php
                $assign = array('rounded' => __('Rounded', 'ptb'),
                    'drop-shadow' => __('Drop Shadow', 'ptb'),
                    'bordered' => __('Bordered', 'ptb'),
                    'circle' => __('Circle', 'ptb')
                );
                ?>
                <?php foreach ($assign as $key => $name): ?>
                    <input value="<?php echo $key ?>" id="ptb_<?php echo $id ?>_assign_<?php echo $key ?>" type="checkbox"
                           name="[<?php echo $id ?>][assign][]"
                           <?php if (isset($data['assign']) && in_array($key, $data['assign'])): ?>checked="checked"<?php endif; ?>  />
                    <label for="ptb_<?php echo $id ?>_assign_<?php echo $key ?>"><?php echo $name ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($type == PTB_Post_Type_Template::ARCHIVE): ?>
            <?php self::link_to_post($id, $type, $data, 'link', __('Only for layout Grid', 'ptb')); ?>
        <?php endif; ?>

        <?php
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
        $url_name = sprintf('%s[url][]', $meta_key);
        $image_link_name = sprintf('%s[link][]', $meta_key);
        $title_name = sprintf('%s[title][]', $meta_key);
        $description_name = sprintf('%s[description][]', $meta_key);
        ?>
        <fieldset class="ptb_cmb_input">
            <ul id="<?php echo $meta_key; ?>_options_wrapper" class="ptb_cmb_options_wrapper">
                <?php $values = is_array($value) && isset($value['url']) ? $value['url'] : array($value); ?>
                <?php foreach ($values as $index => $v): ?>
                    <?php
                    $v = esc_url_raw($v);
                    $style = $v ? sprintf('style="background-image:url(%s)"', $v) : '';
                    $link = !empty($value['link'][$index]) ? esc_attr($value['link'][$index]) : '';
                    $title =  !empty($value['title'][$index]) ? esc_attr($value['title'][$index]) : '';
                    $description = !empty($value['description'][$index]) ? esc_textarea($value['description'][$index]) : '';
                    ?>

                    <li class="<?php echo $meta_key; ?>_option_wrapper ptb_cmb_option">
                        <span class="ptb_cmb_option_sort"><?php echo PTB_Utils::get_icon( 'ti-split-v' ); ?></span>
                        <div class="ptb_post_cmb_image_wrapper">
                            <a href="#" class="ptb_post_cmb_image" <?php echo $style; ?>>
                                <?php echo PTB_Utils::get_icon( 'ti-plus' ); ?>
                            </a>
                        </div>
                        <input type="text" name="<?php echo $url_name; ?>"
                               value="<?php echo $v; ?>" placeholder="<?php _e('Image Url', 'ptb') ?>"/>
                        <input type="text" name="<?php echo $image_link_name; ?>"
                               value="<?php echo $link; ?>" placeholder="<?php _e('Image Link', 'ptb') ?>" class="ptb_extra_row_margin"/>
                        <input type="text" name="<?php echo $title_name; ?>"
                               value="<?php echo $title ?>" placeholder="<?php _e('Title', 'ptb') ?>" class="ptb_extra_row_margin"/>
                        <textarea name="<?php echo $description_name ?>" placeholder="<?php _e('Description', 'ptb') ?>"><?php echo $description ?></textarea>
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
        do_action('ptb_submission_slider', $post_type, $args, $module, $post, $lang, $languages);
    }

    public function ptb_submission_themplate($id, array $args, array $module, array $post_support, array $languages = array()) {
        do_action('ptb_submission_template_slider', $id, $args, $module, $post_support, $languages);
    }

    public function ptb_submission_validate($post_data, array $args, array $module, $post_type, $post_id, $lang, array $languages) {
        $error = FALSE;
        $key = $module['key'];
        $file = isset($_FILES[$key]) && isset($_FILES[$key]['tmp_name']) ? $_FILES[$key] : array();
        $data = $post_id && isset($post_data[$key]) ? $post_data[$key] : array();
        if (!isset($module['size'])) {
            $module['size'] = false;
        }
        if (empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $allow = PTB_Submissiion_Options::get_allow_ext($module['extensions']);
        $fsize = PTB_Submissiion_Options::max_upload_size($module['size']);
        if ($data) {
            $extensions = str_replace(',', '|', implode('|', array_keys($allow)));
        }
        $this->data[$key]['url'] = array();
        if (isset($post_data[$key . '_title'][$lang])) {
            $max = !empty($module['max']) ? (int)$module['max'] : false;
            if ($max === 0) {
                $max = false;
            }
            if ($max && count($post_data[$key . '_title'][$lang]) > $max) {
                $post_data[$key . '_title'][$lang] = array_slice($post_data[$key . '_title'][$lang], $max);
            }
            foreach ($post_data[$key . '_title'][$lang] as $k => $v) {
                $error = false;
                if (!empty($data[$k])) {
					$this->data[$key]['url'][$k] = esc_url_raw($data[$k]);
                } elseif (isset($file['tmp_name'][$k])) {
                    $f = array('name' => $file['name'][$k], 'size' => $file['size'][$k], 'tmp_name' => $file['tmp_name'][$k]);
                    $check = PTB_Submissiion_Options::validate_file($f, $allow, isset($module['size']) ? $module['size'] : NULL, $post_id);
                    if (!isset($check['error'])) {
                        $this->data[$key]['url'][$k] = $check['file']['url'];
                        PTB_Submission_Public::$files[$key][] = $check['file'];
                    } else {
                        $error = $check['error'];
                    }
                }

                if (isset($this->data[$key]['url'][$k])) {
                    foreach ($languages as $code => $lng) {
                        $this->data[$key]['title'][$code][$k] = isset($post_data[$key . '_title'][$code][$k]) ? sanitize_text_field($post_data[$key . '_title'][$code][$k]) : false;
                    }
                }
            }
        }
        if (isset($module['required']) && empty($this->data[$key]['url'])) {
            return $error ? $error : sprintf( __('%s is required', 'ptb'), PTB_Utils::get_label($args['name']) );
        }
        if (empty($this->data[$key]['url'])) {
            $post_data[$key]['url'] = array();
        }
        return $post_data;
    }

    public function ptb_submission_save(array $m, $key, array $post_data, $post_id, $lng) {
        return array(
			'url' => !empty($this->data[$key]['url']) ? $this->data[$key]['url'] : array(),
            'title' =>isset($this->data[$key]['title'][$lng]) ? $this->data[$key]['title'][$lng] : false
        );
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
	public function gallery_exist ( array $meta_query, $origk, array $cmb_option) {
		$meta_query['compare'] = 'NOT LIKE';
		$meta_query['value'] = '"url";a:1:{i:0;s:0:"";}';

		return $meta_query;
	}
}
