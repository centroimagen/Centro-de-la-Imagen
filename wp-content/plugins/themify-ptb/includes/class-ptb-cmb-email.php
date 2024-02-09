<?php
/**
 * Custom meta box class of type Email
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Email
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Email extends PTB_CMB_Base {

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
            'name' => __('Email', 'ptb')
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
            <label for="<?php echo $id; ?>_default_value" class="ptb_cmb_input_label">
                <?php _e("Default Value", 'ptb'); ?>
            </label>
            <div class="ptb_cmb_input">
                <input type="email" id="<?php echo $id; ?>_default_value"/>
            </div>
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
        $sizes = array(16,32,64,128,256,512);
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[seperator]"><?php _e('Show Gravatar', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="checkbox" value="1" id="ptb_<?php echo $id ?>[gravatar]" name="[<?php echo $id ?>][gravatar]" <?php echo isset($data['gravatar']) ?'checked="checked"': '' ?>/>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[gravatar_size]"><?php _e('Gravatar Size', 'ptb') ?></label>
            </div>
            <div class="ptb_custom_select">
                <select name="[<?php echo $id ?>][gravatar_size]">
                    <?php foreach ($sizes as $s): ?>
                        <option <?php if (isset($data['gravatar_size']) && $data['gravatar_size'] == $s): ?>selected="selected"<?php endif; ?>value="<?php echo $s ?>"><?php echo $s,'X',$s ?></option>
                    <?php endforeach; ?>
                </select>
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
        $value = PTB_Utils::get_meta_value( $object, $meta_key, $args['defaultValue'] );
        ?>
        <input type="email" id="<?php echo $meta_key; ?>" name="<?php echo $meta_key; ?>" value="<?php echo sanitize_email($value); ?>"/>
        <?php
    }

	public static function admin_column_display( $value, $field_def ) {
		return $value;
	}
}
