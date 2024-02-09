<?php

/**
 * Custom meta box class to create rating stars
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Rating extends PTB_CMB_Base {

    public $uid = false;
    public $ip = false;

    public function __construct($type, $plugin_name, $version) {
        if (is_admin()) {
            add_action( 'save_post', array( $this, 'remove_rate_data' ) );
            add_filter('ptb_template_modules', array($this, 'is_editable'), 10, 3);
        } else {
            add_filter('ptb_submission_render', array($this, 'is_editable'), 10, 3);
        }
        if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
            add_action('ptb_search_rating',array($this,'search_rating_template'),10,8);
            add_filter('ptb_search_by_rating',array($this,'search_rating'),10,6);
        }
        $this->ip = self::getClientIP();
        add_action('wp_ajax_ptb_extra_rate_voted', array($this, 'voted'));
        add_action('wp_ajax_nopriv_ptb_extra_rate_voted', array($this, 'voted'));
        parent::__construct($type, $plugin_name, $version);
    }

	public function get_assets() {
		return [
			'css' => [
				self::$plugin_name . '-' . $this->type => PTB::$uri . 'public/css/modules/rating.css',
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
            'name' => __('Rating', 'ptb')
        );
        return $cmb_types;
    }

    /**
     * @param string $id the id template
     * @param array $languages
     */
    public function action_template_type($id, array $languages) {
        ?>
        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_stars_count" class="ptb_cmb_input_label">
                <?php _e("Rating Count", 'ptb'); ?>
            </label>
            <div class="ptb_cmb_input">
                <select id="<?php echo $id; ?>_stars_count" name="<?php echo $id; ?>_stars_count">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i ?>"><?php echo $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_admin" class="ptb_cmb_input_label">
                <?php _e("Who can rate", 'ptb'); ?>
            </label>
            <fieldset class="ptb_cmb_input">
                <label for="<?php echo $id; ?>_admin">
                    <input type="radio" id="<?php echo $id; ?>_admin"
                           name="<?php echo $id; ?>_readonly" value="1" checked="checked"/>
                    <span><?php _e("Admin/Editor", 'ptb'); ?></span>
                </label>&nbsp;&nbsp;
                <label for="<?php echo $id; ?>_public">
                    <input type="radio" id="<?php echo $id; ?>_public"
                           name="<?php echo $id; ?>_readonly" value="0" />
                    <span><?php _e("Public Visitors", 'ptb'); ?></span>
                </label><br/>
            </fieldset>
        </div>
		<div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_many" class="ptb_cmb_input_label">
                <?php _e("Disable Rating Schema", 'ptb-relation'); ?>
            </label>
            <fieldset class="ptb_cmb_input">
                <input type="checkbox" id="<?php echo $id; ?>_schema" name="<?php echo $id; ?>_schema" value="1"/>
            </fieldset>
        </div>
        <?php
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
        $sizes = array('small' => __('Small', 'ptb'), 'medium' => __('Meduim', 'ptb'), 'large' => __('Large', 'ptb'));
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[icon]"><?php _e('Icon', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input" data-ptb_icon_picker_container>
                <input type="text" name="[<?php echo $id ?>][icon]" id="ptb_<?php echo $id ?>[icon]" value="<?php echo !empty($data['icon'])? $data['icon'] : 'fa-star' ?>" data-ptb_icon_picker_value />
                <a title="<?php _e('Icon Picker', 'ptb') ?>" class="ptb_icon_picker" href="#" data-ptb_icon_picker><?php _e('Icon', 'ptb') ?></a>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[size]"><?php _e('Icon Size', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>[size]" name="[<?php echo $id ?>][size]">
                        <?php foreach ($sizes as $s => $name): ?>
                            <option value="<?php echo $s ?>" <?php if (isset($data['size']) && $data['size'] === $s): ?>selected="selected"<?php endif; ?>><?php echo $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <span class="fa"></span>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[vcolor]"><?php _e('Voted Color', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="text" class="ptb_color_picker" name="[<?php echo $id ?>][vcolor]" id="ptb_<?php echo $id ?>[vcolor]" data-value="<?php echo !empty($data['vcolor']) ? $data['vcolor'] : '' ?>" />
            </div>
        </div>
        <?php if (!isset($args['readonly']) || !$args['readonly']): ?>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[hcolor]"><?php _e('Hover Color', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="text" class="ptb_color_picker" name="[<?php echo $id ?>][hcolor]" id="ptb_<?php echo $id ?>[hcolor]" data-value="<?php echo !empty($data['hcolor'])? $data['hcolor'] : '' ?>" />
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[before_confirmation]"><?php _e("Confirmation Before Rating", 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input class="ptb_rating_confirm" type="checkbox"  id="ptb_<?php echo $id ?>[before_confirmation]"
                           name="[<?php echo $id ?>][before_confirmation]" value="1" <?php echo !empty($data['before_confirmation']) ? 'checked="checked"' : '' ?>
                           />
                    <div>
                        <?php self::module_language_tabs($id, $data, $languages, 'before_confirmation_text'); ?>
                        <div class="ptb_rate_desc"><?php _e("You can use '#rated_value#' in your text to show current voted value", 'ptb') ?></div>
                    </div>
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[after_confirmation]"><?php _e("Confirmation After Rating", 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input class="ptb_rating_confirm" type="checkbox"  id="ptb_<?php echo $id ?>[after_confirmation]"
                           name="[<?php echo $id ?>][after_confirmation]" value="1" <?php echo !empty($data['after_confirmation']) ? 'checked="checked"' : '' ?>
                           />
                    <div>
                        <?php self::module_language_tabs($id, $data, $languages, 'after_confirmation_text'); ?>
                        <div class="ptb_rate_desc"><?php _e("You can use '#rated_value#' in your text to show current voted value", 'ptb') ?></div>
                    </div>
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[show_vote]"><?php _e('Show Vote Count', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="checkbox" name="[<?php echo $id ?>][show_vote]" id="ptb_<?php echo $id ?>[show_vote]" value="1" />
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
        $readonly = !empty($args['readonly']);
        $name = sprintf('%s', $meta_key);
        ?>
        <?php if ( ! $readonly ) :
            if ( ! is_array( $value ) ) {
                $value = array('count' => 0, 'total' => 0);
            } else if ( empty( $value['total'] ) ) {
				$value['total'] = 0;
			}
			?>
            <div class="ptb_extra_rating_table">
                <table>
                    <tr>
                        <td><strong><?php _e('Click', 'ptb') ?></strong></td>
                        <td><?php _e('How many times users have clicked', 'ptb') ?></td>
                        <td><input type="text" value="<?php echo $value['count'] ?>" name="ptb_rating[<?php echo $meta_key ?>][count]" /></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Sum', 'ptb') ?></strong></td>
                        <td><?php _e('Total sum of the rating', 'ptb') ?></td>
                        <td><input type="text" value="<?php echo $value['total'] ?>" name="ptb_rating[<?php echo $meta_key ?>][total]" /></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Rating', 'ptb') ?></strong></td>
                        <td><?php echo $value['count'] > 0 ? floatval($value['total'] / $value['count']) : 0 ?></td>
                    </tr>
                </table>
            </div>
        <?php else: ?>
            <?php if (is_array($value)): ?>
                <?php $value = $value['count'] > 0 ? floatval($value['total'] / $value['count']) : 0 ?>
            <?php endif; ?>
            <input id="ptb_extra_<?php echo $meta_key; ?>" 
                   type="number" max="<?php echo (int)$args['stars_count'] ?>" 
                   min="0"
                   step="1"
                   name="<?php echo $name; ?>"
                   value="<?php echo $value ? floatval($value) : '0' ?>"/>
               <?php endif; ?>

        <?php
    }

    /* Save post hook handler
     * Remove rating data of the post if user checked the checkbox
     *
     * @since 1.0.0
     */
    public function remove_rate_data($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        remove_action( 'save_post', array( $this, 'remove_rate_data' ) );
        if (!empty($_POST['ptb_rating'])) {
            foreach ($_POST['ptb_rating'] as $key => $rate) {
                $key = sanitize_key($key);
                $prevvalue = get_post_meta($post_id, $key, true);
                if (!$prevvalue || !is_array($prevvalue)) {
                    $value = $prevvalue = array('total' => 0, 'count' => 0, 'users' => array(), 'ip' => array());
                }
                $value['total'] = floatval($rate['total']);
                $value['count'] = (int)$rate['count'];
                update_post_meta($post_id, "ptb_{$key}", $value,$prevvalue);
                update_post_meta( $post_id, "ptb_{$key}_rating", $value['total'] );
            }
        }
    }

    /**
     * Vote ajax handler
     *
     * @since 1.0.0
     */
    public function voted() {
        if (isset($_POST['id']) && isset($_POST['key']) && isset($_POST['value'])) {
            $post_id = (int)$_POST['id'];
            $post = get_post($post_id);
            if (empty($post_id) && $post_id->post_status !== 'publish') {
                wp_die();
            }
            $key = sanitize_key($_POST['key']);
            $plugin_options = new PTB_Options('ptb', $this->get_plugin_version());
            $options = $plugin_options->get_cpt_cmb_options($post->post_type);
            if (!isset($options[$key]) || $options[$key]['type'] !== 'rating') {
                wp_die();
            }
            $meta = $options[$key];
            $value = (int)$_POST['value'];
            unset($options);
            $this->uid = get_current_user_id();
            if (!empty($meta['readonly']) || ($value <= 0 || $value > $meta['stars_count'])) {
                wp_die();
            }
            $wp_meta_key = sprintf('%s_%s', $this->get_plugin_name(), $key);
            $vote = get_post_meta($post_id, $wp_meta_key, true);
            if (!$vote || !is_array($vote)) {
                $vote = array('total' => 0, 'count' => 0, 'users' => array(), 'ip' => array());
            }
            if ($this->uid) {
                if (in_array($this->uid, $vote['users'])) {
                    wp_die();
                }
                $vote['users'][] = $this->uid;
            }
            if (!$this->ip || in_array($this->ip, $vote['ip'])) {
                wp_die();
            }

            $vote['ip'][] = $this->ip;
            $vote['count']++;
            $vote['total']+=$value;
            update_post_meta($post_id, $wp_meta_key, $vote);

            $total = floatval($vote['total'] / $vote['count']);
            /* save an additional custom field to store the overall rating value, this is used for sorting posts */
			update_post_meta( $post_id, $wp_meta_key . '_rating', $total );
            die(json_encode(array('success' => 1, 'total' => $total,'count'=>$vote['count'])));
        } else {
            wp_die();
        }
    }

    /**
     * Get User Ip address
     *
	 * $_SERVER['REMOTE_ADDR'] cannot be used in all cases, such as when the user
	 * is making their request through a proxy, or when the web server is behind
	 * a proxy. In those cases, $_SERVER['REMOTE_ADDR'] is set to the proxy address rather
	 * than the user's actual address.
	 *
     * @since 1.0.0
     */
    private static function getClientIP() {
		$client_ip = false;

		// In order of preference, with the best ones for this purpose first.
		$address_headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $address_headers as $header ) {
			if ( array_key_exists( $header, $_SERVER ) ) {
				/*
				 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
				 * addresses. The first one is the original client. It can't be
				 * trusted for authenticity, but we don't need to for this purpose.
				 */
				$address_chain = explode( ',', $_SERVER[ $header ] );
				$client_ip     = trim( $address_chain[0] );

				break;
			}
		}

		if ( ! $client_ip ) {
			return false;
		}

		$anon_ip = wp_privacy_anonymize_ip( $client_ip, true );

		if ( '0.0.0.0' === $anon_ip || '::' === $anon_ip ) {
			return false;
		}

		return $anon_ip;
    }

    public function is_editable(array $cmb_options, $type, $post_type) {
        if ($type === 'frontend' || is_array($type)) {
            foreach ($cmb_options as $k => $v) {
                if ($v['type'] === 'rating' && !empty($v['readonly'])) {
                    unset($cmb_options[$k]);
                }
            }
        }
        return $cmb_options;
    }

    public function ptb_submission_form($post_type, array $args, array $module, $post, $lang, $languages) {
		$this->enqueue_assets();
        PTB_Utils::enqueue_script( self::$plugin_name . '-submission-rating', PTB_Submission::$url . 'public/js/field-rating.js', array( 'ptb-submission' ), $this->get_plugin_version(), true );
		PTB_Utils::enqueue_style( 'ptb-submission-rating', PTB_Submission::$url . 'public/css/field-rating.css', [ 'ptb-submission' ] );

        $icon = isset($module['icon']) && $module['icon'] ? $module['icon'] : 'fa-star';
		$icon = PTB_Utils::get_icon( $icon );
        $data = isset($post->ID) ? get_post_meta($post->ID, 'ptb_' . $args['key'], TRUE) : false;
        ?>
        <div class="ptb_back_active_module_input">
            <div
                data-id="<?php echo get_the_ID() ?>"
                data-key="<?php echo $args['key'] ?>"
                data-vcolor="<?php echo !empty($module['vcolor']) ? $module['vcolor'] : false ?>" 
                data-hcolor="<?php echo !empty($module['hcolor']) ? $module['hcolor'] : false ?>" 
                class="ptb_extra_rating ptb_extra_not_vote ptb_extra_rating_<?php echo isset($module['size']) ? $module['size'] : 'small' ?><?php if ($data): ?> ptb_extra_readonly_rating<?php endif; ?>">
                    <?php for ($i = $args['stars_count']; $i > 0; --$i): ?>
                    <span class="<?php echo $data >= $i ? ' ptb_extra_voted' : '' ?>"><?php echo $icon ?></span>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="submission[<?php echo $args['key'] ?>]" value="<?php echo $data ?>" />
            <span <?php if ($data): ?>style="display:inline-block;"<?php endif; ?>class="ptb-submission-rate-cancel hide ptb-submission-upload-btn"><?php _e('Cancel', 'ptb') ?></span>
            <?php if (isset($module['show_description'])): ?>
                <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function ptb_submission_validate(array $post_data, array $args, array $module, $post_type, $post_id, $lang, array $languages) {
        $value = false;
        if (isset($post_data[$module['key']])) {
            $value = (int)$post_data[$module['key']];
            if ($value <= 0 || $value > $args['stars_count']) {
                $value = false;
            } else {
                $post_data[$module['key']] = (int)$post_data[$module['key']];
            }
        }
        if (!$value && isset($module['required'])) {
            return sprintf( __( '%s is required', 'ptb' ), PTB_Utils::get_label( $args['name'] ) );
        }
        return $post_data;
    }

    public function ptb_submission_save( array $m, $key, array $post_data, $post_id, $lng ) {
		$ip = self::getClientIP();
		if ( $ip ) {
			$m['value'] = array(
				'total' => $m['value'],
				'count' => 1,
				'users' => array( get_current_user_id() ),
				'ip' => array( $ip )
			);
		}

        return $m;
    }

    public function search_rating_template($post_type,$id,$args,$module,$value,$label,$lang,$languages){
        if(!isset($args['name'])){
            return '';
        }
        $name =  PTB_Utils::get_label($args['name']);
        $name = $name ? sanitize_title($name) : $args['key'];
        $data = array();
        for($i=1;$i<=5;++$i){
            $data[$i] = $i;
        }

		if ( $label ) : ?>
			<div class="ptb_search_label">
				<label for="<?php echo $id ?>"><?php echo esc_attr( $label ); ?></label>
			</div>
		<?php endif;

        PTB_Search_Public::show_as('select', $post_type, $id, $name, $value, $args['key'], $label,$data);
    }
    
    public function search_rating($post_id,$post_type,$value,$args,$meta_key,$post_taxonomies){
        if($value>0 && $value<=$args['stars_count']){
            $value = round($value);
            $meta_key = 'ptb_'.$meta_key;
            $query_args = array(
				'fields' => 'ids',
				'post_type' => $post_type,
				'orderby' => 'ID',
				'order' => 'ASC',
				'nopaging' => 1,
				'include'=> !empty($post_id) ? implode(',', array_keys($post_id)) : '',
				'meta_query' => array(
						array(
							'key' =>$meta_key
						)
				)
			);
            $readonly = isset($args['readonly']) && $args['readonly'];
            if($readonly){
                $query_args['meta_query'][0]['value'] = $value;
            }
            else{
                $query_args['meta_query'][0]['compare'] = 'EXISTS';
            }
            $posts_array = get_posts($query_args);
            $post_id = array(); 
            if(!empty($posts_array)){
                if($readonly){
                    foreach ($posts_array as $p) {
                        $post_id[$p] = 1;
                    }
                }
                else{
                    foreach ($posts_array as $p) {
                        $rating = get_post_meta($p,$meta_key,true);
                        if($rating){
                            $v = maybe_unserialize($rating);
                            if(isset($v['count'])){
                                if( $v['count']>0){
                                    $v = round($v['total']/$v['count']);
                                }
                                else{
                                    continue;
                                }
                            }
                            if($v==$value){
                                $post_id[$p] = 1;
                            }
                        }
                    }
                }
            }
        }
        return $post_id;
    }
}
