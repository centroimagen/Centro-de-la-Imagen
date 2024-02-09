<?php

class PTB_Form_PTT {

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
    protected $settings_section;
    protected $slug_admin_ptt;
    protected $options_key;

    /**
     * The options management class of the the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PTB_Options $options Manipulates with plugin options
     */
    protected $options;
    protected $key;

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
    public function __construct($plugin_name, $version, $options) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->key = 'ptt';

        $this->options_key = 'ptb_plugin_options';
        $this->options = $options;
    }

    public function add_settings_fields($slug_admin_ptt) {
        $this->slug_admin_ptt = $slug_admin_ptt;

        add_settings_section(
                $this->settings_section, '', array($this, 'main_section_cb'), $this->slug_admin_ptt
        );

        $this->add_fields();
    }

    public function main_section_cb() {

        printf(
                '<input type="hidden" id="%1$s" name="%2$s[%1$s]" value="%3$s" />', $this->get_field_id(PTB_Post_Type_Template::ID), $this->options_key, esc_attr($this->get_edit_id())
        );
    }

    private function get_field_id($input_key) {

        return sprintf('%s_%s_%s', $this->plugin_name, $this->key, $input_key);
    }

    private function get_callback_name($input_key) {

        return sprintf('%s_%s_%s', $this->key, $input_key, 'cb');
    }

    private function get_edit_id() {

        $id = '';

        if ('edit' === $_REQUEST['action']) {

            $id = $_REQUEST[sprintf('%s-%s', $this->plugin_name, $this->key)];
        }

        return $id;
    }

    private function get_ptt() {

        $ptt = null;

        $id = $this->get_edit_id();

        if ($this->options->has_post_type_template($id)) {

            $ptt_options = $this->options->get_templates_options();
            $ptt = $ptt_options[$id];
        }

        return $ptt;
    }

    private function get_edit_value($key, $default) {

        $ptt = $this->get_ptt();

        $value = ( isset($ptt) ? $ptt[$key] : $default );

        return $value;
    }

    private function add_fields() {

        add_settings_field(
                $this->get_field_id(PTB_Post_Type_Template::NAME), __('Template Name', 'ptb'), array($this, $this->get_callback_name(PTB_Post_Type_Template::NAME)), $this->slug_admin_ptt, $this->settings_section, array('label_for' => $this->get_field_id(PTB_Post_Type_Template::NAME))
        );

        add_settings_field(
                $this->get_field_id(PTB_Post_Type_Template::POST_TYPE), __('Template for', 'ptb'), array($this, $this->get_callback_name(PTB_Post_Type_Template::POST_TYPE)), $this->slug_admin_ptt, $this->settings_section, array('label_for' => $this->get_field_id(PTB_Post_Type_Template::POST_TYPE))
        );
    }

    public function ptt_name_cb() {

        $value = $this->get_edit_value(PTB_Post_Type_Template::NAME, '');

        printf(
                '<input required="required" type="text" class="regular-text" id="%1$s" name="%2$s[%1$s]" value="%3$s" />', esc_attr($this->get_field_id(PTB_Post_Type_Template::NAME)), $this->options_key, esc_attr($value)
        );
    }

    public function ptt_post_type_cb() {
        $post_create = isset($_REQUEST['slug']) && $_REQUEST['slug'] && isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == admin_url('admin.php?page=ptb-cpt&action=add'); //from page "post type add"
        $slug = $post_create ? esc_attr($_REQUEST['slug']) : '';
        $value = $this->get_edit_value(PTB_Post_Type_Template::POST_TYPE, $slug);
        $post_types = PTB_Utils::get_public_post_types( false );

        $options = array();
        $ptt_id = isset( $_REQUEST['ptb-ptt'] ) ? sanitize_text_field( $_REQUEST['ptb-ptt'] ) : false;
        $themplates = $this->options->get_post_type_templates();
        $themplates_array = array();
        foreach ($themplates as $post_themes) {
			if ( $post_themes->get_name() ) {
				$themplates_array[] = $post_themes->get_post_type();
			}
        }

        if ( $post_types ) {

            foreach ($post_types as $post_type) {
				$post_type = get_post_type_object( $post_type );
                $name = $post_type->name;
                $label = $post_type->labels->singular_name;
                $disable = in_array($name, $themplates_array) ? 'disabled="disabled"' : '';
                $option = sprintf(
                        '<option %4$s value="%1$s" %2$s>%3$s</option>', esc_attr($name), selected($value, $name, false), esc_html($label), $disable
                );
                $options[] = $option;
            }
        }
        $disable = $ptt_id ? 'disabled="disabled"' : 'required="required"';
        printf(
                '<div class="ptb_custom_select"><select %5$s  id="%1$s" name="%2$s[%1$s]"><option value="">%4$s</option>%3$s</select></div>', esc_attr($this->get_field_id(PTB_Post_Type_Template::POST_TYPE)), $this->options_key, implode('', $options), __('Select Post Type', 'ptb'), $disable
        );
        if ( $ptt_id && isset( $themplates[ $ptt_id ] ) ) {
			$post_type = $themplates[ $ptt_id ]->get_post_type();

			if ( ! $this->options->has_custom_post_type( $post_type ) ) {
				echo '<strong class="ptb_small_description">';
				printf( __( 'Note: this post type is not registered by PTB. The template you create here is only used when using the <a href="%s" target="_blank">[ptb] shortcode</a>.', 'ptb' ), 'https://themify.me/docs/post-type-builder-plugin-documentation#ptb-shortcode' );
				echo '</strong>';
			}

			$custom_templates = $themplates[ $ptt_id ]->get_custom_templates();
            printf(
                    '<input type="hidden" value="%3$s" name="%2$s[%1$s]" />', esc_attr($this->get_field_id(PTB_Post_Type_Template::POST_TYPE)), $this->options_key, $value
            );
            ?>

            <div class="ptb_ptt_edit_buttons_wrapper">
				<div>
					<a href="#" title="<?php _e('Archive Template', 'ptb'); ?>"
					   data-template-type="<?php echo PTB_Post_Type_Template::ARCHIVE ?>"
					   id="ptb_ptt_edit_archive" class="ptb_ptt_edit_button ptb_lightbox">
					   <span><?php echo PTB_Utils::get_icon( 'ti-angle-up' ); ?></span>
					   <?php echo $this->options->has_custom_post_type( $post_type ) ? __('Edit Archive Template', 'ptb') : __( 'Edit Default Archive Template', 'ptb' ); ?>
					</a>
				</div>
                <?php
				/* Single templates are only available for PTB post types */
				if ( $this->options->has_custom_post_type( $post_type ) ) : ?>
					<div>
						<a href="#" title="<?php _e('Single Post Template', 'ptb'); ?>"
						   data-template-type="<?php echo PTB_Post_Type_Template::SINGLE ?>" id="ptb_ptt_edit_single" class="ptb_ptt_edit_button ptb_lightbox">
						   <span><?php echo PTB_Utils::get_icon( 'ti-angle-up' ); ?></span>
							<?php _e('Edit Single Post Template', 'ptb'); ?>
						</a>
					</div>
				<?php endif; ?>

				<div class="ptb_custom_templates">
					<?php
					if ( ! empty( $custom_templates ) ) {
						foreach ( $custom_templates as $name => $template ) { ?>
							<div>
								<a href="#"
								   data-template-type="<?php echo esc_attr( PTB_Form_PTT_Custom::$prefix . $name ); ?>" class="ptb_ptt_edit_button ptb_lightbox">
									<?php printf( __( 'Edit %s Template', 'ptb' ), $name ); ?>
								</a>
								<a href="#" class="ptb_ptt_remove" data-template-type="<?php echo esc_attr( PTB_Form_PTT_Custom::$prefix . $name ); ?>"><span><?php echo PTB_Utils::get_icon( 'ti-close' ); ?></span> <?php _e( 'Delete', 'ptb' ); ?></a>
							</div>
						<?php }
					}
					?>
				</div>
				<a id="ptb_new_custom_template" href="#">
					<span><?php echo PTB_Utils::get_icon( 'ti-plus' ); ?></span>
					<?php _e( 'New Archive Template', 'ptb' ); ?>
				</a>

                <div>
                    <?php do_action('ptb_templates_menu') ?>
                </div>
            </div>
            <?php

			/* include used icons into the page */
			PTB_Utils::get_icon( 'ti-more' );
			PTB_Utils::get_icon( 'ti-layout-column3' );
			PTB_Utils::get_icon( 'ti-close' );
        }
    }

    /**
     * @param array $input The inputs array of custom taxonomy
     *
     * @since    1.0.0
     */
    public function process_options($input) {

        if (!isset($input[$this->get_field_id(PTB_Post_Type_Template::NAME)])) {

            return;
        }

        $ptt = new PTB_Post_Type_Template($this->plugin_name, $this->version);

        $this->extract_data($ptt, $input);

        $this->options->update_post_type_template($ptt);
    }

    /**
     * Collects data from edit form inputs
     *
     * @since 1.0.0
     *
     * @param PTB_Post_Type_Template $ptt
     * @param array $input
     */
    private function extract_data($ptt, $input) {

        $id = sanitize_text_field($input[$this->get_field_id(PTB_Post_Type_Template::ID)]);
        $name = sanitize_text_field($input[$this->get_field_id(PTB_Post_Type_Template::NAME)]);
        $post_type = sanitize_text_field($input[$this->get_field_id(PTB_Post_Type_Template::POST_TYPE)]);

        $ptt->set_id($id);
        $ptt->set_name($name);
        $ptt->set_post_type($post_type);

        $ptt_options = $this->options->get_post_type_template($id);

        if (!empty($ptt_options)) {

            $tmp_ptt_obj = new PTB_Post_Type_Template($this->plugin_name, $this->version);
            $tmp_ptt_obj->deserialize($ptt_options);

            $ptt->set_archive($tmp_ptt_obj->get_archive());
            $ptt->set_single($tmp_ptt_obj->get_single());
            $ptt->set_custom_templates( $tmp_ptt_obj->get_custom_templates() );
        }
    }

}