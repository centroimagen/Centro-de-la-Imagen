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
class PTB_CMB_File extends PTB_CMB_Base {

    private $data = array();

	public function __construct($type, $plugin_name, $version) {
		parent::__construct($type, $plugin_name, $version);
		if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
			add_filter('ptb_meta_file_exist', array($this,'file_exist'),10,3);
		}
	}

	public function get_assets() {
		return [
			'css' => [
				self::$plugin_name . '-' . $this->type => PTB::$uri . 'public/css/modules/file.css',
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
            'name' => __('File', 'ptb')
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
        $links = array('lightbox' => __('Lightbox', 'ptb'), 'new_window' => __('New Window'), '0' => __('No', 'ptb'));
        $show_icons = array('1'=>__('Yes','ptb'),'0'=>__('No','ptb'));
        $show_as = array('list'=>__('List','ptb'),'block'=>__('Block','ptb'));
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Open in', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <?php foreach ($links as $k => $v): ?>
                    <input type="radio" id="ptb_<?php echo $id ?>_radio_<?php echo $k ?>"
                           name="[<?php echo $id ?>][file_link]" value="<?php echo $k ?>"
                           <?php if ((!isset($data['file_link']) && $k === '0') || ( isset($data['file_link']) && $data['file_link'] == "$k")): ?>checked="checked"<?php endif; ?>/>
                    <label for="ptb_<?php echo $id ?>_radio_<?php echo $k ?>"><?php echo $v ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Show file icons', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <?php foreach ($show_icons as $k => $v): ?>
                    <input type="radio" id="ptb_<?php echo $id ?>_radio_<?php echo $k ?>"
                           name="[<?php echo $id ?>][show_icons]" value="<?php echo $k ?>"
                           <?php if ((!isset($data['show_icons']) && $k === '1') || ( isset($data['show_icons']) && $data['show_icons'] == "$k")): ?>checked="checked"<?php endif; ?>/>
                    <label for="ptb_<?php echo $id ?>_radio_<?php echo $k ?>"><?php echo $v ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[color]"><?php _e('Color', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="text" class="ptb_color_picker" name="[<?php echo $id ?>][color]" id="ptb_<?php echo $id ?>[color]" data-value="<?php echo isset($data['color']) && $data['color'] ? $data['color'] : '' ?>" />
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[show_as]"><?php _e('Show as', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <?php foreach ($show_as as $k => $v): ?>
                    <input type="radio" id="ptb_<?php echo $id ?>_radio_<?php echo $k ?>"
                           name="[<?php echo $id ?>][show_as]" value="<?php echo $k ?>"
                           <?php if ((!isset($data['show_as']) && $k === 'list') || ( isset($data['show_as']) && $data['show_as'] == "$k")): ?>checked="checked"<?php endif; ?>/>
                    <label for="ptb_<?php echo $id ?>_radio_<?php echo $k ?>"><?php echo $v ?></label>
                <?php endforeach; ?>
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
        $title_name = sprintf('%s[title][]', $meta_key);
        ?>
        <fieldset class="ptb_cmb_input">
            <ul id="<?php echo $meta_key ?>_options_wrapper" class="ptb_cmb_options_wrapper">
                <?php $values = is_array($value) && isset($value['url']) ? $value['url'] : array($value); ?>
                <?php foreach ($values as $index => $v): ?>
                    <?php
                    $v = esc_url_raw($v);
                    $style = false;
                    if($v){
                        $ext = pathinfo($v,PATHINFO_EXTENSION);
                        $style = ' ptb_uploaded ptb_extra_'.$ext;
                    }
                    $title = !empty($value['title'][$index]) ? esc_attr($value['title'][$index]) : '';
                    ?>

                    <li class="<?php echo $meta_key; ?>_option_wrapper ptb_cmb_option">
                        <span class="ptb_cmb_option_sort"><?php echo PTB_Utils::get_icon( 'ti-split-v' ); ?></span>
                        <div class="ptb_post_cmb_image_wrapper">
                            <a href="#" class="ptb_post_cmb_image<?php echo $style; ?>">
                                <?php echo PTB_Utils::get_icon( 'ti-plus' ); ?>
                            </a>
                        </div>

                        <input type="text" name="<?php echo $url_name; ?>"
                               value="<?php echo $v; ?>" placeholder="<?php _e('File Url', 'ptb') ?>"/>
                        <input type="text" name="<?php echo $title_name; ?>"
                               value="<?php echo $title ?>" placeholder="<?php _e('Title', 'ptb') ?>" class="ptb_extra_row_margin"/>
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
        if ( empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $size = PTB_Submissiion_Options::max_upload_size($module['size']);
        $can_be_allowed = array_keys(PTB_Submissiion_Options::get_allow_ext(array(),'application'));

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
                <br/>
                <?php
				_e( 'You can only chose the extensions that are allowed in your site.', 'ptb-submission' );
				echo ' ';
				if ( is_multisite() ) {
					printf( __( 'You can configure allowed extensions in your <a href="%s" target="blank">Network Settings</a>.', 'ptb-submission' ), admin_url( 'network/settings.php' ) );
				} else {
					printf( __( 'To add additional file formats to the list, you can use <a href="%s" target="blank">File Upload Types</a> or a similar plugin.', 'ptb-submission' ), 'https://wordpress.org/plugins/file-upload-types/' );
				}
				?>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_size"><?php _e('Maximum image size(b)', 'ptb-submission') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="number" name="[<?php echo $id ?>][size]" value="<?php echo $size ?>" min="1" max="<?php echo $max_upload ?>" id="ptb_<?php echo $id ?>_size" />
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
        PTB_Utils::enqueue_script( self::$plugin_name . '-submission-file', PTB_Submission::$url . 'public/js/field-file.js', array( 'ptb-submission' ), $this->get_plugin_version(), true );

		PTB_Utils::enqueue_style( 'ptb-submission-file', PTB_Submission::$url . 'public/css/field-file.css', [ 'ptb-submission' ] );

        $data = isset($post->ID) ? get_post_meta($post->ID, 'ptb_' . $args['key'], TRUE) : array();
        if (empty($data['url'])) {
            $data = array('url' => array(false));
            $title = array();
        } else {
            if (!$data['title']) {
                $data['title'] = array();
            }
            $title = $this->ptb_submission_lng_data($data['title'], $args['key'], 'title', $post->ID, $post_type, $languages);
        }
        if (!isset($module['size'])) {
            $module['size'] = false;
        }
        if (empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $module['extensions'] = array_keys(PTB_Submissiion_Options::get_allow_ext($module['extensions'], 'application'));
        
        $size = PTB_Submissiion_Options::max_upload_size($module['size']);
        $max = !empty($module['max'])?(int)$module['max']:false;
        if($max===0){
            $max = false;
        }
        $i = 0;
        ?>
        <div class="ptb_back_active_module_input ptb-submission-multi-text ptb_extra_submission_images ptb_extra_submission_files"<?php if($max):?> data-max="<?php echo $max?>"<?php endif;?>>
            <ul>
                <?php foreach ($data['url'] as $k => $v): ?>
                    <?php if($max && $i>$max):?>
                        <?php break;?>
                    <?php endif;?>
                    <li class="ptb-submission-text-option">
                        <span title="<?php _e('Sort', 'ptb') ?>" class="ptb-submission-option-sort"></span>
                        <div class="ptb-submission-priview-wrap">
                            <div class="ptb-submission-priview">
                                <?php if($v):?>
                                    <input type="hidden" value="<?php echo esc_url_raw($v) ?>" name="submission[<?php echo $args['key'] ?>][f]" />
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="ptb_back_active_module_input">
                            <div class="ptb_extra_image_title"><label data-label="<?php _e('Upload File', 'ptb') ?>" class="ptb-submission-upload-btn" for="ptb_submission_<?php echo $args['key'] ?>"><span class="ptb-submission-upload-btn-iconup"><?php echo PTB_Utils::get_icon( 'fa-upload' ); ?></span><?php echo $v?pathinfo($v,PATHINFO_FILENAME):__('Upload File', 'ptb') ?></label></div>
                            <?php PTB_CMB_Base::module_language_tabs('submission', isset($title[$k]) ? $title[$k] : array(), $languages, $args['key'] . '_title', 'text', __('File Title', 'ptb'), true); ?>
                            <div class="ptb-submission-file-wrap">
                                <input data-extension="<?php echo esc_attr(str_replace(',', '|', implode('|', $module['extensions']))) ?>" data-size="<?php echo $size ?>" data-width="80" data-height="80" id="ptb_submission_<?php echo $args['key'] ?>" class="ptb-submission-file" type="file" name="<?php echo $args['key'] ?>" />
                            </div>
                            <span title="<?php _e('Remove', 'ptb') ?>" class="ptb-submission-remove"></span>
                        </div>
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
        $error = false;
        $key = $module['key'];
        $file = isset($_FILES[$key]['tmp_name']) ? $_FILES[$key] : array();
        $data = $post_id && isset($post_data[$key]['f']) ? $post_data[$key]['f'] : array();
        if (!isset($module['size'])) {
            $module['size'] = false;
        }
        if (empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $allow = PTB_Submissiion_Options::get_allow_ext($module['extensions'], 'application');
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
                    $f = esc_url_raw($data[$k]);
					$this->data[$key]['url'][$k] = $f;
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
                        $this->data[$key]['title'][$code][$k] =  isset($post_data[$key . '_title'][$code][$k]) ? sanitize_text_field($post_data[$key . '_title'][$code][$k]) : false;
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

        return array('url' =>  !empty($this->data[$key]['url']) ? $this->data[$key]['url'] : array(),
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
	public function file_exist ( array $meta_query, $origk, array $cmb_option) {
		$meta_query['compare'] = 'NOT LIKE';
		$meta_query['value'] = '"url";a:1:{i:0;s:0:"";}';

		return $meta_query;
	}

	public static function admin_column_display( $value ) {
		$output = '';
		if ( ! empty( $value['url'] ) ) {
			foreach ( $value['url'] as $i => $item ) {
				$output .= ! empty( $value['title'][ $i ] ) ? sprintf( '%s [%s]', $value['title'][ $i ], $item ) : $item;
			}
		}

		return $output;
	}
}
