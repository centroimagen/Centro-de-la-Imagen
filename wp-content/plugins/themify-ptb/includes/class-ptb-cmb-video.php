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
class PTB_CMB_Video extends PTB_CMB_Base {

    private $data = array();

    public function __construct($type, $plugin_name, $version) {
	    add_filter('ptb_meta_video_exist', array($this,'video_exist'),10,3);
        parent::__construct($type, $plugin_name, $version);
    }

	public function get_assets() {
		return [
			'css' => [
				self::$plugin_name . '-' . $this->type => PTB::$uri . 'public/css/modules/video.css',
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
            'name' => __('Video', 'ptb')
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
                <label for="ptb_<?php echo $id ?>[columns]"><?php _e('Columns', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[columns]" name="[<?php echo $id ?>][columns]">
                        <?php for ($i = 1; $i <= 4; ++$i): ?>
                            <option <?php if (isset($data['columns']) && $data['columns'] == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i ?>">
                                <?php echo $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[preview]"><?php _e('Show Image Preview', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_extra_video_preview_wrap">
                <input class="ptb_extra_video_preview" type="checkbox" id="ptb_<?php echo $id ?>[preview]"
                       name="[<?php echo $id ?>][preview]" value="1"
                       <?php if ($default || !empty($data['preview'])): ?>checked="checked"<?php endif; ?>
                       />
            </div>
        </div>
        <div class="ptb_back_active_module_row ptb_extra_video_link">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[lightbox]"><?php _e('Open in Lightbox', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input id="ptb_<?php echo $id ?>[lightbox]" type="checkbox"
                       class="ptb_extra_lightbox" name="[<?php echo $id ?>][lightbox]"
                       <?php if (!empty($data['lightbox'])): ?>checked="checked"<?php endif; ?>  />
            </div>
        </div>
        <?php if ($type == PTB_Post_Type_Template::ARCHIVE): ?>
        <div class="ptb_back_active_module_row ptb_extra_video_permalink">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Link to post permalink', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="radio" id="ptb_<?php echo $id ?>_link_yes"
                       name="[<?php echo $id ?>][link]" value="1"
                       <?php if (!isset($data['link']) || !empty($data['link'])): ?>checked="checked"<?php endif; ?>/>
                <label for="ptb_<?php echo $id ?>_link_yes"><?php _e('Yes', 'ptb') ?></label>
                <input type="radio" id="ptb_<?php echo $id ?>_link_no"
                       name="[<?php echo $id ?>][link]" value="0"
                       <?php if (isset($data['link']) && $data['link'] == 0): ?>checked="checked"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>_link_no"><?php _e('No', 'ptb') ?></label>
            </div>
        </div>
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
        $title_name = sprintf('%s[title][]', $meta_key);
        $description_name = sprintf('%s[description][]', $meta_key);
        ?>
        <fieldset class="ptb_cmb_input">
            <ul id="<?php echo $meta_key; ?>_options_wrapper" class="ptb_cmb_options_wrapper">
                <?php $values = is_array($value) && isset($value['url']) ? $value['url'] : array($value); 
                    if(!isset($values[0])){
                        $values = array(0=>'');
                    }
                ?>
                <?php foreach ($values as $index => $v): ?>
                    <?php
                    $title =  isset($value['title'][$index]) ? esc_attr($value['title'][$index]) : '';
                    $description = isset($value['description'][$index]) ? esc_textarea($value['description'][$index]) : '';
                    ?>

                    <li class="<?php echo $meta_key; ?>_option_wrapper ptb_cmb_option">
                        <span class="ptb_cmb_option_sort"><?php echo PTB_Utils::get_icon( 'ti-split-v' ); ?></span>
                        <div class="ptb_post_cmb_image_wrapper">
                            <a href="#" class="ptb_post_cmb_image <?php if ($v): ?>ptb_uploaded<?php endif; ?>">
                                <?php echo PTB_Utils::get_icon( 'ti-plus' ); ?>
                            </a>
                        </div>
                        <input type="text" name="<?php echo $url_name; ?>" value="<?php echo esc_url_raw($v); ?>" placeholder="<?php _e('Video Url(youtube/vimeo)', 'ptb') ?>"/>
                        <input type="text" name="<?php echo $title_name; ?>" value="<?php esc_attr_e($title) ?>" placeholder="<?php _e('Title', 'ptb') ?>" class="ptb_extra_row_margin"/>
                        <textarea name="<?php echo $description_name ?>" placeholder="<?php _e('Description', 'ptb') ?>"><?php echo $description; ?></textarea>
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


    /*
     * parse_video_url() PHP function
     * Author: takien, slaffko
     * URL: http://takien.com, http://slaffko.name
     *
     * @param string $url URL to be parsed, eg:
     * http://vimeo.com/1515151
     * http://youtu.be/zc0s358b3Ys,
     * http://www.youtube.com/embed/zc0s358b3Ys
     * http://www.youtube.com/watch?v=zc0s358b3Ys
     * @param string $return what to return
     * - embed, return embed code
     * - thumb, return URL to thumbnail image
     * - thumbmed, return URL to thumbnail mediul image(for vimeo only)
     * - hqthumb, return URL to high quality thumbnail image.
     * @param string $width width of embeded video, default 560
     * @param string $height height of embeded video, default 349
     * @param string $rel whether embeded video to show related video after play or not.
     */

    public static function parse_video_url($url, $return = 'embed', $width = '100%', $height = '100%', $rel = 0, $check = false) {
        $urls = parse_url($url);
        if (!isset($urls['path'])) {
            return false;
        }
        $vid = $yid = false;
        //url is http://vimeo.com/xxxx
        if ($urls['host'] === 'vimeo.com') {
            $v = ltrim($urls['path'], '/');
            $v = explode('/', $v);
            $vid = end($v);
        }
        //url is http://youtu.be/xxxx
        else if ($urls['host'] === 'youtu.be') {
            $yid = ltrim($urls['path'], '/');
        }
        //url is http://www.youtube.com/embed/xxxx
        else if (strpos($urls['path'], 'embed') === 1) {
            $arr=explode('/', $urls['path']);
            $yid = end($arr);
            unset($arr);
        }
        //url is xxxx only
        elseif (strpos($url, '/') === false) {
            $yid = $url;
        }
       
        //http://www.youtube.com/watch?feature=player_embedded&v=m-t4pcO99gI
        //url is http://www.youtube.com/watch?v=xxxx
        else {
            parse_str($urls['query'],$v);
            $yid = isset($v['v']) ? $v['v'] : '';
            if (!empty($v['feature'])) {
                $arr=explode('v=', $urls['query']);
                $yid = end($arr);
                $arr = explode('&', $yid);
                $yid = $arr[0];
            }
        } 
        if ($check) {
            return $vid || $yid;
        }
        if ($yid) {

            //return embed iframe
            if ($return === 'embed') {
                return array('url' => '//www.youtube.com/embed/' . $yid . '?rel=' . $rel, 'data' => '<iframe width="' . $width . '" height="' . $height . '" src="//www.youtube.com/embed/' . $yid . '?rel=' . $rel . '" frameborder="0" ebkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
            }
            //return normal thumb
            else if ($return === 'thumb' || $return === 'thumbmed') {
                return array('url' => '//www.youtube.com/embed/' . $yid . '?rel=' . $rel, 'data' => '//i1.ytimg.com/vi/' . $yid . '/default.jpg');
            }
            //return hqthumb
            else if ($return === 'hqthumb') {
                return array('url' => '//www.youtube.com/embed/' . $yid . '?rel=' . $rel, 'data' => '//i1.ytimg.com/vi/' . $yid . '/hqdefault.jpg');
            } else {
                return false;
            }
        } else if ($vid) {

            $video = wp_safe_remote_get("https://vimeo.com/api/v2/video/" . $vid . ".json");

            if (!$video || !isset($video['body']) || $video['response']['code'] === '404') {
                return FALSE;
            }
            $vimeoObject = json_decode($video['body']);
            if (!empty($vimeoObject)) {
                //return embed iframe
                if ($return === 'embed') {
                    return array('url' => '//player.vimeo.com/video/' . $vid . '?title=0&byline=0&portrait=0', 'data' => '<iframe width="' . ($width ? $width : $vimeoObject[0]->width) . '" height="' . ($height ? $height : $vimeoObject[0]->height) . '" src="//player.vimeo.com/video/' . $vid . '?title=0&byline=0&portrait=0" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
                }
                //return normal thumb
                else if ($return === 'thumb') {
                    return array('url' => '//player.vimeo.com/video/' . $vid . '?title=0&byline=0&portrait=0', 'data' => $vimeoObject[0]->thumbnail_small);
                }
                //return medium thumb
                else if ($return === 'thumbmed') {
                    return array('url' => '//player.vimeo.com/video/' . $vid . '?title=0&byline=0&portrait=0', 'data' => $vimeoObject[0]->thumbnail_medium);
                }
                //return hqthumb
                else if ($return === 'hqthumb') {
                    return array('url' => '//player.vimeo.com/video/' . $vid . '?title=0&byline=0&portrait=0', 'data' => $vimeoObject[0]->thumbnail_large);
                }
            }
        }
        return FALSE;
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
        $can_be_allowed = array_keys(PTB_Submissiion_Options::get_allow_ext(array(), 'video'));
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
                        <option <?php echo $all ? 'disabled="disabled"' : (in_array($ext, $module['extensions'],true) ? 'selected="selected"' : '') ?>  value="<?php echo $ext ?>"><?php echo $ext ?></option>
                    <?php endforeach; ?>
                </select>
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
        if ( ! wp_script_is( self::$plugin_name . '-submission-video' ) ) {
            $pluginurl = plugin_dir_url(dirname(__FILE__));
            $translation_ = array(
                'video_error' => __('Incorrect (Youtube/Vimeo) URL', 'ptb-submission')
            );
            PTB_Utils::enqueue_script( self::$plugin_name . '-fileupload-video', PTB_Submission::$url . 'public/js/jquery.fileupload-video.min.js', array( 'ptb-submission-fileupload-image' ), $this->get_plugin_version(), true );
            PTB_Utils::enqueue_script( self::$plugin_name . '-submission-video', PTB_Submission::$url . 'public/js/field-video.js', array( 'ptb-submission' ), $this->get_plugin_version(), true );
            wp_localize_script(self::$plugin_name . '-submission-video', 'ptb_submission_video', $translation_);
        }

		PTB_Utils::enqueue_style( 'ptb-submission-file', PTB_Submission::$url . 'public/css/field-file.css', [ 'ptb-submission' ] );

        $data = isset($post->ID) ? get_post_meta($post->ID, 'ptb_' . $args['key'], TRUE) : array();
        if ( empty($data['url'])) {
            $data = array('url' => array(false));
            $title = array();
        } else {
            if (!$data['title']) {
                $data['title'] = array();
            }
            if (!$data['description']) {
                $data['description'] = array();
            }
            $title = $this->ptb_submission_lng_data($data['title'], $args['key'], 'title', $post->ID, $post_type, $languages);
            $desc = $this->ptb_submission_lng_data($data['description'], $args['key'], 'description', $post->ID, $post_type, $languages);
        }
        if (!isset($module['size'])) {
            $module['size'] = false;
        }
        if ( empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $module['extensions'] = array_keys(PTB_Submissiion_Options::get_allow_ext($module['extensions'], 'video'));
        $size = PTB_Submissiion_Options::max_upload_size($module['size']);
        $max = !empty($module['max'])?(int)$module['max']:false;
        if($max===0){
            $max = false;
        }
        $i = 0;
        ?>
        <div class="ptb_back_active_module_input ptb-submission-multi-text ptb_extra_submission_images ptb_extra_submission_video"<?php if($max):?> data-max="<?php echo $max?>"<?php endif;?>>
            <ul>
                <?php foreach ($data['url'] as $k => $v): ?>
                    <?php if($max && $i>$max):?>
                        <?php break;?>
                    <?php endif;?>
                    <?php $video = $v && strpos($v, 'youtube.com') === false && strpos($v, 'vimeo.com') === false; ?>
                    <li class="ptb-submission-text-option">
                        <span title="<?php _e('Sort', 'ptb') ?>" class="ptb-submission-option-sort"></span>
                        <div class="ptb-submission-priview-wrap">
                            <div class="ptb-submission-priview">
                                <?php if ($video): ?>
                                    <video src="<?php echo $v ?>" width="80" height="80"></video>
                                    <input type="hidden" value="<?php echo esc_url_raw($v) ?>" name="submission[<?php echo $args['key'] ?>][f]" />
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ptb_back_active_module_input">
                            <div class="ptb_extra_image_title"><label data-label="<?php _e('Upload Video', 'ptb') ?>" class="ptb-submission-upload-btn" for="ptb_submission_<?php echo $args['key'] ?>"><span class="ptb-submission-upload-btn-iconup"><?php echo PTB_Utils::get_icon( 'fa-upload' ); ?></span><?php _e('Upload Video', 'ptb') ?></label></div>
                            <input class="ptb_extra_video_url" value="<?php echo!$video && $v ? $v : '' ?>" type="text" placeholder="<?php _e('Or video Url(Youtube/Vimeo)', 'ptb') ?>" name="submission[<?php echo $args['key'] ?>][url]" />
                            <div class="ptb-submission-file-wrap">
                                <input data-extension="<?php echo esc_attr(str_replace(',', '|', implode('|', $module['extensions']))) ?>" data-size="<?php echo $size ?>" data-width="80" data-height="80" id="ptb_submission_<?php echo $args['key'] ?>" class="ptb-submission-file" type="file" name="<?php echo $args['key'] ?>" />
                            </div>
                        </div>
                        <?php PTB_CMB_Base::module_language_tabs('submission', isset($title[$k]) ? $title[$k] : array(), $languages, $args['key'] . '_title', 'text', __('Video Title', 'ptb'), true); ?>
                        <?php PTB_CMB_Base::module_language_tabs('submission', isset($desc[$k]) ? $desc[$k] : array(), $languages, $args['key'] . '_description', 'textarea', __('Video Description', 'ptb'), true); ?>
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
        $error = false;
        $key = $module['key'];
        $file = isset($_FILES[$key]['tmp_name']) ? $_FILES[$key] : array();
        $data = $post_id && isset($post_data[$key]['f']) ? $post_data[$key]['f'] : array(); /* holds uploaded videos */
        $url = isset($post_data[$key]['url']) ? $post_data[$key]['url'] : array();
        if (!isset($module['size'])) {
            $module['size'] = false;
        }
        if ( empty($module['extensions'])) {
            $module['extensions'] = array('all');
        }
        $allow = PTB_Submissiion_Options::get_allow_ext($module['extensions'], 'video');
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
                if (!empty($url[$k])) {
					/* Youtube/Vimeo videos */
                    $f = esc_url_raw($url[$k]);
                    if ($f && self::parse_video_url($f, false, false, false, false, true)) {
                        $this->data[$key]['url'][$k] = $f;
                    }
                } elseif (!empty($data[$k])) {
					/* previsouly uploaded videos */
                    $f = esc_url_raw($data[$k]);
					$this->data[$key]['url'][$k] = $f;
                } elseif (isset($file['tmp_name'][$k])) {
					/* new video file */
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
                        $this->data[$key]['description'][$code][$k] = isset($post_data[$key . '_description'][$code][$k]) ? $post_data[$key . '_description'][$code][$k] : false;
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
        return array('url' => !empty($this->data[$key]['url']) ? $this->data[$key]['url'] : array(),
            'title' => isset($this->data[$key]['title'][$lng]) ? $this->data[$key]['title'][$lng] : false,
            'description' => isset($this->data[$key]['description'][$lng]) ? $this->data[$key]['description'][$lng] : false
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
	public function video_exist ( array $meta_query, $origk, array $cmb_option) {
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
