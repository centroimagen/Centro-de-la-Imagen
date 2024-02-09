<?php

/**
 * Custom meta box class to create slider
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Slider extends PTB_CMB_Base {

    private $data = array();

	public function __construct($type, $plugin_name, $version) {

		parent::__construct($type, $plugin_name, $version);
		if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
			add_filter('ptb_meta_slider_exist', array($this,'slider_exist'),10,3);
		}
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
            'name' => __('Slider', 'ptb')
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
        $speed = array('200' => __('Slow', 'ptb'), '400' => __('Normal', 'ptb'), '600' => __('Fast', 'ptb'));
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[minSlides]"><?php _e('Visible', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[minSlides]"
                            name="[<?php echo $id ?>][minSlides]">
                                <?php for ($i = 1; $i < 8; ++$i): ?>
                            <option <?php if (isset($data['minSlides']) && $data['minSlides'] == $i): ?>selected="selected"<?php endif; ?>value="<?php echo $i ?>"><?php echo $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <span class="ptb_option_desc"><?php _e('The minimum number of slides to be shown.', 'ptb') ?></span>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[speed]"><?php _e('Speed', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[speed]" name="[<?php echo $id ?>][speed]">
                        <?php foreach ($speed as $k => $v): ?>
                            <option <?php if (isset($data['speed']) && $data['speed'] == $k): ?>selected="selected"<?php endif; ?>value="<?php echo $k ?>"><?php echo $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[autoHover]"><?php _e('Pause On Hover', 'ptb') ?></label>
            </div>
            <div value="1" class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[autoHover]" name="[<?php echo $id ?>][autoHover]">
                        <option <?php if (!empty($data['autoHover'])): ?>selected="selected"<?php endif; ?> value="1"><?php _e('Yes', 'ptb') ?></option>
                        <option <?php if (isset($data['autoHover']) && !$data['autoHover']): ?>selected="selected"<?php endif; ?> value="0"><?php _e('No', 'ptb') ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[captions]"><?php _e('Show Caption', 'ptb') ?></label>
            </div>
            <div value="1" class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[captions]" name="[<?php echo $id ?>][captions]">
                        <option <?php if (isset($data['captions']) && !$data['captions']): ?>selected="selected"<?php endif; ?> value="0"><?php _e('No', 'ptb') ?></option>
                        <option <?php if (!empty($data['captions'])): ?>selected="selected"<?php endif; ?> value="1"><?php _e('Yes', 'ptb') ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[pause]"><?php _e('Auto Scroll', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[pause]" name="[<?php echo $id ?>][pause]">
                        <?php for ($i = 0; $i <= 10; ++$i): ?>
                            <option <?php if ((!isset($data['pause']) && $i === 3) || (isset($data['pause']) && $data['pause'] == $i)): ?>selected="selected"<?php endif; ?>value="<?php echo $i ?>">
                                <?php if ($i === 0): ?>
                                    <?php _e('Off', 'ptb') ?>
                                <?php else: ?>
                                    <?php echo $i ?> <?php _e('sec', 'ptb') ?>
                                <?php endif; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <span class="ptb_option_desc"><?php _e('The amount of time between each auto transition', 'ptb') ?></span>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Show slider pagination', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[pager]" name="[<?php echo $id ?>][pager]">
                        <option <?php if (!empty($data['pager'])): ?>selected="selected"<?php endif; ?> value="1"><?php _e('Yes', 'ptb') ?></option>
                        <option <?php if (isset($data['pager']) && !$data['pager']): ?>selected="selected"<?php endif; ?> value="0"><?php _e('No', 'ptb') ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[controls]"><?php _e('Show slider arrow buttons', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[controls]" name="[<?php echo $id ?>][controls]">
                        <option <?php if (!empty($data['controls'])): ?>selected="selected"<?php endif; ?> value="1"><?php _e('Yes', 'ptb') ?></option>
                        <option <?php if (isset($data['controls']) && !$data['controls']): ?>selected="selected"<?php endif; ?> value="0"><?php _e('No', 'ptb') ?></option>
                    </select>
                </div>
            </div>
        </div>
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
        $link_name = sprintf('%s[link][]', $meta_key);
        $title_name = sprintf('%s[title][]', $meta_key);
        $description_name = sprintf('%s[description][]', $meta_key);
        ?>
        <fieldset class="ptb_cmb_input">
            <ul id="<?php echo $meta_key ?>_options_wrapper" class="ptb_cmb_options_wrapper">
                <?php $values = is_array($value) && isset($value['url']) ? $value['url'] : array($value); ?>
                <?php foreach ($values as $index => $v): ?>
                    <?php
                    $v = esc_url($v);
                    $video = $v && in_array(pathinfo($v,PATHINFO_EXTENSION),array('mp4','wmv','m4v','ogv','webm','mov','avi','flv','mpg','mpeg','mpe','qt'),true);
                    $style = $v && !$video? sprintf('style="background-image:url(%s)"', $v) : '';
                    $title = !empty($value['title'][$index]) ? esc_attr($value['title'][$index]) : '';
                    $link = !empty($value['link'][$index]) ? esc_url($value['link'][$index]) : '';
                    $description = !empty($value['description'][$index]) ? esc_textarea($value['description'][$index]) : '';
                    ?>

                    <li class="<?php echo $meta_key; ?>_option_wrapper ptb_cmb_option">
                        <span class="ptb_cmb_option_sort"><?php echo PTB_Utils::get_icon( 'ti-split-v' ); ?></span>
                        <div class="ptb_post_cmb_image_wrapper">
                            <a href="#" class="ptb_post_cmb_image<?php echo $video?' ptb_uploaded':''?>" <?php echo $style; ?>>
                                <?php echo PTB_Utils::get_icon( 'ti-plus' ); ?>
                            </a>
                        </div>
                        <input type="text" name="<?php echo $url_name; ?>"
                               value="<?php echo $v; ?>" placeholder="<?php _e('Image/Video Url', 'ptb') ?>"/>
                        <input type="text" name="<?php echo $title_name; ?>"
                               value="<?php echo $title ?>" placeholder="<?php _e('Title', 'ptb') ?>" class="ptb_extra_row_margin"/>
                        <input type="text" name="<?php echo $link_name; ?>"
                               value="<?php echo $link; ?>" placeholder="<?php _e('Link', 'ptb') ?>" class="ptb_extra_row_margin"/>
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

    public function ptb_submission_themplate($id, array $args, array $module, array $post_support, array $languages = array()) {
        $max_upload = wp_max_upload_size();
        if (!isset($module['size'])) {
            $module['size'] = $max_upload;
        }
        if (empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $size = PTB_Submissiion_Options::max_upload_size($module['size']);
		$can_be_allowed = array_keys(PTB_Submissiion_Options::get_allow_ext(array(), 'imageVideo'));

        $all = in_array('all', $module['extensions'],true);
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_extensions"><?php _e('Allowed extensions', 'ptb-submission') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <select size="10" class="ptb-select" multiple="multiple" name="[<?php echo $id ?>][extensions][arr]" id="ptb_<?php echo $id ?>_extensions">
                    <option <?php if ($all): ?>selected="selected"<?php endif; ?> value="all"><?php _e('ALL', 'ptb-submission') ?></option>
                    <?php foreach ($can_be_allowed as $ext): ?>
                        <option  <?php echo $all ? 'disabled="disabled"' : (in_array($ext, $module['extensions'],true) ? 'selected="selected"' : '') ?>  value="<?php echo $ext ?>"><?php echo $ext ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_size"><?php _e('Maximum image size(b)', 'ptb-submission') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="number" name="[<?php echo $id ?>][size]" value="<?php echo $size ?>" min="1" max="<?php echo $max_upload ?>" id="ptb_<?php echo $id ?>_size"/>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_max"><?php _e('Maximum files upload', 'ptb-submission') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb-submission-small-input">
                    <input type="number" name="[<?php echo $id ?>][max]" value="<?php echo !empty($module['max'])?(int)$module['max']:0 ?>" min="0"  id="ptb_<?php echo $id ?>_max" />
                    <small class="ptb-submission-small-description"><?php _e('Set "0" to unlimit file upload','ptb-submission')?></small>
                </div>
            </div>
        </div>
        <?php
    }

    public function ptb_submission_form($post_type, array $args, array $module, $post, $lang, $languages) {
		PTB_Utils::enqueue_style( 'ptb-submission-slider', PTB_Submission::$url . 'public/css/field-slider.css', [ 'ptb-submission' ] );

        $data = isset($post->ID) ? get_post_meta($post->ID, 'ptb_' . $args['key'], TRUE) : array();
        if (empty($data['url'])) {
            $data = array('url' => array(false));
            $title = array();
        } else {
            $title = $this->ptb_submission_lng_data($data['title'], $args['key'], 'title', $post->ID, $post_type, $languages);
        }
        if (!isset($module['size'])) {
            $module['size'] = false;
        }
        if (empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $module['extensions'] = array_keys(PTB_Submissiion_Options::get_allow_ext($module['extensions'], 'imageVideo'));
        $size = PTB_Submissiion_Options::max_upload_size($module['size']);
        $max = !empty($module['max'])?(int)$module['max']:false;
        if($max===0){
            $max = false;
        }
        $i = 0;
        ?>
        <div class="ptb_back_active_module_input ptb-submission-multi-text ptb_extra_submission_images"<?php if($max):?> data-max="<?php echo $max?>"<?php endif;?>>
            <ul>
                <?php foreach ($data['url'] as $k => $v): ?>
                    <?php if($max && $i>$max):?>
                        <?php break;?>
                    <?php endif;?>
                    <li class="ptb-submission-text-option">
                        <span title="<?php _e('Sort', 'ptb') ?>" class="ptb-submission-option-sort"></span>
                        <div class="ptb-submission-priview-wrap">
                            <div class="ptb-submission-priview">
                                <?php if ($v): ?>
                                    <img src="<?php echo esc_url_raw($v) ?>" width="80" height="80" />
                                    <input type="hidden" value="<?php echo $v ?>" name="submission[<?php echo $args['key'] ?>]" />
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ptb_back_active_module_input">
                            <div class="ptb_extra_image_title"><label data-label="<?php _e('Upload Image', 'ptb') ?>" class="ptb-submission-upload-btn" for="ptb_submission_<?php echo $args['key'] ?>"><span class="ptb-submission-upload-btn-iconup"><?php echo PTB_Utils::get_icon( 'fa-upload' ); ?></span><?php _e('Upload Image', 'ptb') ?></label></div>
                            <?php PTB_CMB_Base::module_language_tabs('submission', isset($title[$k]) ? $title[$k] : array(), $languages, $args['key'] . '_title', 'text', __('Image Title', 'ptb'), true); ?>
                            <div class="ptb-submission-file-wrap">
                                <input data-extension="<?php echo esc_attr(str_replace(',', '|', implode('|', $module['extensions']))) ?>" data-size="<?php echo $size ?>"  data-width="80" data-height="80" id="ptb_submission_<?php echo $args['key'] ?>" class="ptb-submission-file" type="file" name="<?php echo $args['key'] ?>" />
                            </div>
                        </div>
                        <span title="<?php _e('Remove', 'ptb') ?>" class="ptb-submission-remove"></span>
                    </li>
                    <?php ++$i;?>
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

        $error = FALSE;
        $key = $module['key'];
        $file = isset($_FILES[$key]['tmp_name']) ? $_FILES[$key] : array();
        $data = $post_id && isset($post_data[$key]) ? $post_data[$key] : array();
        if (!isset($module['size'])) {
            $module['size'] = false;
        }
        if (empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $allow = PTB_Submissiion_Options::get_allow_ext($module['extensions'], 'imageVideo');
        $fsize = PTB_Submissiion_Options::max_upload_size($module['size']);
        if ($data) {
            $extensions = str_replace(',', '|', implode('|', array_keys($allow)));
        }
        $this->data[$key]['url'] = array();
        if (isset($post_data[$key . '_title'][$lang])) {
            $max = !empty($module['max'])?(int)$module['max']:false;
            if($max===0){
                $max = false;
            }
            if($max && count($post_data[$key . '_title'][$lang])>$max){
                $post_data[$key . '_title'][$lang] = array_slice($post_data[$key . '_title'][$lang], $max);
            }
            foreach ($post_data[$key . '_title'][$lang] as $k => $v) {
                $error = false;
                if (!empty($data[$k])) {
                    $this->data[$key]['url'][$k] = esc_url_raw($data[$k]);
                } elseif (isset($file['tmp_name'][$k])) {
                    $f = array('name' => $file['name'][$k], 'size' => $file['size'][$k], 'tmp_name' => $file['tmp_name'][$k]);
                    $check = PTB_Submissiion_Options::validate_file($f, $allow, $fsize, $post_id);
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
        if(empty($this->data[$key]['url'])){
            $post_data[$key]['url'] = array();
        }
        return $post_data;
    }

    public function ptb_submission_save(array $m, $key, array $post_data, $post_id, $lng) {
        return array(
			'url' => ! empty( $this->data[$key]['url'] ) ? $this->data[$key]['url'] : array(),
            'title' => isset($this->data[$key]['title'][$lng]) ? $this->data[$key]['title'][$lng] : false
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
	public function slider_exist ( array $meta_query, $origk, array $cmb_option) {
		$meta_query['compare'] = 'NOT LIKE';
		$meta_query['value'] = '"url";a:1:{i:0;s:0:"";}';

		return $meta_query;
	}
}
