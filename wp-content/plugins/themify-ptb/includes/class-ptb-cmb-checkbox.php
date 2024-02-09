<?php
/**
 * Custom meta box class of type Checkbox
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Checkbox
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Checkbox extends PTB_CMB_Base {
	
	public function __construct($type, $plugin_name, $version) {
        parent::__construct($type, $plugin_name, $version);
        if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
            add_filter('ptb_meta_checkbox_exist', array($this,'checkbox_exist'),10,3);
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
            'name' => __('Checkbox', 'ptb')
        );

        return $cmb_types;
    }

    /**
     * @param string $id the id template
     * @param array $languages
     */
    public function action_template_type($id, array $languages) {
        $lng_count = count($languages) > 1;
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
                        <?php if ($lng_count): ?>
                            <ul class="ptb_language_tabs">
                                <?php foreach ($languages as $code => $lng): ?>
                                    <li <?php if (isset($lng['selected'])): ?>class="ptb_active_tab_lng"<?php endif; ?>>
                                        <a class="ptb_lng_<?php echo $code ?>" title="<?php echo $lng['name'] ?>" href="#"></a>
                                    </li>
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
                        <input type="checkbox" name="<?php echo $id; ?>_default_checked" class="ptb_cmb_option_default_checked">
                        <span class="<?php echo $id; ?>_default_checked_label ptb_cmb_option_default_checked_label"><?php _e('Pre-check this', 'ptb') ?></span>
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
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[display_one_line]"><?php _e('Display multiple checkboxes as', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_change_disable" data-disabled="list,paragraph,bullet_list,numbered_list" data-action="1">
                <input type="radio" id="ptb_<?php echo $id ?>[display_list]"
                       name="[<?php echo $id ?>][display]" value="list"
                       <?php if (isset($data['display']) && $data['display'] == 'list'): ?>checked="checked"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>[display_list]"><?php _e('List', 'ptb') ?></label>
                
                <input type="radio" id="ptb_<?php echo $id ?>[display_paragraph]"
                       name="[<?php echo $id ?>][display]" value="paragraph"
                       <?php if (isset($data['display']) && $data['display'] == 'paragraph'): ?>checked="checked"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>[display_paragraph]"><?php _e('Paragraph', 'ptb') ?></label>
                
                <input type="radio" id="ptb_<?php echo $id ?>[display_one_line]"
                       name="[<?php echo $id ?>][display]" value="one_line"
                       <?php if (!$data || (isset($data['display']) && $data['display'] == 'one_line')): ?>checked="checked"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>[display_one_line]"><?php _e('One line', 'ptb') ?></label>
                
                <input type="radio" id="ptb_<?php echo $id ?>[display_bullet_list]"
                       name="[<?php echo $id ?>][display]" value="bullet_list"
                       <?php if (isset($data['display']) && $data['display'] == 'bullet_list'): ?>checked="checked"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>[display_bullet_list]"><?php _e('Bullet list', 'ptb') ?></label>
                
                <input type="radio" id="ptb_<?php echo $id ?>[display_numbered_list]"
                       name="[<?php echo $id ?>][display]" value="numbered_list"
                       <?php if (isset($data['display']) && $data['display'] == 'numbered_list'): ?>checked="checked"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>[display_numbered_list]"><?php _e('Numbered list', 'ptb') ?></label>
            </div>
        </div>
        <div class="ptb_back_active_module_row ptb_maybe_disabled">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[seperator]"><?php _e('Separator', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="text" id="ptb_<?php echo $id ?>[seperator]"
                    name="[<?php echo $id ?>][seperator]" value="<?php echo  !empty($data['seperator'])? $data['seperator']:''?>" />
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
     * @param string $meta_key the same as meta box internal id
     * @param array $args
     */
    public function render_post_type_meta($object, $meta_key, $args) {

        $value = PTB_Utils::get_meta_value( $object, $meta_key );
        $name = sprintf('%s[]', $meta_key);
		$columns = ! empty( $args['columns'] ) ? $args['columns'] : 'inline';
        ?>
        <fieldset class="ptb_col_<?php echo $columns; ?>">
            <?php
            foreach ($args['options'] as $option) {
                $id = esc_attr($option['id']);
                $label = PTB_Utils::get_label($option);
                ?>
                <label for="<?php echo $id; ?>">
                    <input type="checkbox" id="<?php echo $id; ?>"
                           name="<?php echo $name; ?>"
                           value="<?php echo $id; ?>" 
                           <?php if (!empty($value)): ?>
                               <?php checked(in_array($id, $value), true); ?>
                           <?php endif; ?>
					/>
                    <span><?php echo strip_tags(html_entity_decode($label)); ?></span>
                </label>
            <?php } ?>
            <input type="hidden" value="" name="<?php echo $name; ?>" />
        </fieldset>
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
	public function checkbox_exist ( array $meta_query, $origk, array $cmb_option) {

		$meta_query['value'] = 'a:1:{i:0;s:0:"";}';

		return $meta_query;
	}

	public static function admin_column_display( $value, $field_def ) {
		if ( ! is_array( $value ) ) {
			return '';
		}

		$output = [];
		foreach ( $field_def['options'] as $option ) {
			if ( in_array( $option['id'], $value, true ) ) {
				$output[] = PTB_Utils::get_label( $option, $option['id'] );
			}
		}

		return implode( '<br>', $output );
	}
}
