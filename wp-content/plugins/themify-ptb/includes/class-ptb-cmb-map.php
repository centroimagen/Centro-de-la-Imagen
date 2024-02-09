<?php

/**
 * Custom meta box class to create google map
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Map extends PTB_CMB_Base {

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
            'name' => __('Map', 'ptb')
        );
        return $cmb_types;
    }
    
    public function __construct($type, $plugin_name, $version) {
        parent::__construct($type, $plugin_name, $version);
        if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
            add_action('ptb_search_map',array($this,'search_map_template'),10,8);
            add_filter('ptb_search_by_map',array($this,'search_map'),10,6);
	        add_filter('ptb_meta_map_exist', array($this,'map_exist'),10,3);
        }
		add_action('ptb_submission_template_map', array($this, 'ptb_submission_themplate'), 10, 5);
    }

	public function get_assets() {
		return [
			'css' => [
				self::$plugin_name . '-' . $this->type => PTB::$uri . 'public/css/modules/map.css',
			],
		];
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
        if ($default) {
            $data['width'] = 100;
            $data['height'] = 300;
        }
        $dimenission = array('%', 'px');
        $road_types = array('ROADMAP' => __('Road Map', 'ptb'),
            'SATELLITE' => __('Satellite', 'ptb'),
            'HYBRID' => __('Hybrid', 'ptb'),
            'TERRAIN' => __('Terrain', 'ptb')
        );
        $settings = array(0 => __('Disable', 'ptb'), 1 => __('Enable', 'ptb'));
        $display = array('map' => __('Map', 'ptb'), 'text' => __('Address Text', 'ptb') );
        ?>
		<div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[displayAs]"><?php _e('Display As', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <?php foreach ($display as $l => $n): ?>
                    <input type="radio" id="ptb_<?php echo $id ?>_radio_<?php echo $l ?>"
                           name="[<?php echo $id ?>][displayAs]" value="<?php echo $l ?>"
                           <?php if ((!isset($data['displayAs']) && $l === 'map') || ( isset($data['displayAs']) && $data['displayAs'] == "$l")): ?>checked="checked"<?php endif; ?>/>
                    <label for="ptb_<?php echo $id ?>_radio_<?php echo $l ?>"><?php echo $n ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[zoom]"><?php _e('Zoom', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[zoom]"
                            name="[<?php echo $id ?>][zoom]">
                                <?php for ($i = 8; $i <= 17; ++$i): ?>
                            <option <?php if (isset($data['zoom']) && $data['zoom'] == $i): ?>selected="selected"<?php endif; ?>value="<?php echo $i ?>"><?php echo $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[width]"><?php _e('Width', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <input type="text" id="ptb_<?php echo $id ?>[width]"
                       name="[<?php echo $id ?>][width]" value="<?php echo $data['width'] > 0 ? floatval($data['width']) : '0' ?>"
                       />
                <div class="ptb_custom_select">
                    <select name="[<?php echo $id ?>][width_t]">
                        <?php foreach ($dimenission as $d): ?>
                            <option <?php if (isset($data['width_t']) && $data['width_t'] === $d): ?>selected="selected"<?php endif; ?>value="<?php echo $d ?>"><?php echo $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[height]"><?php _e('Height', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <input type="text" id="ptb_<?php echo $id ?>[height]"
                       name="[<?php echo $id ?>][height]" value="<?php echo $data['height'] > 0 ? floatval($data['height']) : '0' ?>"
                       />px
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[road_type]"><?php _e('Type', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[road_type]" name="[<?php echo $id ?>][road_type]">
                        <?php foreach ($road_types as $k => $r): ?>
                            <option <?php if (isset($data['road_type']) && $data['road_type'] === $k): ?>selected="selected"<?php endif; ?>value="<?php echo $k ?>"><?php echo $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[scroll]"><?php _e('Scrollwheel', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[scroll]" name="[<?php echo $id ?>][scroll]">
                        <?php foreach ($settings as $k => $r): ?>
                            <option <?php if (isset($data['scroll']) && $data['scroll'] == $k): ?>selected="selected"<?php endif; ?>value="<?php echo $k ?>"><?php echo $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[drag]"><?php _e('Draggable', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[drag]" name="[<?php echo $id ?>][drag]">
                        <?php foreach ($settings as $k => $r): ?>
                            <option <?php if (isset($data['drag']) && $data['drag'] == $k): ?>selected="selected"<?php endif; ?>value="<?php echo $k ?>"><?php echo $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <?php
        $settings[0] = __('Yes', 'ptb');
        $settings[1] = __('No', 'ptb');
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[drag_m]"><?php _e('Disable draggable on mobile', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[drag_m]" name="[<?php echo $id ?>][drag_m]">
                        <?php foreach ($settings as $k => $r): ?>
                            <option <?php if (isset($data['drag_m']) && $data['drag_m'] == $k): ?>selected="selected"<?php endif; ?>value="<?php echo $k ?>"><?php echo $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[default]"><?php _e('Default Address', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input name="[<?php echo $id ?>][can_be_empty]" type="hidden" value="1" />
                <input class="ptb_towidth" type="text" id="ptb_<?php echo $id ?>[default]"
                       name="[<?php echo $id ?>][default]" value="<?php echo isset($data['default'])?esc_attr($data['default']):''?>"
                       />
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
        ?>  
        <input type="hidden" id="ptb_extra_<?php echo $meta_key ?>_place" value="<?php echo isset($value['place']) ? esc_attr($value['place']) : '' ?>" name="<?php echo $meta_key ?>[place]"/>
        <input type="text"  class="ptb_extra_map_controls" id="ptb_extra_<?php echo $meta_key ?>_location"  placeholder="<?php _e('Enter a location', 'ptb') ?>" />
        <select id="ptb_extra_<?php echo $meta_key ?>_select" class="ptb_extra_map_select">
            <option><?php _e('All', 'ptb') ?></option>
            <option value="establishment"><?php _e('Establishments', 'ptb') ?></option>
            <option value="address"><?php _e('Addresses', 'ptb') ?></option>
            <option value="geocode"><?php _e('Geocodes', 'ptb') ?></option>
        </select>
        <div class="ptb_extra_map_canvas" id="ptb_extra_<?php echo $meta_key ?>_canvas"></div>
        <table class="ptb_extra_map_info" width="100%">
            <?php if ( ! PTB::get_option()->get_google_map_key() ) : ?>
            <tr>
                <td colspan="2">
                    <span class="ptb_extra_map_missing"><?php _e('Error: Google Maps API key is missing','ptb') ?></span>
                    <a target="_blank" href="<?php echo add_query_arg('page','ptb-settings', admin_url('admin.php') ); ?>"><?php _e('Add Map Key','ptb') ?></a>
                </td>
            </tr>
            <?php endif;?>
            <tr>
                <td><label for="ptb_extra_<?php echo $meta_key ?>_info"><?php _e('Info window', 'ptb') ?></label></td>
                <td><textarea name="<?php echo $meta_key ?>[info]" id="ptb_extra_<?php echo $meta_key ?>_info"><?php echo isset($value['info']) ? $value['info'] : '' ?></textarea></td>
            </tr>
            <tr>
                <td><label for="ptb_extra_<?php echo $meta_key ?>_lat"><?php _e('Lat,Long', 'ptb') ?></label></td>
                <td><input id="ptb_extra_<?php echo $meta_key ?>_lat" type="text" value="" /></td>
            </tr>
        </table>
        <?php
    }
	
	public function ptb_submission_themplate($id, array $args, array $module, array $post_support, array $languages = array()) {
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[default]"><?php _e('Default Address', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input class="ptb_towidth" type="text" id="ptb_<?php echo $id ?>[default]"
                       name="[<?php echo $id ?>][default]" value="<?php echo isset($module['default'])?esc_attr($module['default']):''?>"
                       />
            </div>
        </div>
        <?php
    }

    public function ptb_submission_form($post_type, array $args, array $module, $post, $lang, $languages) {
        PTB_Utils::enqueue_script( self::$plugin_name . '-submission-map', PTB_Submission::$url . 'public/js/field-map.js', array( 'ptb-submission' ), $this->get_plugin_version(), true );

		PTB_Utils::enqueue_style( 'ptb-submission-map', PTB_Submission::$url . 'public/css/field-map.css', [ 'ptb-submission' ] );

        if (isset($post->ID)) {
            $data = get_post_meta($post->ID, 'ptb_' . $args['key'], TRUE);
            $desc = isset($data['info']) ? $this->ptb_submission_lng_data(array($data['info']), $args['key'], 'info', $post->ID, $post_type, $languages) : array();
            $desc = $desc ? current($desc) : array();
        } else {
            $data = $desc = array();
        }
		$default_data = '';
		if ( !empty($module['default']) ) {
			$default_data = $module['default'];
		}
        ?>  
        <div class="ptb_back_active_module_input">
            <div class="ptb_extras_submission_map" data-id="<?php echo $args['key'] ?>" data-default="<?php esc_attr_e($default_data) ?>" id="ptb_extra_<?php echo $args['key'] ?>_canvas"></div>
            <div class="ptb_extra_submission_map_control">
                <input type="hidden" id="ptb_extra_<?php echo $args['key'] ?>_place" value="<?php echo isset($data['place']) ? esc_attr($data['place']) : '' ?>" name="submission[<?php echo $args['key'] ?>][place]"/>
                <input type="text"  class="ptb_extra_map_controls" id="ptb_extra_<?php echo $args['key'] ?>_location"  placeholder="<?php _e('Enter a location', 'ptb') ?>" />
                <?php PTB_CMB_Base::module_language_tabs('submission', $desc, $languages, $args['key'] . '_info', 'textarea', __('Info window', 'ptb'), true); ?>
            </div>
			<?php if ( isset( $module['show_description'] ) ) : ?>
				<div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label( $args['description'] ); ?></div>
			<?php endif; ?>
        </div>
        <?php
    }

    public function ptb_submission_validate(array $post_data, array $args, array $module, $post_type, $post_id, $lang, array $languages) {
        $value = false;
        if (isset($post_data[$module['key']]['place'])) {
            $value = json_decode(stripslashes($post_data[$module['key']]['place']), TRUE);
            if (!isset($value['place']) && !isset($value['location'])) {
                $value = false;
            }
        }
        if (!$value && isset($module['required'])) {
            return sprintf( __( '%s is required', 'ptb' ), PTB_Utils::get_label($args['name']) );
        }
        return $post_data;
    }

    public function ptb_submission_save(array $m, $key, array $post_data, $post_id, $lng) {
        $m['value']['place'] = sanitize_text_field($m['value']['place']);
        if (!empty($post_data[$key . '_info'][$lng])) {
            $m['value']['info'] = esc_textarea($post_data[$key . '_info'][$lng]);
        }
        return $m;
    }
    
     public function search_map_template($post_type,$id,$args,$module,$value,$label,$lang,$languages){
        $name =  PTB_Utils::get_label($args['name']);
        $name = $name ? sanitize_title($name) : $args['key'];
        $options = array();
        PTB_Public::$shortcode = true;

		/* assets for Map field in Search form, located in PTB Search plugin */
        PTB_Utils::enqueue_style( self::$plugin_name . '-search-map', PTB_Search_Public::$url . 'public/css/field-map.css' );
        PTB_Utils::enqueue_style( self::$plugin_name . '-search-map', PTB_Search_Public::$url . 'public/js/field-map.js', array( 'ptb-search' ), $this->get_plugin_version(), true );

        $query_args = array(
            'post_type' => $post_type,
            'fields'=>'ids',
            'posts_per_page' => -1,
            'post_status'=>'publish',
            'orderby'=>'ID',
            'order'=>'ASC',
            'meta_query' => array(
                array(
                    'key' =>'ptb_'. $args['key'],
                    'compare' => 'EXISTS'
                ),
            )
        );
        $posts = get_posts($query_args);
        if(!empty($posts)){
            foreach($posts as $p){
                $cf_value = get_post_meta($p,'ptb_'.$args['key'],true);
                $value = json_decode($cf_value['place'],true);
                $label = ! empty( $cf_value['info'] ) ? $cf_value['info'] : ( ! empty( $value['lat'] ) ? $value['lat'] . ',' . $value['lng'] : '' );
                $value = empty($value['place']) ?array('place'=>$value['place']):$value['location'];
                $options[base64_encode(wp_json_encode($value))] = $label;
            }
        }
        wp_reset_postdata();
        PTB_Public::$shortcode = false;
        PTB_Search_Public::show_as('select', $post_type, $id, $name, $value, $args['key'], $label,$options);
    }
    
    public function search_map($post_id,$post_type,$value,$args,$meta_key,$post_taxonomies){
        if($value){
            $value = json_decode(base64_decode($value),true);
            if($value && (isset($value['place']) || (isset($value['lat']) && isset($value['lng'])))){
                $condition = array();
                if(!empty($post_id)){
                    $condition[] = 'post_id IN('. implode(',', array_keys($post_id)).')'; 
                }
                $post_id = array();
                if(isset($value['place'])){
                    $value = esc_sql($value['place']);
                    $condition[] = "LOCATE('{$value}',`meta_value`)>0";
                }
                else{
                    $lat = esc_sql($value['lat']);
                    $lng = esc_sql($value['lng']);
                    $condition[] = "LOCATE('{$lat}',`meta_value`)>0 AND LOCATE('{$lng}',`meta_value`)>0";
                }
                $condition = implode(' AND ',$condition);
                global $wpdb;
                $get_values = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$meta_key' AND $condition");
                if (!empty($get_values)) {
                    $ids = array();
                    foreach ($get_values as $val) {
                        $ids[] = $val->post_id;
                    }
                    $ids = implode(',', $ids);

                    $get_posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                    if (!empty($get_posts)) {
                        $post_id = array();
                        foreach ($get_posts as $p) {
                            $post_id[$p->ID] = 1;
                        }
                    }
                }
            }
        }
        return $post_id;
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
	public function map_exist ( array $meta_query, $origk, array $cmb_option) {
		$meta_query['compare'] = 'NOT LIKE';
		$meta_query['value'] = '"place";s:0:""';

		return $meta_query;
	}

	public static function admin_column_display( $value, $field_def ) {
		if ( ! empty( $value['place'] ) ) {
			$place = json_decode( $value['place'], true );
			if ( ! empty( $place['location']['lat'] ) && ! empty( $place['location']['lng'] ) ) {
				$output = sprintf( '<a href="https://www.google.com/maps/@%1$s,%2$s,8z" target="blank">', $place['location']['lat'], $place['location']['lng'] );
				$output .= ! empty( $value['info'] ) ? $value['info'] : sprintf( '%s, %s', $place['location']['lat'], $place['location']['lng'] );
				return $output .= '</a>';
			}
		}
	}
}