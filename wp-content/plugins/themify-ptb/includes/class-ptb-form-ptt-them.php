<?php

class PTB_Form_PTT_Them {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    protected $type;
    protected $settings_section;
    protected $post_taxonomies;

    /**
     * The options management class of the the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PTB_Options $options Manipulates with plugin options
     */
    protected $options;
    public static $key = 'ptt';
    protected $themplate_id = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options the plugin options instance
     *
     */
    public function __construct($plugin_name, $version, $themplate_id = false) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->themplate_id = $themplate_id;
        $this->options = PTB::get_option();
    }

    /**
     * Add settings section for themplage
     *
     * @since    1.0.0
     *
     * @param string $type
     *
     */
    public function add_settings_section($type) {

        $this->type = $type;
        $this->settings_section = $this->plugin_name . '-ptt-' . $type;
        add_settings_section(
                $this->settings_section, '', array($this, 'main_section_cb'), $this->settings_section
        );
        require_once plugin_dir_path(dirname(__FILE__)) . '/admin/partials/ptb-admin-display-edit-ptt-them.php';
    }

    public function main_section_cb() {

        $ptt = $this->get_ptt();
        $languages = PTB_Utils::get_all_languages();
        $layout = isset($ptt[$this->type]['layout']) ? $ptt[$this->type]['layout'] : false;
        $post_taxonomies = $cmb_options = $post_support = array();
        $this->options->get_post_type_data($ptt['post_type'],$cmb_options,$post_support,$post_taxonomies);

        $this->post_taxonomies = array();
        if (!empty($post_taxonomies)) {
            foreach ($post_taxonomies as $t) {
				if ( in_array( $t, [ 'category', 'post_tag' ], true ) ) {
					continue;
				}
				if ( taxonomy_exists( $t ) ) {
					$this->post_taxonomies[$t] = get_taxonomy( $t )->labels->name;
				}
            }
            unset( $post_taxonomies );
        }

		/* the type of template being edited, eg: single, archive, search, etc. */
		$context = $this->type;
		if ( PTB_Form_PTT_Custom::is_custom_template( $context ) ) {
			$context = 'custom';
		}

		/* list of field types in PTB */
		$cmb_types = PTB_Options::get_cmb_types();

        $cmb_options = apply_filters('ptb_template_modules', $cmb_options, $context, $ptt['post_type']);
		$this->add_fields( $ptt[$this->type], $ptt['post_type'] );
        $sort_cmb = array();
        foreach ($cmb_options as $key=>$cmb){
            $sort_cmb[$key] = PTB_Utils::get_label( $cmb['name'], $key );
        }
        natcasesort($sort_cmb);
        ?>  
        <input type="hidden" value="<?php echo $this->type ?>" name="ptb_type"/>
        <input type="hidden" value="<?php echo $this->themplate_id ?>" name="ptb-<?php echo self::$key ?>"/>
        <input type="hidden" value="" name="ptb_layout" id="ptb_layout"/>
        <div class="ptb_back_builder">
            <?php //Metabox Buttons   ?>
            <div class="ptb_back_module_panel ptb_scrollbar">
                <?php  foreach ($sort_cmb as $meta_key => $name): ?>
                    <?php
                    $args = $cmb_options[$meta_key];
                    $type = sanitize_key($args['type']);
                    $meta_key = sanitize_key($meta_key);
                    $metabox = in_array( $type, $post_support,true ); /* false: this is PTB custom field */
                    $id = !$metabox ? $meta_key : $type;
                    ?>
                    <div data-type="<?php echo $type ?>"
                         id="ptb_cmb_<?php echo $meta_key ?>"
                         class="ptb_back_module<?php if (!$metabox): ?> ptb_is_metabox<?php endif; ?>"
						 <?php if ( ! $metabox && isset( $cmb_types[ $type ] ) ) : ?>data-ptb_type_label="<?php printf( __( 'Type: %s', 'ptb' ), $cmb_types[ $type ]['name'] ); ?>"<?php endif; ?>
					 >
                         <?php $this->draw_module_holder( $type, $id, $name, array(), $args, $context, $metabox, $post_support, $languages); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php //Dropping container  ?>
            <div class="ptb_back_row_panel" id="ptb_row_wrapper">
                <?php if (!empty($layout)): ?>
                    <?php foreach ($layout as $row_key => $_row): ?>
                        <?php
                        $row_css = !empty($_row['row_classes'])?$_row['row_classes']:'';
                        unset($_row['row_classes']);
                        $grid_keys = array_keys($_row);
                        $array_gid_keys = array();
                        foreach ($grid_keys as $keys) {
                            $tmp_keys = explode('-', $keys);
                            $array_gid_keys[] = $tmp_keys[0] . '-' . $tmp_keys[1];
                        }
                        $grid_keys = implode('-', $array_gid_keys);
                        ?>
                        <div
                            class="ptb_back_row ptb_expanded<?php if ($row_key === 0): ?> ptb_first_row<?php endif; ?>">
                            <?php $this->draw_grid($grid_keys,$row_css);?>
                            <div class="ptb_back_row_content">
                                <?php $count = 6 - count($_row);  //6 is the maximum number of grids   ?>
                                <?php if ($count > 0): ?>
                                    <?php for ($i = 0; $i < $count; ++$i): ?>
                                        <?php $_row[] = array(); //fill array for set maximum colums count ?>
                                    <?php endfor; ?>
                                <?php endif; ?>
                                <?php $first = true; ?>
                                <?php foreach ($_row as $col_key => $col): ?>
                                    <?php
                                                            
                                    $grid_keys = false;
                                    if (!is_numeric($col_key)) {
                                        $tmp_key = explode('-', $col_key);
                                        $grid_keys = $tmp_key[0] . '-' . $tmp_key[1];
                                    }
                                    ?>
                                    <div
                                        class="<?php if ($first && $grid_keys): ?>first <?php $first = false; ?><?php endif; ?>ptb_back_col<?php if ($grid_keys): ?> ptb_col<?php echo $grid_keys ?> ptb_show_grid<?php endif; ?>"
                                        <?php if ($grid_keys): ?>data-grid="<?php echo $grid_keys ?>"<?php endif; ?>>
                                        <?php
                                        $col_css = !empty($col['col_classes'])?$col['col_classes']:'';
                                        unset($col['col_classes']);
                                            $this->col_top($col_css);
                                        ?>

                                        <div class="ptb_module_holder">
                                            <div class="ptb_empty_holder_text"><?php _e('Drop module here', 'ptb') ?></div>
                                            <?php if (!empty($col)): ?>
                                                <?php foreach ($col as $module): ?>
                                                    <?php
                                                    $meta_key = sanitize_key($module['key']);
                                                    $args = $cmb_options[$meta_key];
                                                    $name = esc_html( PTB_Utils::get_label( $args['name'], $meta_key ) );
                                                    if ($module['type'] !== 'plain_text' && $module['type'] !== 'custom_text' && $module['type'] !== 'editor') {
                                                        foreach ($module as $mk=>&$values) {
                                                            if($mk!=='text_after' && $mk!=='text_before' && !empty($values)){
                                                                if (!is_array($values)) {
                                                                    $values = sanitize_text_field($values);
                                                                } else{
                                                                    foreach ($values as &$value) {
                                                                        $value = sanitize_text_field($value);
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $type = $module['type'];
                                                    $metabox = in_array($type, $post_support,true);
                                                    $id = !$metabox ? $meta_key : $type;
                                                    ?>
                                                    <div data-type="<?php echo $type ?>" class="ptb_back_module ptb_dragged">
                                                        <?php $this->draw_module_holder( $type, $id, $name, $module, $args, $context, $metabox, $post_support, $languages ); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ptb_back_row ptb_first_row ptb_new-themplate">
                         <?php $this->draw_grid();?>
                        <div class="ptb_back_row_content">
                            <?php //6 is the maximum number of grids   ?>
                            <?php for ($i = 0; $i < 6; ++$i): ?>
                                <div class="ptb_back_col">
                                    <div class="ptb_module_holder">
                                        <div class="ptb_empty_holder_text"><?php _e('Drop module here', 'ptb') ?></div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="ptb_add_row ptb_cmb_add_field"><span><?php echo PTB_Utils::get_icon( 'ti-plus' ); ?></span> <?php _e('Add Row', 'ptb') ?></div>
            </div>
        </div>
        <?php
    }

    protected function get_field_name($input_key) {
        return sprintf('%s_%s_%s', $this->plugin_name, self::$key, $input_key);
    }

    protected function get_field_id($field_key) {

        return sprintf('%s_%s_%s', $this->plugin_name, self::$key, $field_key);
    }
    
    private function draw_grid($grid_keys=NULL, $row_classes=''){
        $grids = array(
            array('1-1'),
            array('4-2','4-2'),
            array('3-1','3-1','3-1'),
            array('4-1','4-1','4-1','4-1'),
            array('5-1','5-1','5-1','5-1','5-1'),
            array('6-1','6-1','6-1','6-1','6-1','6-1')
        );
        ?>
        <div class="ptb_back_row_top">
            <div class="ptb_left">
                <div class="ptb_grid_menu">
                    <a class="ptb_row_btn ptb_grid_options"><?php echo PTB_Utils::get_icon( 'ti-layout-column3' ); ?></a>
                    <div class="ptb_grid_list_wrapper">
                        <ul class="ptb_grid_list tf_clearfix">
                            <li>
                                <ul>
                                    <?php $this->draw_grid_keys($grids, $grid_keys);?>
                                </ul>
                            </li>
                            <li>
                                <ul>
                                    <?php 
                                     $grids = array(
                                        array('4-1','4-3'),
                                        array('4-1','4-1','4-2'),
                                        array('4-1','4-2','4-1'),
                                        array('4-2','4-1','4-1'),
                                        array('4-3','4-1')
                                    );
                                    $this->draw_grid_keys($grids, $grid_keys);?>
                                </ul>
                            </li>
                            <li>
                                <ul>
                                    <?php 
                                     $grids = array(
                                        array('3-2','3-1'),
                                        array('3-1','3-2')
                                    );
                                    $this->draw_grid_keys($grids, $grid_keys);?>
                                </ul>
                            </li>
                        </ul>
                        <label>
                            <input class="ptb_row_custom_css ptb_input_width_40" type="text" value="<?php esc_attr_e($row_classes)?>" name="<?php echo $this->get_field_name('row_class')?>" />
                            <?php _e('Custom CSS Class','ptb')?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="ptb_right">
                <a href="#" class="ptb_row_btn ptb_toggle_module"><?php echo PTB_Utils::get_icon( 'ti-angle-up' ); ?></a>
                <a href="#" class="ptb_row_btn ptb_delete_module"><?php echo PTB_Utils::get_icon( 'ti-close' ); ?></a>
            </div>
        </div>
        <?php
    }

    private function col_top($col_classes='') {
        ?>

        <div class="ptb_back_col_top">
            <div class="ptb_left">
                <div class="ptb_grid_menu">
                    <a class="ptb_row_btn ptb_col_settings"><?php echo PTB_Utils::get_icon( 'ti-more' ); ?></a>
                    <div class="ptb_grid_list_wrapper">
                        <label>
                            <input class="ptb_col_custom_css ptb_input_width_40" type="text" value="<?php esc_attr_e($col_classes)?>" name="<?php echo $this->get_field_name('col_class')?>" />
		                    <?php _e('Custom CSS Class','ptb')?>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
    
    private function draw_grid_keys($grids,$selected){
        ?>
        <?php foreach($grids as $grid):?>
            <?php $k = implode('-',$grid);
                $keys = array();
            ?>
            <li <?php if ($selected === $k): ?>class="selected"<?php endif; ?>>
                <?php foreach($grid as $g):?>
                    <?php $keys[]= '"'.$g.'"';?>
                <?php endforeach;?>
                <a href="#" title="<?php echo $k?>" class="ptb_column_select ptb_grid_<?php echo str_replace('-', '_', $k)?>" data-grid=[<?php echo implode(',',$keys)?>]></a>
            </li>
        <?php endforeach;?>
        <?php
    }

	/**
	 * @args string $context
	 */
    private function draw_module_holder( $type, $id, $name, $module, $args, $context, $metabox,$post_support,$languages){
		$cmb_types = PTB_Options::get_cmb_types();
		?>
            <strong class="ptb_module_name"><?php echo $name ?></strong>
            <div class="ptb_active_module">
                <div class="ptb_back_module_top">
                    <div class="ptb_left">
                        <span class="ptb_back_active_module_title">
							<?php echo $name ?>
							<?php if ( isset( $cmb_types[ $args['type'] ] ) ) : ?> [<?php echo $cmb_types[ $args['type'] ]['name']; ?>]<?php endif; ?>
						</span>
                    </div>
                    <div class="ptb_right">
                        <a href="#" class="ptb_module_btn ptb_toggle_module"><?php echo PTB_Utils::get_icon( 'ti-angle-up' ); ?></a>
                        <a href="#" class="ptb_module_btn ptb_delete_module"><?php echo PTB_Utils::get_icon( 'ti-close' ); ?></a>
                    </div>
                </div>
                <div data-type="<?php echo $type ?>" class="ptb_back_active_module_content">                                                                                                                         
                    <?php do_action('before_template_row', $id, $module, $this->type, $languages); ?>
                    <?php
					if ( has_action( "ptb_field_{$type}_template" ) ) {
						/* per field type hook */
                        do_action( "ptb_field_{$type}_template", $id, $context, $args, $module, $post_support, $languages );
                    } else if ( has_action( "ptb_{$context}_template" ) ) {
						/* this is used by PTB Search and Relations addons */
                        do_action( "ptb_{$context}_template", $type, $id, $args, $module, $post_support, $languages );
                    } else { ?>
                        <?php if (!$metabox): ?>
                            <?php do_action('ptb_template_' . $type, $id, $this->type, $args, $module, $languages) ?>
                        <?php else: ?>
                            <?php $this->get_main_fields($id, $name, $module, $languages) ?>
                        <?php endif; ?>  
                        <?php
                        if ($type !== 'plain_text' && $type !== 'custom_text' && $type !== 'editor') {
                            PTB_CMB_Base::module_multi_text($id, $module, $languages, 'text_before', __('Text Before', 'ptb'));
                            PTB_CMB_Base::module_multi_text($id, $module, $languages, 'text_after', __('Text After', 'ptb'));
                       
                            $icon_position = array(
                                'before_text_before'=>__('Before "Text Before"','ptb'),
                                'after_text_before'=>__('After "Text Before"','ptb'),
                                'before_text_after'=>__('Before "Text After"','ptb'),
                                'after_text_after'=>__('After "Text After"','ptb')
                            );
                        
                        ?>
                            <div class="ptb_back_active_module_row">
                                <div class="ptb_back_active_module_label">
                                    <label for="ptb_<?php echo $id ?>_field_icon"><?php _e('Icon', 'ptb') ?></label>
                                </div>
                                <div class="ptb_back_active_module_input" data-ptb_icon_picker_container>
                                    <input type="text" name="[<?php echo $id ?>][field_icon]" value="<?php echo !empty($module['field_icon'])? $module['field_icon'] : '' ?>" id="ptb_<?php echo $id ?>_field_icon" data-ptb_icon_picker_value />
                                    <a title="<?php _e('Icon Picker', 'ptb') ?>" href="#" class="ptb_icon_picker" data-ptb_icon_picker><?php _e('Icon', 'ptb') ?></a>
                                </div>
                            </div>
                            <div class="ptb_back_active_module_row">
                                <div class="ptb_back_active_module_label">
                                    <label><?php _e('Show icon', 'ptb') ?></label>
                                </div>
                                <div class="ptb_back_active_module_input">
                                    <?php   foreach ($icon_position as $pos=>$pos_val):?>
                                        <input type="radio" id="ptb_<?php echo $id?>_field_icon_radio_<?php echo $pos ?>"
                                            name="[<?php echo $id ?>][icon_pos]" value="<?php echo $pos ?>"
                                            <?php if ((!isset($module['icon_pos']) && $pos === 'before_text_before') || ( isset($module['icon_pos']) && $module['icon_pos'] === $pos)): ?>checked="checked"<?php endif; ?>/>
                                        <label for="ptb_<?php echo $id?>_field_icon_radio_<?php echo $pos ?>"><?php echo $pos_val ?></label>
                                    <?php endforeach;?>
                                </div>
                            </div>
                        <?php }?>
                        <div class="ptb_back_active_module_row">
                            <div class="ptb_back_active_module_label">
                                <label for="ptb_<?php echo $id ?>[css]"><?php _e('Custom CSS Class', 'ptb') ?></label>
                            </div>
                            <div class="ptb_back_active_module_input">
                                <input id="ptb_<?php echo $id ?>[css]" class="ptb_towidth" type="text"  name="[<?php echo $id ?>][css]" value="<?php echo !empty($module['css'])? $module['css'] : '' ?>" />
                            </div>
                        </div>
						<?php if ( $type !== 'relation' ) : ?>
							<div class="ptb_back_active_module_row">
								<div class="ptb_back_active_module_label">
									<label for="ptb_<?php echo $id ?>[display_inline]"><?php _e('Display Inline', 'ptb') ?></label>
								</div>
								<div class="ptb_back_active_module_input">
									<label>
										<input id="ptb_<?php echo $id ?>[display_inline]" type="checkbox"
											   name="[<?php echo $id ?>][display_inline]"
											   <?php if (!empty($module['display_inline'])): ?>checked="checked"<?php endif; ?>  />
											   <?php _e('Display this module inline (float left)', 'ptb'); ?>
									</label>
								</div>
							</div>
						<?php endif; ?>
                    <?php } ?>
                    <?php do_action('after_template_row', $id, $module, $this->type, $languages); ?>
                </div>
            </div>
        <?php
    }

    /**
     * Save post themplate
     *
     * @since 1.0.0
     *
     * @param post array $data
     */
    public function save_themplate($data) {
        $post_type = $this->get_ptt();

        if ($post_type) {
            $this->type = $data[$this->plugin_name . '_type'];
            if (!isset($post_type[$this->type])) {
                $post_type[$this->type] = array();
            }
            $post_type[$this->type]['layout'] = array();
            if (isset($data[$this->plugin_name . '_layout'])) {
				if ( $data[$this->plugin_name . '_layout'] === 0 ) {
					unset( $post_type[$this->type] );
				} else {
					$layout = stripslashes_deep($data[$this->plugin_name . '_layout']);
					$post_type[$this->type]['layout'] = json_decode($layout, true);
				}
            }
			
            $_keys = $this->type == PTB_Post_Type_Template::ARCHIVE ? array('layout_post', 'offset_post', 'orderby_post', 'order_post', 'pagination_post','masonry') : array('navigation_post','same_category','same_tax');
            foreach ($_keys as $key) {
                $fieldname = $this->get_field_name($key);
                if (isset($data[$fieldname])) {
                    $post_type[$this->type][$fieldname] = sanitize_text_field($data[$fieldname]);
                } else if( ! empty( $post_type[$this->type][$fieldname] ) && ! isset( $data[$fieldname] ) ) {
					$post_type[$this->type][$fieldname] = '';
				}
            }
            $post_type = apply_filters('ptb_template_save', $post_type, $data);
            $this->options->option_post_type_templates[$this->themplate_id] = $post_type;
            $this->options->update();
            die(json_encode(array(
                'status' => '1'
            )));
        }
    }

    protected function get_ptt() {
        $ptt = null;
        if ($this->options->has_post_type_template($this->themplate_id)) {
            $ptt_options = $this->options->get_templates_options();
            $ptt = $ptt_options[$this->themplate_id];
        }

        return $ptt;
    }

    protected function get_edit_value($key, $default) {

        $ptt = $this->get_ptt();

        $value = ( isset($ptt) && array_key_exists($key, $ptt) ? $ptt[$key] : $default );

        return $value;
    }

    /**
     * Render post fields
     *
     * @since 1.0.0
     * @param string $type
     * @param string $name
     * @param array $data
     * @param array $languages
     */
    protected function get_main_fields($type, $name, array $data = array(), array $languages = array()) {
        switch ($type):
            case 'editor':
            case 'author':
            case 'comments':
                ?>
                <input type="hidden" name="[<?php echo $type ?>][<?php echo $type ?>]"/>
                <?php break; ?>
            <?php
            case 'title':
                ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_title_tag"><?php _e('HTML Tag', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select name="[<?php echo $type ?>][title_tag]" id="<?php echo $this->plugin_name ?>_title_tag">
                                <?php for ($i = 1; $i <= 6; ++$i): ?>
                                    <option
                                        <?php if (isset($data['title_tag']) && $data['title_tag'] == $i): ?>selected="selected"<?php endif; ?>
                                        value="<?php echo $i ?>">h<?php echo $i ?></option>
								<?php endfor; ?>
								<option value="p"<?php if ( isset( $data['title_tag'] ) && $data['title_tag'] == 'p' ) : ?> selected="selected"<?php endif; ?>>Paragraph</option>
								<option value="div"<?php if ( isset( $data['title_tag'] ) && $data['title_tag'] == 'div' ) : ?> selected="selected"<?php endif; ?>>Div</option>
                            </select>
                        </div>
                    </div>
                </div>
                <?php PTB_CMB_Base::link_to_post('title', $this->type, $data); ?>
                <?php break; ?>
            <?php case 'excerpt': ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label"><label for="ptb_excerpt_count"><?php _e('Word Count', 'ptb') ?></label></div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_excerpt_count" type="text" class="ptb_xsmall"
                               name="[<?php echo $type ?>][excerpt_count]"
                               <?php if (isset($data['excerpt_count'])): ?>value="<?php echo $data['excerpt_count'] ?>"<?php endif; ?> />
                               <?php _e('Words', 'ptb') ?>
                        <input type="hidden" value="1" name="[<?php echo $type ?>][can_be_empty]" />
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label"><label for="ptb_excerpt_readmore"><?php _e('More Link', 'ptb') ?></label></div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_excerpt_readmore" type="checkbox"
                                name="[<?php echo $type ?>][readmore]" value="1"
	                    <?php if (isset($data['readmore'])): ?>checked="checked"<?php endif; ?>
                        >
                        <?php _e('Add read more link', 'ptb');?>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label"><label for="ptb_excerpt_readmore_text">&nbsp;</label></div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_excerpt_readmore_text" type="text" class=""
                               name="[<?php echo $type ?>][readmore_text]"
		                       <?php if (isset($data['readmore_text'])): ?> value="<?php echo $data['readmore_text'] ?>"<?php else: ?> placeholder="<?php _e('Read More..', 'ptb'); ?>"<?php endif; ?> />
		                <?php _e('More Link Text', 'ptb') ?>
                    </div>
                </div>
                <?php break; ?>
            <?php case 'custom_text': ?>
                <div class="ptb_back_active_module_row ptb_<?php echo $type ?>">
                    <?php if (count($languages) > 1): ?>
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
                                <textarea class="ptb_wp_editor"
                                          name="[<?php echo $type ?>][text][<?php echo $code ?>]">
                                              <?php if (isset($data['text'][$code])): ?> <?php echo $data['text'][$code] ?><?php endif; ?>
                                </textarea>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php break; ?>
                <?php case 'plain_text': ?>
                    <div class="ptb_back_active_module_row ptb_<?php echo $type ?>">
                        <?php if (count($languages) > 1): ?>
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
                                <textarea name="[<?php echo $type ?>][text][<?php echo $code ?>]"><?php if (isset($data['text'][$code])): ?><?php echo $data['text'][$code] ?><?php endif; ?></textarea>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php break; ?>
            <?php case 'taxonomies': ?>
                <?php if (!empty($this->post_taxonomies)): ?>

                    <div class="ptb_back_active_module_row">
                        <div class="ptb_back_active_module_label">
                            <label for="ptb_select_taxonomies"><?php _e('Select Taxonomies', 'ptb') ?></label>
                        </div>
                        <div class="ptb_back_active_module_input">
                            <div class="ptb_custom_select">
                                <select id="ptb_select_taxonomies" name="[<?php echo $type ?>][taxonomies]">
                                    <?php foreach ($this->post_taxonomies as $tax => $tax_name): ?>
                                        <option
                                            <?php if (isset($data['taxonomies']) && $data['taxonomies'] === $tax): ?>selected="selected"<?php endif; ?>
                                            value="<?php echo $tax ?>"><?php echo $tax_name ?></option>
                                        <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="ptb_back_active_module_row">
                        <div class="ptb_back_active_module_label">
                            <label for="ptb_taxonomies_display_order"><?php _e('Display Order', 'ptb') ?></label>
                        </div>
                        <div class="ptb_back_active_module_input">
                            <div class="ptb_custom_select">
                                <select id="ptb_taxonomies_display_order" name="[<?php echo $type ?>][display_order]">
                                        <option
                                            <?php if (isset($data['display_order']) && $data['display_order'] === 'by_name'): ?>selected="selected"<?php endif; ?>
                                            value="by_name"><?php _e('Term name','ptb'); ?></option>
                                        <option
                                            <?php if (isset($data['display_order']) && $data['display_order'] === 'by_id'): ?>selected="selected"<?php endif; ?>
                                            value="by_id"><?php _e('ID number','ptb'); ?></option>
                                </select>
                            </div>
                            &nbsp;
                            <div class="ptb_custom_select">
                                <select id="ptb_taxonomies_display_order_direction" name="[<?php echo $type ?>][order_direction]">
                                    <option
                                        <?php if (isset($data['order_direction']) && $data['order_direction'] === 'asc'): ?>selected="selected"<?php endif; ?>
                                        value="asc"><?php _e('Ascending','ptb'); ?></option>
                                    <option
                                        <?php if (isset($data['order_direction']) && $data['order_direction'] === 'desc'): ?>selected="selected"<?php endif; ?>
                                        value="desc"><?php _e('Descending','ptb'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="ptb_back_active_module_row">
                        <div class="ptb_back_active_module_label">
                            <label for="ptb_seperator_taxonomies"><?php _e('Separator', 'ptb') ?></label>
                        </div>
                        <div class="ptb_back_active_module_input">
                            <input id="ptb_seperator_taxonomies" type="text" class="ptb_towidth"
                                   name="[<?php echo $type ?>][seperator]"
                                   <?php if (isset($data['seperator'])): ?>value="<?php echo $data['seperator'] ?>"<?php endif; ?> />
                        </div>
                    </div>
                    <div class="ptb_back_active_module_row">
                        <div class="ptb_back_active_module_label">
                            <label for="ptb_linked_terms"><?php _e('Linked', 'ptb') ?></label>
                        </div>
                        <div class="ptb_back_active_module_input">
							<select id="ptb_linked_terms" name="[<?php echo $type ?>][link]">
								<option <?php if ( isset( $data['link'] ) && $data['link'] === 'yes' ) : ?>selected="selected"<?php endif; ?> value="yes"><?php _e('Yes', 'ptb'); ?></option>
								<option <?php if ( isset( $data['link'] ) && $data['link'] === 'no' ) : ?>selected="selected"<?php endif; ?> value="no"><?php _e('No', 'ptb'); ?></option>
							</select>
                        </div>
                    </div>
                    <div class="ptb_back_active_module_row">
                        <div class="ptb_back_active_module_label">
                            <label for="ptb_image"><?php _e('Show Cover Image', 'ptb') ?></label>
                        </div>
                        <div class="ptb_back_active_module_input">
							<select id="ptb_image" name="[<?php echo $type ?>][image]">
								<option <?php if ( isset( $data['image'] ) && $data['image'] === 'no' ) : ?>selected="selected"<?php endif; ?> value="no"><?php _e('No', 'ptb'); ?></option>
								<option <?php if ( isset( $data['image'] ) && $data['image'] === 'yes' ) : ?>selected="selected"<?php endif; ?> value="yes"><?php _e('Yes', 'ptb'); ?></option>
							</select>
                        </div>
                    </div>
					<div class="ptb_back_active_module_row">
						<div class="ptb_back_active_module_label">
							<label><?php _e('Image Dimension', 'ptb') ?></label>
						</div>
						<div class="ptb_back_active_module_input">
							<input id="ptb_image_width" type="text" class="ptb_xsmall" name="[<?php echo $type ?>][image_w]"
								   <?php if (isset($data['image_w'])): ?>value="<?php echo $data['image_w'] ?>"<?php endif; ?> />
							<label for="ptb_image_width"><?php _e('Width', 'ptb') ?></label>
							<input id="ptb_image_height" type="text" class="ptb_xsmall" name="[<?php echo $type ?>][image_h]"
								   <?php if (isset($data['image_h'])): ?>value="<?php echo $data['image_h'] ?>"<?php endif; ?> />
							<label for="ptb_image_height"><?php _e('Height (px)', 'ptb') ?></label>
						</div>
					</div>
                <?php endif; ?>
                <?php break; ?>
            <?php case 'date': ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_date_format"><?php _e('Date Format', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_date_format" type="text"
                               class="ptb_towidth" name="[<?php echo $type ?>][date_format]"
                               <?php if (isset($data['date_format'])): ?>value="<?php echo $data['date_format'] ?>"<?php endif; ?> />
                               <?php _e('(e.g. M j,Y)', 'ptb') ?> <a
                            href="https://wordpress.org/support/article/formatting-date-and-time/"
                            target="_blank"><?php _e('More info', 'ptb') ?></a>
                    </div>
                </div>
                <?php break; ?>
            <?php
            case 'post_tag':
            case 'category':
                ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_category_seperator"><?php _e('Separator', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_category_seperator" type="text" class="ptb_towidth"
                               name="[<?php echo $type ?>][seperator]"
                               <?php if (isset($data['seperator'])): ?>value="<?php echo $data['seperator'] ?>"<?php endif;
                               ?> />
                    </div>
                </div>
				<div class="ptb_back_active_module_row">
					<div class="ptb_back_active_module_label">
						<label for="ptb_linked_terms"><?php _e('Linked', 'ptb') ?></label>
					</div>
					<div class="ptb_back_active_module_input">
						<select id="ptb_linked_terms" name="[<?php echo $type ?>][link]">
							<option <?php if ( isset( $data['link'] ) && $data['link'] === 'yes' ) : ?>selected="selected"<?php endif; ?> value="yes"><?php _e('Yes', 'ptb'); ?></option>
							<option <?php if ( isset( $data['link'] ) && $data['link'] === 'no' ) : ?>selected="selected"<?php endif; ?> value="no"><?php _e('No', 'ptb'); ?></option>
						</select>
					</div>
				</div>
                <?php break; ?>
            <?php case 'thumbnail': ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_thumbnail_width"><?php _e('Image Dimension', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_thumbnail_width" type="text" class="ptb_xsmall"
                               name="[<?php echo $type ?>][width]"
                               <?php if (isset($data['width'])): ?>value="<?php echo $data['width'] ?>"<?php endif; ?> />
                        <label><?php _e('Width', 'ptb') ?></label>
                        <input type="text" class="ptb_xsmall"
                               name="[<?php echo $type ?>][height]"
                               <?php if (isset($data['height'])): ?>value="<?php echo $data['height'] ?>"<?php endif; ?> />
                        <label><?php _e('Height', 'ptb') ?> (px)</label>
                    </div>
                </div>
                <?php PTB_CMB_Base::link_to_post('thumbnail', $this->type, $data); ?>
                <?php break; ?>
            <?php case 'custom_image': ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_custom_image_file"><?php _e('Image File', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_post_image_wrapper">
                            <div class="ptb_post_image_thumb_wrapper">
                                <div class="ptb_post_image_thumb" <?php if (isset($data['image'])): ?> style="background-image: url(<?php echo $data['image'] ?>)"<?php endif; ?>></div>
                            </div>
                            <div class="ptb_post_image_add_wrapper">
                                <input id="ptb_custom_image_file" type="text" class="ptb_towidth"
                                       name="[<?php echo $type ?>][image]"
                                       <?php if (isset($data['image'])): ?>value="<?php echo $data['image'] ?>"<?php endif; ?> />
                                <a href="#" onclick="PTB.ImageUpload(this)" class="ptb_post_image_add">+<?php _e('Media Library', 'ptb') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Image Dimension', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_width" type="text"
                               class="ptb_xsmall"
                               name="[<?php echo $type ?>][width]"
                               <?php if (isset($data['width'])): ?>value="<?php echo $data['width'] ?>"<?php endif; ?> />
                        <label for="ptb_width"><?php _e('Width', 'ptb') ?></label>
                        <input id="ptb_height" type="text"
                               class="ptb_xsmall"
                               name="[<?php echo $type ?>][height]"
                               <?php if (isset($data['height'])): ?>value="<?php echo $data['height'] ?>"<?php endif; ?> />
                        <label for="ptb_height"><?php _e('Height', 'ptb') ?> (px)</label>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_custom_image_link"><?php _e('Image Link', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_custom_image_link" type="text" class="ptb_towidth" name="[<?php echo $type ?>][link]"
                               <?php if (isset($data['link'])): ?>value="<?php echo $data['link'] ?>"<?php endif; ?>/>
                    </div>
                </div>
            <?php break; ?>
            <?php case 'comment_count': ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Link To Comment Page', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input type="radio" id="ptb_<?php echo $type ?>_radio_yes"
                           name="[<?php echo $type ?>][link_to_comment]" value="yes"
                           <?php if (!isset($data['link_to_comment']) || (isset($data['link_to_comment']) && $data['link_to_comment']==='yes')): ?>checked="checked"<?php endif; ?>/>
                        <label for="ptb_<?php echo $type ?>_radio_yes"><?php _e('Yes','ptb')?></label>
                        
                        <input type="radio" id="ptb_<?php echo $type ?>_radio_no"
                           name="[<?php echo $type ?>][link_to_comment]" value="no"
                           <?php if (isset($data['link_to_comment']) && $data['link_to_comment']==='no'): ?>checked="checked"<?php endif; ?>/>
                        <label for="ptb_<?php echo $type ?>_radio_no"><?php _e('No','ptb')?></label>
                    </div>
                </div>
                <?php PTB_CMB_Base::module_multi_text($type, $data, $languages, 'zero', __('Text when there are no comments', 'ptb')); ?>
                <?php PTB_CMB_Base::module_multi_text($type, $data, $languages, 'one', __('Text when there is one comment', 'ptb')); ?>
                <?php PTB_CMB_Base::module_multi_text($type, $data, $languages, 'more', __('Text when there is more than one comment', 'ptb')); ?>
            <?php break; ?>
            <?php case 'permalink': ?>
                <?php PTB_CMB_Base::module_multi_text($type, $data, $languages, 'text', __('Text', 'ptb')); ?>
                <?php do_action('ptb_template_link_button', $type, $this->type, array(), $data, $languages) ?>
                <?php break; ?>
        <?php endswitch; ?>

        <?php
    }

    /**
     * Frontend layout render
     *
     * @since 1.0.0
     * @param array   $template
     * @param array   $post_support
     * @param array   $cmb_options
     * @param array   $post_meta
     * @param string  $post_type 
     * @param boolean $is_single 
     */
    public function display_public_themplate(array $template, array $post_support, array $cmb_options, array $post_meta, $post_type, $is_single = false) {
        $post_meta = apply_filters('ptb_filter_post_meta', $post_meta, $post_type, $cmb_options, $is_single);
        $lang = PTB_Utils::get_current_language_code();
        $layout = $template['layout'];
        $count = count($layout) - 1;
        ob_start();
        ?>
        <div class="ptb_items_wrapper entry-content" itemscope itemtype="https://schema.org/MediaObject">
            <?php foreach ($layout as $k => $row): ?>
                <?php
                $class= $k === 0?'first':($k === $count?'last':'');
                $row_class = !empty($row['row_classes'])?esc_attr($row['row_classes']):'';
                unset($row['row_classes']);
                ?>
                <div class="<?php if ($class): ?>ptb_<?php echo $class ?>_row <?php endif; ?>ptb_row ptb_<?php echo $post_type ?>_row <?php echo $row_class ?>">
                    <?php
                    if (!empty($row)):
                        $colums_count = count($row) - 1;
                        $i = 0;
						$exclude_from_sanitization = [ 'text_before', 'text_after', 'seperator' ];
                        foreach ($row as $col_key => $col):
                            ?>
                            <?php
	                        $col_class = !empty($col['col_classes'])?esc_attr($col['col_classes']):'';
	                        unset($col['col_classes']);
                            $tmp_key = explode('-', $col_key);
                            $key = $tmp_key[0] . '-' . $tmp_key[1];
                            ?>
                            <div class="ptb_col ptb_col<?php echo $key ?><?php if ($i === 0): ?> ptb_col_first<?php elseif ($i === $colums_count): ?> ptb_col_last<?php endif; ?> <?php echo $col_class ?>">
                                <?php if (!empty($col)): ?>
                                    <?php foreach ($col as $index => $module): ?>
                                        <?php
                                        if(!isset($module['key'])){
                                            continue;
                                        }
                                        $type = $module['type'];
                                        $meta_key = $module['key'];
										$is_custom_type = has_action( 'ptb_custom_' . $type );
                                        if (!isset($cmb_options[$meta_key]) && ! $is_custom_type ) {
                                            continue;
                                        }
                                        if ($module['type'] !== 'plain_text' && $module['type'] !== 'custom_text' && $module['type'] !== 'editor') {
                                            foreach ($module as $mk=>&$values) {
                                                if ( ! in_array( $mk, $exclude_from_sanitization, true ) && ! empty( $values ) ) {
                                                    if (!is_array($values)) {
                                                        $values = sanitize_text_field($values);
                                                    } 
                                                    else{
                                                        foreach ($values as &$value) {
                                                            $value = sanitize_text_field($value);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else {
                                            $meta_data = array();
                                        }
                                        $args = isset( $cmb_options[$meta_key] ) ? $cmb_options[$meta_key] : [];
                                        $args['key'] = $meta_key;
                                        $fields = in_array($type, $post_support,true); 
                                        $is_exist = !$fields && (!empty($post_meta['ptb_' . $meta_key]) || (isset($post_meta['ptb_' . $meta_key]) && $post_meta['ptb_' . $meta_key]==='0'));
                                        if ($fields || isset($args['can_be_empty']) || isset($module['can_be_empty']) || $is_exist || $is_custom_type ):
                                            if (!$fields) {
                                                $meta_value = $is_exist? $post_meta['ptb_' . $meta_key] : false;
                                                if ($meta_value || $meta_value==='0') {
                                                    $meta_data = maybe_unserialize(current($meta_value));
                                                    if ($meta_data === false) {
                                                        $meta_data = current($meta_value);
                                                    }
                                                    if (!isset($meta_data[$meta_key]) || !is_array($meta_data)) {
                                                        $post_meta[$meta_key] = $meta_data;
                                                        if (!is_array($meta_data)) {
                                                            $meta_data = array($meta_data);
                                                        }
                                                    }
                                                    $meta_data = array_merge($meta_data, $post_meta);
                                                } else {
                                                    $meta_data = $post_meta;
                                                }
                                            }
                                            if( !isset($args['can_be_empty'])  && ! $fields && ( ! isset( $meta_data[$args['key']] ) || '' === $meta_data[$args['key']] ) ){
												if ( ! $is_custom_type ) {
													continue;
												}
                                            }
											$classes = array( 'ptb_module', 'ptb_' . $type );
											if ( ! empty( $module['css'] ) ) {
												$classes[] = trim( $module['css'] );
											}
											if ( ! $fields ) {
												$classes[] = 'ptb_' . $meta_key;
											}
											if ( isset( $module['display_inline'] ) ) {
												$classes[] = 'ptb_module_inline';
											}
											$before = '<div class="' . join( ' ', $classes ) . ' tf_clearfix">';
											$after = '</div><!-- .' . join( '.', $classes ) . ' -->';
                                            ?>

                                                <?php
                                                $ptb_empty_field = false;
												if (has_action('ptb_custom_' . $type)) {
                                                    echo $before;
													do_action('ptb_custom_' . $type, $args, $module, $meta_data, $lang, $is_single, $k . '_' . $col_key . '_' . $index);
													echo $after;
                                                } else {
                                                    ob_start();
                                                    if ($fields) {
                                                        $ptb_empty_field = $this->get_public_main_fields(false, $type, $args, $module, $post_meta, $lang, $is_single, $k . '_' . $col_key . '_' . $index);
                                                    } else {
                                                        $ptb_empty_field = apply_filters('ptb_template_public' . $type, false, $args, $module, $meta_data, $lang, $is_single, $k . '_' . $col_key . '_' . $index);
                                                    }
                                                    $cont = trim(ob_get_contents());
                                                    ob_end_clean();
                                                    if ($cont!=='' && $cont!==false) {
														echo $before;
                                                        $icon = !empty($module['field_icon'])?$module['field_icon']:false;
                                                        $icon_pos = $icon && !empty($module['icon_pos'])?$module['icon_pos']:false;
                                                        if($icon_pos==='before_text_before'){
                                                            PTB_CMB_Base::get_icon($icon, $icon_pos);
                                                        }
                                                        if (isset($module['text_before'][$lang]) && $type != 'title' && !$ptb_empty_field) {
                                                            PTB_CMB_Base::get_text_after_before($module['text_before'][$lang], true);
                                                        }
                                                        if($icon_pos==='after_text_before'){
                                                            PTB_CMB_Base::get_icon($icon, $icon_pos);
                                                        }
                                                        echo $cont;
                                                        if($icon_pos==='before_text_after'){
                                                            PTB_CMB_Base::get_icon($icon, $icon_pos);
                                                        }
                                                        if (isset($module['text_after'][$lang])) {
                                                            PTB_CMB_Base::get_text_after_before($module['text_after'][$lang], false);
                                                        }
                                                        if($icon_pos==='after_text_after'){
                                                            PTB_CMB_Base::get_icon($icon, $icon_pos);
                                                        }
														echo $after;
                                                    }
                                                }
                                                ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php ++$i; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if ($is_single && ! PTB_Public::is_lightbox() && !empty($template['ptb_ptt_navigation_post'])): ?>
                <?php $is_same_cat = !empty($template['ptb_ptt_same_category']);
                      $same_tax = $is_same_cat && !empty($template['ptb_ptt_same_tax'])?$template['ptb_ptt_same_tax']:'category';		// By default it is category. passing empty sting will break the links.
                ?>
                <div class="ptb-post-nav tf_clearfix">
                    <?php previous_post_link('<span class="ptb-prev">%link</span>', '<span class="ptb-arrow">' . _x('&laquo;', 'Previous entry link arrow', 'ptb') . '</span> %title', $is_same_cat,'',$same_tax) ?>
                    <?php next_post_link('<span class="ptb-next">%link</span>', '<span class="ptb-arrow">' . _x('&raquo;', 'Next entry link arrow', 'ptb') . '</span> %title', $is_same_cat,'',$same_tax) ?>
                </div> 
            <?php endif; ?>  
        </div>  
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Frontend post fields render
     *
     * @since 1.0.0
     * @param string $type
     * @param array $args
     * @param array $data
     * @param array $meta_data
     * @param array $lang
     * @param boolean $is_single single page
     * @param string $index index in themplate
     */
    protected function get_public_main_fields($ptb_empty_field, $type, array $args, array $data, array $meta_data, $lang = false, $is_single = false, $index = false) {
		$post_type = $meta_data['post_type'];
		if( $template_file = PTB_Public::get_instance()->locate_template(
			/* order of the template files, from top to bottom */
			array(
				"field--{$post_type}-{$type}.php",
				"field--{$type}.php",
			),
                        $type
		) ) {

			do_action( "ptb_before_{$type}" );

			include $template_file;

			do_action( "ptb_after_{$type}" );
		}
		return $ptb_empty_field;
    }

}
