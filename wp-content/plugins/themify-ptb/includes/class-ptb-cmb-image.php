<?php
/**
 * Custom meta box class of type Image
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Image
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Image extends PTB_CMB_Base {

	public function __construct($type, $plugin_name, $version) {
		parent::__construct($type, $plugin_name, $version);
		if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
			add_filter('ptb_meta_image_exist', array($this,'image_exist'),10,3);
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
            'name' => __('Image', 'ptb')
        );
        return $cmb_types;
    }

    /**
     * Renders the meta boxes on post edit dashboard
     *
     * @since 1.0.0
     *
     * @param WP_Post $post
     * @param string $meta_key the same as meta box internal id
     * @param array $args
     */
    public function render_post_type_meta( $object, $meta_key, $args ) {

        $value = PTB_Utils::get_meta_value( $object, $meta_key );
		if ( is_numeric( $value ) ) {
			// assume the data in the custom field is an attachment ID
			$value = wp_get_attachment_url( $value );
		}
		if ( is_string( $value ) ) {
			// convert string value to array, as expected by the field
			$value = array( 1 => $value );
		}
		if ( empty( $value ) ) {
			$value = array( 1 => '' );
		}
        $name = sprintf('%s[]', $meta_key);
        ?>
        <div class="ptb_post_cmb_image_button_wrapper">
            <div class="ptb_post_cmb_image_wrapper">
                <a href="#" id="image_<?php echo $meta_key; ?>" class="ptb_post_cmb_image" <?php echo isset($value[1]) ? sprintf('style="background-image:url(%s)"', $value[1]) : ''; ?>>
                    <?php echo PTB_Utils::get_icon( 'ti-plus' ); ?>
                </a>
            </div>
            <input type="hidden" name="<?php echo $name; ?>" value="<?php echo isset($value[0]) ? esc_attr($value[0]) : ''; ?>"/>
            <input type="text" class="ptb_cmb_image_url" placeholder="<?php _e('Image Url','ptb')?>" name="<?php echo $name; ?>" value="<?php echo isset($value[1]) ? esc_attr($value[1]) : ''; ?>" />
            <input type="text" placeholder="<?php _e('Image Link','ptb')?>" name="<?php echo $name; ?>" class="ptb_extra_row_margin" value="<?php echo !empty($value[2]) ? esc_url($value[2]) : ''; ?>"/>
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
        ?>

        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Image Dimension', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input id="ptb_<?php echo $id ?>_width" type="text" class="ptb_xsmall" name="[<?php echo $id ?>][width]"
                       <?php if (isset($data['width'])): ?>value="<?php echo $data['width'] ?>"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>_width"><?php _e('Width', 'ptb') ?></label>
                <input id="ptb_<?php echo $id ?>_height" type="text" class="ptb_xsmall" name="[<?php echo $id ?>][height]"
                       <?php if (isset($data['height'])): ?>value="<?php echo $data['height'] ?>"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>_height"><?php _e('Height (px)', 'ptb') ?></label>
            </div>
        </div>
        
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_permalink"><?php _e('Use Permalink', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_change_disable" data-disabled="1" data-action="show">
                <input value="1" <?php if (isset($data['permalink'])): ?>checked="checked"<?php endif; ?>  id="ptb_<?php echo $id ?>_permalink" type="checkbox" name="[<?php echo $id ?>][permalink]" />
                <input class="ptb_maybe_disabled" style="width: 94.8%;" placeholder="<?php _e('Or Custom Url', 'ptb') ?>" <?php if (isset($data['custom_url'])): ?>value="<?php echo esc_url($data['custom_url']) ?>"<?php endif; ?> type="text" id="ptb_<?php echo $id ?>_custom_url" name="[<?php echo $id ?>][custom_url]" />
            </div>
        </div>
        <?php
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
	public function image_exist ( array $meta_query, $origk, array $cmb_option) {
		$meta_query['compare'] = 'NOT LIKE';
		$meta_query['value'] = 'i:1;s:0:"";i:2;s:0:""';
		return $meta_query;
	}

	public static function admin_column_display( $value, $field_def ) {
		if ( ! empty( $value[0] ) ) {
			if ( is_numeric( $value[0] ) ) {
				$url = wp_get_attachment_url( $value[0] );
			} else {
				$url =  $value[0];
			}
		} else if ( ! empty( $value[1] ) ) {
			$url = $value[1];
		}

		if ( ! empty( $url ) ) {
			return '<img src="' . esc_url( $url ) . '" alt="" style="max-width: 100%; height: auto;" />';
		}
	}
}
