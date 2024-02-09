<?php

class PTB_Form_PTT_Search extends PTB_Form_PTT_Them {

    private $template_id = false;

	/**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param string themplate_id
     *
     */
    public function __construct($plugin_name, $version, $themplate_id = false) {
        $this->template_id = $themplate_id;
        parent::__construct($plugin_name, $version, $themplate_id);
        add_action('ptb_search_template', array($this, 'search_template'), 10, 6);
    }

	/**
	 * Search layout parametrs
	 *
	 * @since 1.0.0
	 */
	public function add_fields( $data = array(), $post_type = '' ) {
		$fieldname = $this->get_field_name( 'result_type' );
		$pages = get_posts( array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby' => 'post_title',
			'order' => 'ASC'
		) );
		$result_types = array(
			'archive' => __( 'Display results in the post type archive page', 'ptb-search' ),
			'same_page' => __( 'Show results on the same page', 'ptb-search' ),
			'diff_page' => __( 'Show results on a different page', 'ptb-search')
		);
		$is_ptb_type = is_ptb_post_type( $post_type );
		if ( ! $is_ptb_type ) {
			unset( $result_types['archive'] );
		}
		?>
		<div class="ptb_lightbox_row">
			<div class="ptb_lightbox_label"><?php _e( 'Result Page Template', 'ptb-search' ); ?></div>
			<div class="ptb_lightbox_input">
				<ul class="ptb_result_switcher">
					<?php foreach( $result_types as $id => $option ) : ?>
					<li>
						<?php
							$current = ! empty( $data[$fieldname] ) ? $data[$fieldname] : ( $is_ptb_type ? 'archive' : 'same_page' );
							printf( '<input id="ptb_%1$s" type="radio" value="%1$s" name="%2$s" %3$s>'
								, $id, $fieldname, checked( $id, $current, false ) );
							printf( '<label for="ptb_%s">%s</label>', $id, $option );
						?>
						<?php if ( $id === 'same_page' ) : ?>
							<p class ="ptb_same_page_select ptb_lightbox_field_indent ptb-hide">
								<?php
									$disable_ajax = $this->get_field_name( 'disable_ajax' );
									printf( '<input id="ptb_%s" type="checkbox" value="1" name="%s" %s>'
										, $disable_ajax, $disable_ajax, checked( '1', ! empty( $data[$disable_ajax] ) ? $data[$disable_ajax] : '', false ) );
								?>
								<label for="ptb_<?php echo $disable_ajax; ?>"><?php _e( 'Disable Ajax to load the search results', 'ptb-search' ); ?></label><br><br>
								<small>Note: [ptb-search-results] and [ptb] shortcode need to be present on the page for Ajax search (<a href="https://themify.me/docs/ptb-search#search-result-location" target="_blank">learn more</a>)</small>
							</p>
						<?php endif; ?>
					</li>
					<?php endforeach; ?>
				</ul>
				<div class="ptb_result_page_select ptb_lightbox_field_indent ptb-hide">
					<div class="ptb_custom_select">
						<?php
							$fieldname = $this->get_field_name( 'page' );
							$current = ! empty( $data[$fieldname] ) ? $data[$fieldname] : '';
						?>
						<select name="<?php echo $fieldname; ?>" id="ptb_result_page">
							<?php if ( ! empty( $pages ) ) :
								foreach ( $pages as $p ) {
									printf( '<option value="%s" %s>%s</option>'
										, $p->ID, selected( $p->ID, $current, false ), $p->post_title );
								} 
							endif; ?>
						</select>
					</div>
					<label for="ptb_result_page"><?php _e( 'Select a page to use as the search result page.', 'ptb-search') ?></label>
				</div>
			</div>
		</div>
		<div class="ptb_lightbox_row ptb-search_orderby">
			<?php
			$ptt = $this->get_ptt();
			$sorting_fileds = PTB_Form_PTT_Archive::get_sort_fields($this->options->get_cpt_cmb_options($ptt['post_type']));
			?>
			<div class="ptb_lightbox_label"><?php _e('Order By', 'ptb-search'); ?></div>
			<div class="ptb_lightbox_input">
				<div class="ptb_custom_select">
					<?php $fieldname = $this->get_field_name('orderby'); ?>
					<select name="<?php echo $fieldname ?>">
						<?php foreach ($sorting_fileds as $key => $sort): ?>
							<option <?php if (isset($data[$fieldname]) && $data[$fieldname] === $key): ?>selected="selected" <?php endif; ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_attr($sort) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="ptb_lightbox_row ptb-search_order">
			<div class="ptb_lightbox_label"><?php _e('Order', 'ptb-search'); ?></div>
			<div class="ptb_lightbox_input">
				<div class="ptb_custom_select">
					<?php $fieldname = $this->get_field_name('order'); ?>
					<select name="<?php echo $fieldname ?>">
						<option <?php if (isset($data[$fieldname]) && $data[$fieldname] === 'desc'): ?>selected="selected"<?php endif; ?> value="desc"><?php _e('Descending', 'ptb-search'); ?></option>
						<option <?php if (isset($data[$fieldname]) && $data[$fieldname] === 'asc'): ?>selected="selected"<?php endif; ?> value="asc"><?php _e('Ascending', 'ptb-search'); ?></option>
					</select>
				</div>
			</div>
		</div>
		<div class="ptb_lightbox_row ptb_no_result">
			<div class="ptb_lightbox_label"><?php _e( 'No Result Message', 'ptb-search' ); ?></div>
			<div class="ptb_lightbox_input">
				<?php 
				$fieldname = $this->get_field_name( 'no_result' );
				PTB_CMB_Base::module_language_tabs($fieldname,isset( $data[$fieldname])? $data[$fieldname]:array(), PTB_Utils::get_all_languages(), 'no_result', 'text', false, TRUE); ?>
				<small><?php _e('This message will display when there is no result','ptb-search')?></small>
			</div>
		</div>
		<div class="ptb_lightbox_row ptb_scrollto">
			<div class="ptb_lightbox_label"><?php _e( 'Scroll To Result', 'ptb-search' ); ?></div>
            <div class="ptb_lightbox_input">
                <?php $fieldname = $this->get_field_name('scrollto'); ?>
                <input type="radio" name="<?php echo $fieldname; ?>" value="1" id="ptb_ptt_scrollto_yes" <?php if ( isset( $data[$fieldname] ) && $data[ $fieldname ] === '1' ) : ?>checked="checked"<?php endif; ?>>
                <label for="ptb_ptt_scrollto_yes"><?php _e( 'Yes', 'ptb-search' ); ?></label>
                <input type="radio" name="<?php echo $fieldname; ?>" value="0" id="ptb_ptt_scrollto_no" <?php if ( ! isset( $data[$fieldname] ) || $data[$fieldname] === '0' ): ?>checked="checked"<?php endif; ?>>
                <label for="ptb_ptt_scrollto_no"><?php _e( 'No', 'ptb-search' ); ?></label>
            </div>
		</div>
		<?php
	}

    public function search_template($type, $id, array $args, array $module, array $post_support, array $languages) {
        $empty = empty($module);
        $show_as = array(
            'radio' => __('Radio', 'ptb-search'),
            'checkbox' => __('Checkbox', 'ptb-search'),
            'select' => __('Select', 'ptb-search')
        );
        ?>

        <?php if (!in_array($type, array('custom_image','plain_text' ,'custom_text','button'),true)): ?>
            <div class="ptb_back_active_module_row">
                <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'label', __('Label', 'ptb')); ?>
            </div>
        <?php endif; ?>

        <?php
        switch ($type) {
            case 'number':
                $show_as = array(
                    'text' => __('Text', 'ptb-search'),
                    'slider' => 'Min/Max Slider'
                );
                ?>  
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Input Option', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <?php foreach ($show_as as $key => $input): ?>
                            <input type="radio" id="ptb_<?php echo $id ?>[<?php echo $key ?>]" name="[<?php echo $id ?>][show_as]" value="<?php echo $key ?>"
                                   <?php if ((isset($module['show_as']) && $module['show_as'] === $key) || ($empty && $key === 'text')): ?>checked="checked"<?php endif; ?> />
                            <label for="ptb_<?php echo $id ?>[<?php echo $key ?>]"><?php echo $input ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                break;
			case 'select':
				?>
				<div class="ptb_back_active_module_row">	
					<div class="ptb_back_active_module_label">
						<label for="ptb_<?php echo $id ?>[multiselect]"><?php _e('Allow Multi-Select', 'ptb-search') ?></label>
					</div>
					<div class="ptb_back_active_module_input">
						<label>
							<input id="ptb_<?php echo $id ?>[multiselect]" type="checkbox" value="1" name="[<?php echo $id ?>][multiselect]" <?php if(isset($module['multiselect'])): ?>checked="checked" <?php endif;?>/>
							<?php _e('Allow user to select multiple options to search', 'ptb-search'); ?>
						</label>
					</div>
				</div>
				<?php
				break;
            case 'text':
                break;
            case 'taxonomies':
            case 'category':
            case 'post_tag':
                $show_as['autocomplete'] = __('AutoComplete', 'ptb-search');
                $show_as['multiselect'] = __('Multiple Select', 'ptb-search');
                if($type==='post_tag' && empty($module['show_as'])){
                    $module['show_as'] = 'autocomplete';
                }
                $orderby = array(
					'name'=>__('Name','ptb-search'),
                    'id'=>__('ID','ptb-search'),
                    'count '=>__('Count ','ptb-search'),
                    'slug'=>__('Slug','ptb-search'),
                    'description'=>__('Description','ptb-search'),
                    'term_group'=>__('Term Group','ptb-search')
                    );
                $order = array('ASC'=>__('ASC','ptb-search'),'DESC'=>__('DESC','ptb-search'));
                ?>
                <?php if ($type == 'taxonomies'): ?>
                    <div class="ptb_back_active_module_row">
                        <div class="ptb_back_active_module_label">
                            <label for="ptb_select_taxonomy"><?php _e('Select Taxonomy', 'ptb-search') ?></label>
                        </div>
                        <div class="ptb_back_active_module_input">
                            <select class="ptb-select" id="ptb_select_taxonomy" name="[<?php echo $id ?>][taxonomy]">
                                <?php foreach ($this->post_taxonomies as $tax => $tax_name): ?>
                                    <option <?php if (isset($module['taxonomy']) && $module['taxonomy'] === $tax): ?>selected="selected"<?php endif; ?> value="<?php echo $tax ?>">
                                        <?php echo $tax_name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="ptb_back_active_module_row ptb_search_show_as">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Input Option', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input ptb_change_disable" data-action="1" data-disabled="autocomplete">
                        <?php foreach ($show_as as $key => $input): ?>
                            <input type="radio" id="ptb_<?php echo $id ?>[<?php echo $key ?>]" name="[<?php echo $id ?>][show_as]" value="<?php echo $key ?>"
                                   <?php if ((isset($module['show_as']) && $module['show_as'] === $key) || ($empty && $key === 'select')): ?>checked="checked"<?php endif; ?> />
                            <label for="ptb_<?php echo $id ?>[<?php echo $key ?>]"><?php echo $input ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="ptb_back_active_module_row ptb_maybe_disabled">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Orderby', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <select class="ptb-select" name="[<?php echo $id ?>][orderby]">
                            <?php foreach ($orderby as $k => $v): ?>
                                <option <?php if (isset($module['orderby']) && $module['orderby'] === $k): ?>selected="selected"<?php endif; ?> value="<?php echo $k ?>">
                                    <?php echo $v ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="ptb-select" name="[<?php echo $id ?>][order]">
                            <?php foreach ($order as $k => $v): ?>
                                <option <?php if (isset($module['order']) && $module['order'] === $k): ?>selected="selected"<?php endif; ?> value="<?php echo $k ?>">
                                    <?php echo $v ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Show Count', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                       <input id="ptb_<?php echo $id ?>_html" type="checkbox" name="[<?php echo $id ?>][count]"
                               value="1" <?php if ($empty || !empty($module['count'])): ?>checked="checked"<?php endif; ?> />
                    </div>
                </div>
				<div class="ptb_back_active_module_row ptb_maybe_disabled">
					<div class="ptb_back_active_module_label">
						<label for="ptb_term_exclude"><?php _e('Exclude Terms', 'ptb-search') ?></label>
					</div>
					<div class="ptb_back_active_module_input">
						<input type="text" class="ptb_towidth" id="ptb_term_exclude" name="[<?php echo $id ?>][term_exclude]" value="<?php echo isset( $module['term_exclude'] ) ? esc_attr( $module['term_exclude'] ) : ''; ?>">
						<small><?php _e( 'Enter term IDs to exclude (eg. 2, 4, 12)', 'ptb-search' ); ?></small>
					</div>
				</div>
				<div class="ptb_back_active_module_row ptb_maybe_disabled">
					<div class="ptb_back_active_module_label">
						<label for="ptb_term_include"><?php _e('Include Terms', 'ptb-search') ?></label>
					</div>
					<div class="ptb_back_active_module_input">
						<input type="text" class="ptb_towidth" id="ptb_term_include" name="[<?php echo $id ?>][term_include]" value="<?php echo isset( $module['term_include'] ) ? esc_attr( $module['term_include'] ) : ''; ?>">
						<small class="description"><?php _e( 'Enter term IDs to include only (eg. 2, 4, 12)', 'ptb-search' ); ?></small>
					</div>
				</div>
				<div class="ptb_back_active_module_row ptb_search_tax_operator" style="display: none">
					<div class="ptb_back_active_module_label">
						<label for="ptb_term_include"><?php _e('Operator', 'ptb-search') ?></label>
					</div>
					<div class="ptb_back_active_module_input">
						<select class="ptb-select" name="[<?php echo $id ?>][operator]">
							<option <?php if ( isset( $module['operator'] ) && $module['operator'] === 'OR' ) : ?>selected="selected"<?php endif; ?> value="OR"><?php _e( 'OR', 'ptb-search' ); ?></option>
							<option <?php if ( isset( $module['operator'] ) && $module['operator'] === 'AND' ) : ?>selected="selected"<?php endif; ?> value="AND"><?php _e( 'AND', 'ptb-search' ); ?></option>
						</select>
						<div class="description">
							<p><?php _e( 'How to select posts when multiple terms are selected:'. 'ptb-search' ); ?></p>
							<dl>
								<dt><?php _e( 'OR', 'ptb-search' ); ?></dt><dd><?php _e( 'Displays posts that are assigned to either of the chosen terms', 'ptb-search' ); ?></dd>
								<dt><?php _e( 'AND', 'ptb-search' ); ?></dt><dd><?php _e( 'Will only display posts that are assigned to all the selected terms', 'ptb-search' ); ?></dd>
							</dl>
						</div>
					</div>
				</div>
                <?php
                break;
            case 'has':
                $fields = array(
                    'comments' => PTB_Search_Options::get_name('comments'),
                    'thumbnail' => PTB_Search_Options::get_name('thumbnail'),
                );
                ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_has_field"><?php _e('Select Field', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <select class="ptb-select" id="ptb_has_field" name="[<?php echo $id ?>][has_field]">
                            <?php foreach ($fields as $f => $name): ?>
                                <?php if(in_array($f,$post_support)):?>
                                    <option <?php if (isset($module['has_field']) && $module['has_field'] === $f): ?>selected="selected"<?php endif; ?> value="<?php echo $f ?>">
                                        <?php echo $name ?>
                                    </option>
                                <?php endif;?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Input Option', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <?php foreach ($show_as as $key => $input): ?>
                            <input type="radio" id="ptb_<?php echo $id ?>[<?php echo $key ?>]" name="[<?php echo $id ?>][show_as]" value="<?php echo $key ?>"
                                   <?php if ((isset($module['show_as']) && $module['show_as'] === $key) || ($empty && $key === 'checkbox')): ?>checked="checked"<?php endif; ?> />
                            <label for="ptb_<?php echo $id ?>[<?php echo $key ?>]"><?php echo $input ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                break;
            case 'button':
                $aligment = array(
                    'left' =>__('Left','ptb-search'),
                    'center' => __('Center','ptb-search'),
                    'right' => __('Right','ptb-search')
                );
                $colors = array(
                    'white' => __('White', 'ptb'),
                    'yellow' => __('Yellow', 'ptb'),
                    'orange' => __('Orange', 'ptb'),
                    'blue' => __('Blue', 'ptb'),
                    'green' => __('Green', 'ptb'),
                    'red' => __('Red', 'ptb'),
                    'black' => __('Black', 'ptb'),
                    'purple' => __('Purple', 'ptb'),
                    'gray' => __('Gray', 'ptb'),
                    'light-yellow' => __('Light-yellow', 'ptb'),
                    'light-green' => __('Light-green', 'ptb'),
                    'pink' => __('Pink', 'ptb'),
                    'lavender' => __('Lavender', 'ptb')
                );
                $reset = array(
                    '' =>__('Don\'t show','ptb-search'),
                    'before' => __('Before Submit Button','ptb-search'),
                    'after' => __('After Submit Button','ptb-search')
                );
                ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_has_field"><?php _e('Alignment', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">                          
                        <select class="ptb-select" name="[<?php echo $id ?>][aligmnet]">
                            <?php foreach ($aligment as $f => $name): ?>
                                <option <?php if (isset($module['aligmnet']) && $module['aligmnet'] === $f): ?>selected="selected"<?php endif; ?> value="<?php echo $f ?>">
                                    <?php echo $name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'text', __('Button Text', 'ptb-search')); ?>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_color"><?php _e('Button Color', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>_color" name="[<?php echo $id ?>][color]">
                                <?php foreach ($colors as $color => $name): ?>
                                    <option class="shortcode ptb_link_button <?php echo $color ?>" <?php if (isset($module['color']) && $module['color'] === $color): ?>selected="selected"<?php endif; ?> value="<?php echo $color ?>"><?php echo $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php _e('OR', 'ptb') ?>
                        <input class="ptb_color_picker" type="text" name="[<?php echo $id ?>][custom_color]" <?php if (!empty($module['custom_color'])): ?>data-value="<?php echo $module['custom_color'] ?>"<?php endif; ?> />
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_reset"><?php _e('Reset Button', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
						<?php foreach ( $reset as $value => $name ) : ?>
							<input type="radio" id="ptb_<?php echo $id ?>_reset_<?php echo $value; ?>" name="[<?php echo $id ?>][reset]" value="<?php echo $value ?>"
							   <?php if ( ( isset( $module['reset'] ) && $module['reset'] === $value ) || ( empty( $module['reset'] ) && $value === '' ) ) : ?>checked="checked"<?php endif; ?> />
							<label for="ptb_<?php echo $id ?>_reset_<?php echo $value; ?>"><?php echo $name ?></label>
						<?php endforeach; ?>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'reset_text', __('Reset Button Text', 'ptb-search')); ?>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_reset_color"><?php _e('Reset Button Color', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>_reset_color" name="[<?php echo $id ?>][reset_color]">
                                <?php foreach ($colors as $color => $name): ?>
                                    <option class="shortcode ptb_link_button <?php echo $color ?>" <?php if (isset($module['color']) && $module['reset_color'] === $color): ?>selected="selected"<?php endif; ?> value="<?php echo $color ?>"><?php echo $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php _e('OR', 'ptb') ?>
                        <input class="ptb_color_picker" type="text" name="[<?php echo $id ?>][custom_reset_color]" <?php if (!empty($module['custom_reset_color'])): ?>data-value="<?php echo $module['custom_reset_color'] ?>"<?php endif; ?> />
                    </div>
                </div>
                <?php
                break;
            case 'editor':
                ?>
                <input type="hidden" name="[<?php echo $id ?>][editor]" value="1" />
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_html"><?php _e('Use html editor(not recomended)', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_<?php echo $id ?>_html" type="checkbox" name="[<?php echo $id ?>][html]"
                               value="1" <?php if (isset($module['html'])): ?>checked="checked"<?php endif; ?> />
                    </div>
                </div>
                <?php
                break;
            case 'link_button':
                ?>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'text', __('Text for link label', 'ptb-search')); ?>
                </div>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'url', __('Text for link url', 'ptb-search')); ?>
                </div>
                <?php
                break;
            case 'custom_text':
            case 'plain_text':
            case 'custom_image':
                $this->get_main_fields($id, '', $module, $languages);
                break;
            default:
                ?>
                <?php do_action('ptb_search_template_' . $type, $id, $args, $module, $post_support, $languages); ?>
                <input type="hidden" name="[<?php echo $id ?>][<?php echo $id ?>]"/>
                <?php
                break;
        }
        ?>
        <?php if (false && !in_array($type, $post_support)): ?>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>_show_description"><?php _e('Show metabox description', 'ptb-search') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input id="ptb_<?php echo $id ?>_show_description" type="checkbox" name="[<?php echo $id ?>][show_description]"
                           value="1" <?php if (isset($module['show_description'])): ?>checked="checked"<?php endif; ?> />
                </div>
            </div>
        <?php endif; ?>
        <?php
    }
}
