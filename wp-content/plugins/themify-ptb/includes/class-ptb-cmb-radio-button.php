<?php
/**
 * Custom meta box class of type Radio Button
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Radio Button
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Radio_Button extends PTB_CMB_Base {

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
            'name' => __('Radio Button', 'ptb')
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
			<label for="<?php echo $id; ?>_columns" class="ptb_cmb_input_label"><?php _e( 'Columns', 'ptb' ); ?></label>
			<fieldset class="ptb_cmb_input">
				<select name="<?php echo $id; ?>_columns" id="<?php echo $id; ?>_columns">
					<option value="inline"><?php _e( 'Inline', 'ptb' ); ?></option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
				</select>
			</fieldset>
		</div>

        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_options" class="ptb_cmb_input_label">
                <?php _e("Options", 'ptb'); ?>
            </label>
            <fieldset class="ptb_cmb_input">
                <ul id="<?php echo $id; ?>_options_wrapper" class="ptb_cmb_options_wrapper">

                    <li class="<?php echo $id; ?>_option_wrapper ptb_cmb_option">
                        <span class="ptb_cmb_option_sort"><?php echo PTB_Utils::get_icon( 'ti-split-v' ); ?></span>
                        <?php if (count($languages) > 1): ?>
                            <ul class="ptb_language_tabs">
                                <?php foreach ($languages as $code => $lng): ?>
                                    <li <?php if (isset($lng['selected'])): ?>class="ptb_active_tab_lng"<?php endif; ?>>
                                        <a class="ptb_lng_<?php echo $code ?>" title="<?php echo $lng['name'] ?>" href="#"></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <ul class="ptb_language_fields">
                            <?php foreach ($languages as $code => $lng): ?>
                                <li <?php if (isset($lng['selected'])): ?>class="ptb_active_lng"<?php endif; ?>>
                                    <input name="<?php echo $id; ?>_options_<?php echo $code ?>[]" type="text"/>&nbsp;&nbsp;
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <input type="radio" name="<?php echo $id; ?>_default_selected" class="ptb_cmb_option_default_selected"/>
                        <span class="<?php echo $id; ?>_default_selected_label ptb_cmb_option_default_selected_label"><?php _e('Default Selected', 'ptb') ?></span>
                        <span class="<?php echo $id; ?>_remove remove"><?php echo PTB_Utils::get_icon( 'ti-close' ); ?></span>
                    </li>
                </ul>
                <div id="<?php echo $id; ?>_add_new" class="ptb_cmb_option_add">
                    <?php echo PTB_Utils::get_icon( 'ti-plus' ); ?>
                    <?php _e("Add new", 'ptb'); ?>
                </div>
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
    public function render_post_type_meta($object, $meta_key, $args) {

        $value = PTB_Utils::get_meta_value( $object, $meta_key );
		$columns = ! empty( $args['columns'] ) ? $args['columns'] : 'inline';
        ?>
        <fieldset class="ptb_col_<?php echo $columns; ?>">
            <?php
            foreach ($args['options'] as $option) {
                $label = PTB_Utils::get_label($option);
                ?>
                <label>
                    <input type="radio" id="<?php echo $option['id']; ?>" name="<?php echo $meta_key; ?>"
                           value="<?php echo $option['id']; ?>" <?php empty($value) ? checked($option['selected'], true) : checked($option['id'], $value); ?>>
                    <span><?php echo strip_tags(html_entity_decode($label)); ?></span>
                </label>
                <?php
            }
            ?>
        </fieldset>
        <?php
    }

	public static function admin_column_display( $value, $field_def ) {
		if ( ! empty( $value ) ) {
			foreach ( $field_def['options'] as $option ) {
				if ( $option['id'] === $value ) {
					return PTB_Utils::get_label( $option, $option['id'] );
				}
			}
		}
	}
}