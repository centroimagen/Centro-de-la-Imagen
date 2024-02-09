<?php
/**
 * Custom meta box class of type Telephone
 *
 * @link       https://themify.me
 * @since      1.2.8
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Telephone
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Telephone extends PTB_CMB_Base {

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
            'name' => __('Telephone', 'ptb')
        );

        return $cmb_types;
    }
	public function __construct($type, $plugin_name, $version) {
        parent::__construct($type, $plugin_name, $version);
    }

    /**
     * Renders the meta boxes for themplates
     *
     * @since 1.0.0
     *
     * @param string $id the metabox id
     * @param string $type the type of the page(Archive or Single)
     * @param array $args Array of custom meta types of plugin
     * @param array $data saved data
     * @param array $languages languages array
     */
    public function action_them_themplate($id, $type, $args, $data = array(), array $languages = array()) {

        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[placement]"><?php _e('Text In place', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_back_text">
                <input type="text" id="ptb_<?php echo $id ?>[placement]"
                       name="[<?php echo $id ?>][placement]" value="<?php echo (isset($data['placement']) ? esc_attr($data['placement']) : '') ?>"
                       />
				<?php _e('(e.g Call Us) leave it empty to display the phone number', 'ptb'); ?>
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
        if (!$value) {
            $value = '';
        }
        ?>
        <input type="tel" id="<?php echo $meta_key; ?>" size="11" name="<?php echo $meta_key; ?>" value="<?php echo esc_attr($value); ?>"/>
        <?php
    }

    public function ptb_submission_form($post_type, array $args, array $module, $post, $lang, $languages) {
        $data = isset($post->ID) ? get_post_meta($post->ID, 'ptb_' . $args['key'], TRUE) : false;
        ?>
        <div class="ptb_back_active_module_input">
            <input id="ptb_submission_<?php echo $args['key'] ?>" type="text" size="11" class="ptb_towidth" name="submission[<?php echo $args['key'] ?>]">
			<?php if ( isset( $module['show_description'] ) ) : ?>
				<div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label( $args['description'] ); ?></div>
			<?php endif; ?>
        </div>
        <?php
    }

    public function ptb_submission_validate(array $post_data, array $args, array $module, $post_type, $post_id, $lang, array $languages) {
        $value = preg_replace('/[^0-9\(\)\s-]/', '', $post_data[$module['key']]);
        if (!$value && isset($module['required'])) {
            return sprintf( __( '%s is required', 'ptb' ), PTB_Utils::get_label( $args['name'] ) );
        }

        $post_data[$module['key']] = $value;
        return $post_data;
    }

    public function ptb_submission_save(array $m, $key, array $post_data, $post_id, $lng) {
        return $m;
    }


	public static function admin_column_display( $value ) {
		return $value;
	}

}