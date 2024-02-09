<?php
/**
 * Custom meta box class of type Number
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type number
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Number extends PTB_CMB_Base {

	public function __construct($type, $plugin_name, $version) {
		parent::__construct($type, $plugin_name, $version);
		if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
			add_filter('ptb_meta_number_exist', array($this,'number_exist'),10,3);
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
            'name' => __('Number', 'ptb')
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
            <label for="<?php echo $id; ?>_range" class="ptb_cmb_input_label">
                <?php _e("Show as range", 'ptb'); ?>
            </label>
            <div class="ptb_cmb_input">
                <input type="checkbox" id="<?php echo $id; ?>_range" name="<?php echo $id; ?>_showrange" value="1" />
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
        $currency = PTB_Utils::get_currencies();
        $currency_pos = PTB_Utils::get_currency_position();
        ?>  
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[currency]"><?php _e('Currency', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input ptb_change_disable" data-disabled="0" data-action="1">
                    <div class="ptb_custom_select">
                        <select id="ptb_<?php echo $id ?>[currency]" name="[<?php echo $id ?>][currency]">
                            <option value="0" <?php if(empty($data['currency'])):?>selected="selected"<?php endif;?>>---</option>
                            <?php foreach ($currency as $c => $name): ?>
                            <option <?php if (isset($data['currency']) && $data['currency'] === $c): ?>selected="selected"<?php endif; ?> value="<?php echo $c ?>"><?php echo $name. ' (' . PTB_Utils::get_currency_symbol($c) . ')' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ptb_back_active_module_row ptb_maybe_disabled">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[currency_pos]"><?php _e('Currency Position', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <div class="ptb_custom_select">
                        <select id="ptb_<?php echo $id ?>[currency_pos]" name="[<?php echo $id ?>][currency_pos]">
                            <?php foreach ($currency_pos as $c => $name): ?>
                                <option <?php if (isset($data['currency_pos']) && $data['currency_pos'] === $c): ?>selected="selected"<?php endif; ?> value="<?php echo $c ?>"><?php echo $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ptb_back_active_module_row ptb_thousand_separator">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[thousand]"><?php _e('Thousand Separator', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="text" id="ptb_<?php echo $id ?>[thousand]"
                           name="[<?php echo $id ?>][thousand]" value="<?php echo isset($data['thousand']) ? $data['thousand'] : '' ?>"
                           />
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[decimal]"><?php _e('Decimal Separator', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="text" id="ptb_<?php echo $id ?>[decimal]"
                           name="[<?php echo $id ?>][decimal]" value="<?php echo isset($data['decimal']) ? $data['decimal'] : '.' ?>"
                           />
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[ndecimals]"><?php _e('Number of Decimals', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="text" id="ptb_<?php echo $id ?>[ndecimals]"
                           name="[<?php echo $id ?>][ndecimals]" value="<?php echo isset($data['ndecimals']) ? (int)$data['ndecimals'] : 2 ?>"
                           />
                </div>
            </div>
            <?php if(!empty($args['range'])):?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[seperator]"><?php _e('Range separator', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input type="text" id="ptb_<?php echo $id ?>[rangeseperator]"
                               name="[<?php echo $id ?>][seperator]" value="<?php echo isset($data['seperator']) ? $data['seperator'] : ' - ' ?>"
                               />
                    </div>
                </div>
            <?php endif;?>
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
        <?php if(isset($args['range']) && $args['range']):?>
            <input class="ptb_number" placeholder="<?php _e('From', 'ptb') ?>" type="text" id="<?php echo $meta_key; ?>_from" name="<?php echo $meta_key; ?>[from]"  value="<?php echo !is_array($value)?$value:(isset($value['from'])?$value['from']:''); ?>"/>
            <span class="ptb-arrow-right"><?php echo PTB_Utils::get_icon( 'ti-arrow-right' ); ?></span>
            <input class="ptb_number" placeholder="<?php _e('To', 'ptb') ?>" type="text" id="<?php echo $meta_key; ?>_to" name="<?php echo $meta_key; ?>[to]"  value="<?php echo is_array($value) && isset($value['to'])?$value['to']:''; ?>"/>
        <?php else:?>
            <input class="ptb_number" type="text" id="<?php echo $meta_key; ?>_from" name="<?php echo $meta_key; ?>" value="<?php echo !is_array($value)?$value:(isset($value['from'])?$value['from']:''); ?>"/>
        <?php endif;?>
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
	public function number_exist ( array $meta_query, $origk, array $cmb_option) {

		// checking for serialized range date is enough.
		$meta_query['compare'] = 'NOT LIKE';
		$meta_query['value'] = '"from";s:0:""';

		return $meta_query;
	}

	public static function admin_column_display( $value, $field_def ) {
		if ( is_array( $value ) ) {
			return $value['from'] . ' - ' . $value['to'];
		} else {
			return $value;
		}
	}
}
