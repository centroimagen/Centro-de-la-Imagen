<?php
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    PTB
 * @subpackage PTB/admin
 * @author     Themify <ptb@themify.me>
 */
class PTB_Admin {

    /**
     * The options management class of the the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PTB_Options $options Manipulates with plugin options
     */
    protected $options;
    protected $cpt_form;
    protected $ctx_form;
    protected $ptt_form;
    protected $ie_form;
    protected $options_form;
    protected $ptt_archive_form;
    protected $ptt_single_form;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;
    private $slug_admin_cpt;
    private $slug_admin_ctx;
    private $slug_admin_ptt;
    private $slug_admin_ie;
    private $slug_admin_ptt_archive;
    private $slug_admin_ptt_single;
    private $slug_admin_settings;
    private $settings_key;
    private $settings_section;
    private $columns = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options
     *
     * @private param string $plugin_name The name of this plugin.
     * @private param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version, $options) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->settings_section = $this->plugin_name . '_main_section';

        $this->slug_admin_cpt = $this->plugin_name . '-cpt';
        $this->slug_admin_ctx = $this->plugin_name . '-ctx';
        $this->slug_admin_ptt = $this->plugin_name . '-ptt';
        $this->slug_admin_ie = $this->plugin_name . '-ie';
        $this->slug_admin_ptt_archive = $this->plugin_name . '-ptt-archive';
        $this->slug_admin_ptt_single = $this->plugin_name . '-ptt-single';
        $this->slug_admin_settings = $this->plugin_name . '-settings';

        $this->options = $options;

        $this->settings_key = $this->options->get_settings_key();

        $this->cpt_form = new PTB_Form_CPT($this->plugin_name, $this->version, $this->options);
        $this->ctx_form = new PTB_Form_CTX($this->plugin_name, $this->version, $this->options);
        $this->ptt_form = new PTB_Form_PTT($this->plugin_name, $this->version, $this->options);
        $this->ie_form = new PTB_Form_ImportExport( $this->plugin_name, $this->version, $this->options );
        $this->options_form = new PTB_Form_Settings( $this->plugin_name, $this->version, $this->options );
		add_action( 'load-post-type-builder_page_ptb-troubleshoot', [ __CLASS__, 'flush_permalinks' ] );
		add_action( 'admin_init', [ $this, 'init' ], 999 );
    }

	public function init() {
		$post_types = $this->options->get_custom_post_types();
		foreach ( $post_types as $key => $post_type ) {
			if ( $post_type->ad_show_in_menu ) {
				$label = PTB_Utils::get_label( $post_type->singular_label, $key );
				add_submenu_page( 'edit.php?post_type=' . $key, null, sprintf( __( 'Edit: %s', 'ptb' ), $label ), 'manage_options', $post_type->get_edit_url(), null );
			}
			add_filter( 'manage_edit-' . $key . '_columns', array( $this, 'ptb_colums' ) );
		}
		add_filter( 'posts_clauses', array( $this, 'ptb_sort_colums' ), 11, 2 );
	}

    /**
     * Get the current custom post type id or null
     *
     * @since 1.0.0
     *
     * @return string
     */
    public static function get_current_custom_post_type_id() {
        return isset($_REQUEST['action']) && 'edit' === $_REQUEST['action']?$_REQUEST['post_type']:'';
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     * This function called from PTB main class and registered with 'admin_menu' hook.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        add_menu_page(
                __('Post Type Builder', 'ptb'), __('Post Type Builder', 'ptb'), 'manage_options', $this->slug_admin_cpt, array($this, 'display_custom_cpt_ctx'), 'dashicons-welcome-write-blog', '58.896427'
        );
        $menu = array($this->slug_admin_cpt => array(
                __('Post Types', 'ptb'),
                __('Post Types', 'ptb'),
                'manage_options'
            ),
            $this->slug_admin_ctx => array(
                __('Taxonomies', 'ptb'),
                __('Taxonomies', 'ptb'),
                'manage_options',
                array($this, 'display_custom_cpt_ctx')
            ),
            $this->slug_admin_ptt => array(
                __('Templates', 'ptb'),
                __('Templates', 'ptb'),
                'manage_options',
                array($this, 'display_templates')
            ),
            $this->slug_admin_ie => array(
                __('Import/Export', 'ptb'),
                __('Import/Export', 'ptb'),
                'manage_options',
                array($this, 'display_import_export')
            ),
            $this->slug_admin_settings => array(
                __('PTB Settings', 'ptb'),
                __('Settings', 'ptb'),
                'manage_options',
                array( $this, 'display_settings' )
            ),
        );
        $menu = apply_filters('ptb_admin_menu', $menu);
        foreach ($menu as $slug => $options) {
            add_submenu_page( $this->slug_admin_cpt, $options[0], $options[1], $options[2], $slug, isset($options[3]) ? $options[3] : false );
        }
		add_submenu_page( $this->slug_admin_cpt, __('About', 'ptb'), __('About', 'ptb'), 'manage_options', 'ptb-about', array( $this, 'display_about' ) );
		add_submenu_page( $this->slug_admin_cpt, __('Post Type Builder Troubleshoot', 'ptb'), __('Troubleshoot', 'ptb'), 'manage_options', 'ptb-troubleshoot', array( $this, 'display_troubleshoot' ) );
    }

    /**
     * Register the plugin settings and settings section.
     * This function called from PTB main class and registered with 'admin_init' hook.
     *
     * @since    1.0.0
     */
    public function register_plugin_settings() {
        register_setting(
                $this->settings_key, $this->settings_key, array($this, 'sanitize_options_cb')
        );

        if (!get_transient('ptb_welcome_page')) {
            return;
        }
        delete_transient('ptb_welcome_page');

        if (!is_network_admin() && !isset($_GET['activate-multi'])) {
            wp_safe_redirect(add_query_arg(array('page' => 'ptb-about'), admin_url('admin.php')));
        }
    }

    /**
     * Callback function for settings registration
     *
     * @since 1.0.0
     *
     * @param array $input the inputs array of settings page
     *
     * @return mixed
     */
    public function sanitize_options_cb($input) {

        if (isset($input['ptb_cpt_builtin_post_type'])) {
			$this->cpt_form->process_options_for_builtin_cpt( $input );
        } else if (isset($input['ptb_cpt_id'])) {
            $this->cpt_form->process_options($input);
        } elseif (isset($input['ptb_ctx_id'])) {

            $this->ctx_form->process_options($input);
        } elseif (isset($input['ptb_ptt_archive'])) {

            $this->ptt_archive_form->process_options($input);
        } elseif (isset($input['ptb_ptt_single'])) {

            $this->ptt_single_form->process_options($input);
        } elseif (isset($input['ptb_ptt_id'])) {

            $this->ptt_form->process_options($input);
        } elseif (isset($input['ptb_ie_export'])) {

            $this->ie_form->export($input);
        } elseif (isset($input['ptb_ie_import'])) {

            $this->ie_form->import($input);
        } elseif (isset($input['ptb_css'])) {

            $this->options_form->process_options($input);
        }
        return $this->options->get_options();
    }

    /**
     * Render the custom post types/custom taxonomies page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_custom_cpt_ctx() {
        $page = sanitize_text_field($_REQUEST['page']);
        if($page!==$this->plugin_name.'-cpt' && $page!==$this->plugin_name.'-ctx'){
            return;
        }
        $type = $page===$this->plugin_name.'-cpt'?'cpt':'ctx';
        if($type==='cpt'){
            $this->cpt_form->add_settings_fields($this->slug_admin_cpt);
        }
        else{
            $this->ctx_form->add_settings_fields($this->slug_admin_ctx);
        }
        if (!empty($_REQUEST['action'])) {
            $action = sanitize_text_field($_REQUEST['action']);
            if ( !empty($_GET['slug']) && ('delete' === $action || 'copy' === $action)) {
                $slug = sanitize_key($_GET['slug']);
                $message = '';
                if ('copy' === $action && !empty($_GET['old_slug'])){
                    $message = sprintf(
                            __('Custom %1$s "%2$s" has been copied from "%3$s"', 'ptb'),($type=='cpt'?__('post type','ptb'):__('taxonomy','ptb')), $slug, sanitize_key($_GET['old_slug'])
                    );
                }
                elseif('delete' === $action){
                    $message = sprintf(
                        __('Custom %1$s "%2$s" successfully removed.', 'ptb'),($type=='cpt'?__('post type','ptb'):__('taxonomy','ptb')), $slug
                    );  
                } 
                if($message){
                    add_settings_error($this->plugin_name . '_notices', '', $message, 'updated');
                }
                include_once( 'partials/ptb-admin-display-list-'.$type.'.php' );
            } elseif ('edit' === $action || 'add' === $action) {
				include_once( 'partials/ptb-admin-display-edit-'.$type.'.php' );
            } else {
                include_once( 'partials/ptb-admin-display-list-'.$type.'.php' );
            }
        } else {

            include_once( 'partials/ptb-admin-display-list-'.$type.'.php' );
        }
    }


    /**
     * Render the custom templates page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_templates() {

        $this->ptt_form->add_settings_fields($this->slug_admin_ptt);

        if (isset($_GET['action'])) {

            $action = sanitize_text_field($_REQUEST['action']);

            if ('delete' === $action) {


                if (isset($_REQUEST['ptb-ptt'])) {

                    $id = $_REQUEST['ptb-ptt'];
                    $template = $this->options->get_post_type_template($id);
                    if (isset($template) && isset($template['name'])) {
                        check_admin_referer('ptt_nonce', 'nonce');
                        $message = sprintf(
                                __('%1$s template successfully removed.', 'ptb'), $template['name']
                        );
                        $this->options->remove_post_type_template($id);
                        $this->options->update();
                        add_settings_error($this->plugin_name . '_notices', '', $message, 'updated');
                    }
                    include_once( 'partials/ptb-admin-display-list-ptt.php' );
                }
            } elseif ('edit' === $action) {

                if (isset($_REQUEST['template'])) {

                    if ('archive' === $_REQUEST['template']) {

                        $this->ptt_archive_form->add_settings_fields($this->slug_admin_ptt_archive);

                        include_once( 'partials/ptb-admin-display-edit-ptt-archive.php' );
                    } elseif ('single' === $_REQUEST['template']) {

                        $this->ptt_single_form->add_settings_fields($this->slug_admin_ptt_single);

                        include_once( 'partials/ptb-admin-display-edit-ptt-single.php' );
                    }
                } else {

                    include_once( 'partials/ptb-admin-display-edit-ptt.php' );
                }
                $this->options->add_template_styles();
            } elseif ('add' === $action) {

                if (!isset($_REQUEST['settings-updated'])) {

                    include_once( 'partials/ptb-admin-display-edit-ptt.php' );
                } else {

                    include_once( 'partials/ptb-admin-display-list-ptt.php' );
                }
            }
        } else {
            include_once( 'partials/ptb-admin-display-list-ptt.php' );
        }
    }

    /**
     * Renders the Import/Export page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_import_export() {

        $this->ie_form->add_settings_fields($this->slug_admin_ie);
        ?>
        <div class="wrap">
            <h2><?php _e('Import/Export', 'ptb') ?></h2>

            <div class="ptb_notices">
                <?php settings_errors($this->plugin_name . '_notices'); ?>
            </div>
            <?php do_settings_sections($this->plugin_name . '-ie') ?>
        </div>
        <?php
    }

    /**
     * Renders PTB Settings page
     *
     * @since    1.0.0
     */
    public function display_settings() {

        $this->options_form->add_settings_fields( $this->slug_admin_settings );
        ?>
        <div class="wrap">

            <h2><?php _e( 'Post Type Builder Settings', 'ptb' ) ?></h2>

            <div class="ptb_notices">
                <?php settings_errors($this->plugin_name . '_notices'); ?>
            </div>

			<form  method="post" action="options.php" enctype="multipart/form-data" id="<?php echo $this->slug_admin_settings ?>">
				<div class="ptb-tabs">
					<nav class="nav-tab-wrapper">
						<a href="#ptb-settings" class="nav-tab nav-tab-active"><?php _e( 'Settings', 'ptb' ); ?></a>
						<a href="#ptb-customcss" class="nav-tab"><?php _e( 'Custom CSS', 'ptb' ); ?></a>
					</nav>
					<div id="ptb-settings">
						<?php do_settings_sections( $this->slug_admin_settings ) ?>
					</div>
					<div id="ptb-customcss" style="display: none">
						<?php do_settings_sections( 'ptb-customcss' ) ?>
					</div>
				</div><!-- .ptb-tabs -->
				<?php settings_fields( 'ptb_plugin_options' ); ?>
				<?php submit_button(__('Save', 'ptb')); ?>
			</form>

        </div>
        <?php
    }

    /**
     * Renders About page.
     *
     * @since    1.0.0
     */
    public function display_about() {
		include( 'partials/about.php' );
    }

	public static function flush_permalinks() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'ptb-flush' && wp_verify_nonce( $_GET['nonce'], 'ptb_flush_nonce' ) ) {
            add_settings_error( 'ptb-troubleshoot', '', __('Permalinks have been updated'), 'updated' );
            flush_rewrite_rules( true );
        }
	}

    public function display_troubleshoot() {
		include 'partials/troubleshoot.php';
    }
    
    
    /**
     * Post type options validation function.
     * Checks is the post type name allowed to use?
     * Used from dashboards trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_post_type_name_validate() {

        if (wp_verify_nonce($_REQUEST['nonce'], 'ajax-ptb-cpt-nonce')) {

            $cpt_slug = sanitize_text_field($_POST['slug']);
            $reserved_by_theme = array('menu', 'section');
            $message = '';

            if (post_type_exists($cpt_slug)) {

                $message = sprintf(__('Post type "%s" exists', 'ptb'), $cpt_slug);
                
            } elseif (strlen($cpt_slug) > 20) {

                $message = __('Post type name can\'t be longer than 20 symbols', 'ptb');
                
            } elseif (strlen($cpt_slug) < 1) {

                $message = __('Post type name can\'t be empty', 'ptb');
                
            } elseif (is_plugin_active('themify-builder/themify-builder.php') && in_array($cpt_slug, $reserved_by_theme)) {

                $message = __('Post type name is reserved by themify-builder, please type another name', 'ptb');
                
            } elseif (preg_match("/[^a-z0-9_-]/", $cpt_slug, $match)) {

                $message = __('Post type name should only contain lowercase letters and the underscore or dash character', 'ptb');
            }
            elseif(in_array($cpt_slug, PTB_Utils::get_reserved_terms())){
               $message = __('Post type name is reserved by <a href="//codex.wordpress.org/Reserved_Terms" target="_blank">WP</a>, please type another name', 'ptb');
            }

            die($message);
        }
    }
    
    
    /**
     * Taxonomy type options validation function.
     * Checks is the taxonomy name allowed to use?
     * Used from dashboards trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_taxonomy_name_validate() {

        if (wp_verify_nonce($_REQUEST['nonce'], 'ajax-ptb-ctx-nonce')) {

            $ctx_slug = sanitize_text_field($_POST['slug']);
            $message = '';
          
            if (taxonomy_exists($ctx_slug)) {

                $message = sprintf(__('Taxonomy "%s" exists', 'ptb'), $ctx_slug);
            }
            elseif (strlen($ctx_slug) > 32) {

                $message = __('Taxonomy name can\'t be longer than 32 symbols', 'ptb');
            }
            elseif (strlen($ctx_slug) < 1) {

                $message = __('Taxonomy name can\'t be empty', 'ptb');
            }
            elseif (preg_match("/[^a-z0-9_]/", $ctx_slug, $match)) {

                $message = __('Taxonomy name should only contain lowercase letters and the underscore character', 'ptb');
            }
            elseif(in_array($ctx_slug, PTB_Utils::get_reserved_terms())){
               $message = __('Taxonomy name is reserved by <a href="//codex.wordpress.org/Reserved_Terms" target="_blank">WP</a>, please type another name', 'ptb');
            }
            die($message);
        }
    }

    /**
     * Register the JavaScript/Stylesheets for the dashboard.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook_suffix ) {
        $screen = get_current_screen();
        $plugin_dir = PTB::$uri . 'admin/';
		PTB_CMB_Base::register_common_assets();
        wp_register_style( $this->plugin_name . '-admin', PTB_Utils::enque_min( $plugin_dir . 'css/ptb-admin.css'), array( $this->plugin_name . '-common' ), $this->version, 'all' );

		wp_register_script( $this->plugin_name . '-widget-js', PTB_Utils::enque_min( $plugin_dir . 'js/ptb-widget.js' ), array( 'jquery' ), PTB::get_plugin_name(), true );
		wp_register_style( $this->plugin_name . '-widget', PTB_Utils::enque_min( $plugin_dir . 'css/ptb-widget.css' ), array(), $this->version, 'all' );
		if ( $hook_suffix === 'widgets.php' ) {
			wp_enqueue_style( $this->plugin_name . '-widget' );
			wp_enqueue_script( $this->plugin_name . '-widget-js' );
		}

        if ($screen->id != 'customize') {
            $id = __('Post Type Builder','ptb');//multilanguage screen id
            $id = sanitize_title($id);
            $screens = array( $id . '_page_ptb-ptt', 'toplevel_page_ptb-cpt', $id . '_page_ptb-ctx', $id. '_page_ptb-ie', $id . '_page_ptb-settings' );

			/* load PTB scripts only if there are options to display for the current post type or taxonomy term */
            if ( $screen->base === 'post' ) {
                $post_type = $screen->post_type;
				if ( ! empty( $this->options->get_cpt_cmb_options( $post_type ) ) ) {
					$screens[] = $post_type;
				}
            } else if ( $screen->base === 'edit-tags' || $screen->base === 'term' ) {
				$taxonomy = $screen->taxonomy;
				if ( ! empty( $this->options->get_ctx_cmb_options( $taxonomy ) ) ) {
					$screens[] = 'edit-' . $taxonomy;
				}
			}

            $screens = apply_filters('ptb_screens', $screens, $screen);
            wp_register_script( $this->plugin_name . '-admin', PTB_Utils::enque_min($plugin_dir . 'js/ptb-admin.js'), array( 'jquery', $this->plugin_name . '-common' ), $this->version, false );
			
			if (in_array($screen->id, $screens)) {
                unset($screen, $screens);
                if (!wp_style_is('themify-colorpicker')) {
                    wp_enqueue_style('themify-colorpicker', PTB_Utils::enque_min($plugin_dir . 'css/jquery/jquery.minicolors.css'), array(), $this->version, 'all');
                }

                wp_enqueue_media();
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-effects-core');
                wp_enqueue_script('jquery-effects-blind');
                wp_enqueue_script('themify-colorpicker-js', $plugin_dir . 'js/jquery/jquery.minicolors.min.js', array('jquery'), $this->version, false);
                $translation_array = array(
                    'post_type_delete' => __('All posts and template will be deleted. Do you want to delete this?', 'ptb'),
                    'remove_posts'=>__('Remove posts as well','ptb'),
                    'unregister_posts'=>__('Unregister the post type','ptb'),
                    'remove_terms'=>__('Remove terms as well','ptb'),
                    'taxonomy_delete' => __('Unregister the taxonomy', 'ptb'),
                    'template_delete' => __('Do you want to delete this?', 'ptb'),
                    'module_delete' => __('Do you want to delete this module?', 'ptb'),
                    'import_samples' => __('Import sample posts too', 'ptb'),
                    'import' => __('Import', 'ptb'),
                    'confirm_import'=>__('This operation will override the post type %s, this includes the templates. Should we continue?','ptb'),
                    'lng' => PTB_Utils::get_default_language_code(),
                    'import_pre'=>array(
                        'loading'=>__('Start Loading','ptb'),
                        'cpt'=>__('Loading Custom Post Type: %s','ptb'),
                        'start_samples'=>__('Start Loading Samples','ptb'),
                        'samples'=>__('Loading Samples: %s','ptb'),
                        'start_images'=>__('Start Loading Images','ptb'),
                        'images'=>__('Loading Images: %s','ptb'),
                        'saving'=>__('Saving...','ptb'),
                        'finish' => __( 'Import is completed', 'ptb' )
                    ),
					'edit_template' => __( 'Edit %s Template', 'ptb' ),
					'remove_template' => __( 'Remove Template', 'ptb' ),
					'new_template' => __( 'Enter the template name', 'ptb' ),
					'confirm_remove_template' => __( 'Delete Template?', 'ptb' ),
					'empty_message' => PTB_Utils::is_multilingual() ? __( '%s and its translation can\'t be empty.', 'themify' ) : __( '%s can\'t be empty.', 'themify' ),
                );
                wp_localize_script( $this->plugin_name . '-admin', 'ptb_js_admin', $translation_array );
                wp_enqueue_script( $this->plugin_name . '-admin' );
				wp_enqueue_style( $this->plugin_name . '-admin' );

				wp_enqueue_style($this->plugin_name, PTB_Utils::enque_min($plugin_dir . 'css/ptb-extra.css'), array('ptb-admin'), $this->version, 'all');
				wp_enqueue_style('themify-datetimepicker', $plugin_dir . 'css/jquery-ui-timepicker.min.css', array(), $this->version, 'all');

				wp_enqueue_script($this->plugin_name . '-video', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-video.js'), array('ptb-admin'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-audio', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-audio.js'), array('ptb-admin'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-slider', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-slider.js'), array('ptb-admin'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-gallery', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-gallery.js'), array('ptb-admin', 'jquery-ui-sortable'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-rating', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-rating.js'), array('ptb-admin'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-progress-bar', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-progress-bar.js'), array('ptb-admin'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-icon', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-icon.js'), array('ptb-admin'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-file', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-file.js'), array('ptb-admin'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-accordion', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-accordion.js'), array('ptb-admin'), $this->version, false);
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_script('themify-datetimepicker', $plugin_dir . 'js/jquery-ui-timepicker.min.js', array('jquery-ui-datepicker'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-date', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-event-date.js'), array('themify-datetimepicker', 'ptb-admin'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-map', PTB_Utils::enque_min($plugin_dir . 'js/ptb-extra-map.js'), array(), $this->version, true);
				wp_localize_script($this->plugin_name.'-map', 'ptb', array(
					'lng'=>PTB_Utils::get_current_language_code(),
					'map_key' => $this->options->get_google_map_key(),
					'i18n' => [
						'currentText' => _x( 'Now', 'PTB date picker currentText', 'ptb' ),
						'closeText' => _x( 'Done', 'PTB date picker closeText', 'ptb' ),
						'timeText' => _x( 'Time', 'PTB date picker timeText', 'ptb' ),
					],
				));

				do_action( 'ptb_admin_enqueue_scripts' );
            }
        }
    }

	/**
	 * Load assets for the TinyMCE shortcode generator
	 *
	 * @since 1.7.7
	 */
	public function wp_enqueue_editor() {
		wp_enqueue_style( $this->plugin_name . 'tinymce', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/tinymce.css', array(), $this->version, 'all' );
	}

    public function add_ptb_shortcode() {

        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        //shortcodes
        if ('true' == get_user_option('rich_editing')) {
            add_filter('mce_external_plugins', array($this, 'ptb_add_shortcodes_buttons'));
            add_filter('mce_buttons', array($this, 'ptb_register_button'));
            if (is_admin()) {
                add_action('admin_footer', array($this, 'ptb_get_shortcodes'));
            } else {
                add_action('wp_footer', array($this, 'ptb_get_shortcodes'));
            }
        }
    }

    /**
     * Add shortcode JS to the page
     *
     * @return HTML
     */
    public function ptb_get_shortcodes() {
        $post_types = PTB_Utils::get_public_post_types( false );
		$ptb_data = []; /* data for [ptb] shortcode generator */
	    $ptb_taxonomy_data = []; /* data for [ptb_taxonomy] shortcode generator */
		$ptb_field_data = []; /* data for [ptb_field] shortcode generator */
        foreach ( $post_types as $post_type ) {
			$fields = $this->options->get_cpt_cmb_options( $post_type );
			/* shortcode generator available for all PTB post types and the ones that have PTB custom fields */
			if ( $this->options->has_custom_post_type( $post_type ) || ! empty( $fields ) ) {
				$post_type_object = get_post_type_object( $post_type );
                $ptb_data[] = [ 'type' => $post_type, 'name' => $post_type_object->labels->name ];

				$custom_fields = [];
				foreach ( $fields as $meta_key => $field ) {
					$custom_fields[ $meta_key ] = PTB_Utils::get_label( $field['name'], $meta_key );
				}
				$ptb_field_data[ $post_type ] = [
					'name' => get_post_type_object( $post_type )->labels->name,
					'fields' => $custom_fields,
				];
			}

			/* [ptb_taxonomy] shortcode only available for PTB post types */
			if ( $this->options->has_custom_post_type( $post_type ) && ! empty( $post_type_object->taxonomies ) ) {
				$ptb_taxonomy_data[] = [ 'type' => $post_type, 'name' => $post_type_object->labels->name ];
			}
        }

        if ( ! empty( $ptb_taxonomy_data ) ) {
	        $name = __('PTB Taxonomy', 'ptb');
	        $ptb_taxonomy_data = array( 'type' => 'ptb_taxonomy', 'name' => $name, 'submenu' => $ptb_taxonomy_data );
	        $ptb_data[] = $ptb_taxonomy_data;
        }

        $ptb_data = apply_filters( 'ptb_shorcode_template_menu', $ptb_data );
        if ( ! empty($ptb_data) ) {
            echo '<script type="text/javascript">
					var ptb_shortcodes_button = new Array(),
						ptb_fields = \''. json_encode( $ptb_field_data, true ) .'\'
						ptb_assets = "'.plugin_dir_url(__FILE__).'";';
            foreach ( $ptb_data as $k => $post_themes ) {
				echo "ptb_shortcodes_button['$k']=";
				if ( is_array($post_themes) ) {
					echo json_encode($post_themes).";";
				} else {
					echo $post_themes.";";
				}
            }
            echo '</script>';
        }
    }

    /**
     * Add new Javascript to the plugin scrippt array
     *
     * @param  Array $plugin_array - Array of scripts
     *
     * @return Array
     */
    public function ptb_add_shortcodes_buttons($plugin_array) {
      
        $plugin_array[$this->plugin_name] = plugin_dir_url(__FILE__) . 'js/ptb-shortcode.js';
        $plugin_array[$this->plugin_name . 'Fields'] = plugin_dir_url(__FILE__) . 'js/ptb-shortcode.js';

        return $plugin_array;
    }

    /**
     * Add new button to the buttons array
     *
     * @param  Array $buttons - Array of buttons
     *
     * @return Array
     */
    public function ptb_register_button($buttons) {
        $buttons[] = $this->plugin_name;
        $buttons[] = $this->plugin_name . 'Fields';
        return $buttons;
    }

    /**
     * Set post type colums
     *
     * @since 1.0.0
     */
    public function ptb_colums($columns) {
        foreach ($columns as $c => $t) {
            if (strpos($c, 'taxonomy-') !== false) {
                $this->columns[$c] = $t;
            }
        }
        if (!empty($this->columns)) {
            add_action('restrict_manage_posts', array($this, 'add_filters'));
        }
        return $columns;
    }

    public function add_filters() {
        if (!empty($this->columns)) {

            $args = array('hide_empty' => 1,
                'hierarchical' => 1,
                'hide_if_empty' => 1,
                'show_count' => 1,
                'value_field' => 'slug'
            );
            foreach ($this->columns as $col => $tax_name) {
                $tax_slug = str_replace('taxonomy-', '', $col);
                $slug = isset($_GET[$tax_slug]) && $_GET[$tax_slug] != -1 ? sanitize_key($_GET[$tax_slug]) : false;
                $args['taxonomy'] = $tax_slug;
                $args['show_option_all'] = sprintf(__('Show All %s', 'ptb'), $tax_name);
                $args['name'] = $args['id'] = $tax_slug;
                $args['selected'] = $slug;
                wp_dropdown_categories($args);
            }
        }
    }

    public function add_sort($columns) {
        foreach ($this->columns as $col => $name) {
            $columns[$col] = $col;
        }
        return array_merge($this->columns, $columns);
    }

    public function ptb_sort_colums($clauses, $wp_query) {
        global $wpdb;
        if ( !empty($wp_query->query['orderby']) ) {
            $orderby = is_array($wp_query->query['orderby']) ? implode(' ', $wp_query->query['orderby']) : $wp_query->query['orderby'];
            if ( strpos($orderby, 'taxonomy-') !== false ) {
                $slug = str_replace('taxonomy-', '', $orderby);
                if ($this->options->has_custom_taxonomy($slug)) {
                    if (strpos($clauses['join'], 'JOIN ' . $wpdb->term_relationships . ' ON (wp_posts.ID = ' . $wpdb->term_relationships . '.object_id)') === false) {
                        $clauses['join'].="LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id ";
                        $clauses['where'] .= "AND (taxonomy = '$slug' OR taxonomy IS NULL)";
                    }
                    $clauses['join'].="LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
                                      LEFT OUTER JOIN {$wpdb->terms} USING (term_id)";
                    $clauses['groupby'] = "object_id";
                    $clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC)";
                    $clauses['orderby'] .= ( 'ASC' == strtoupper($wp_query->get('order')) ) ? 'ASC' : 'DESC';
                }
            }
        }

        return $clauses;
    }
    
    public function remove_disalog(){
        if (!empty($_REQUEST['type']) && !empty($_REQUEST['slug']) && !empty($_REQUEST['nonce']) && check_ajax_referer('ptb_remove_dialog','nonce', false)) {
            $slug = sanitize_key($_REQUEST['slug']); 
            $include = $_REQUEST['type']==='cpt'?$this->options->has_custom_post_type($slug): $this->options->has_custom_taxonomy($slug);
            if($include){
                include_once( 'partials/ptb-remove-dialog.php' );
            }
        }
        wp_die();
    }
    
    /**
     * Ajax handler of remove/unregister action
     *
     * @since 1.0.0
     */
    public function ptb_remove() {

        if (!empty($_POST['slug']) && !empty($_POST['type']) && check_ajax_referer('ptb_remove_'.$_POST['slug'],'nonce', false)) {
            $result = false;
            $type = sanitize_text_field($_POST['type']);
            $remove_all = !empty($_POST['remove']) && $_POST['remove']==='1';
            $slug = sanitize_key($_POST['slug']);
            $page = $this->plugin_name.'-'.$type;
            if($type==='cpt'){
                $result = $remove_all?$this->options->remove_custom_post_type($slug):$this->options->unregister_custom_post_type($slug);
            }
            elseif($type==='ctx'){
                $result = $remove_all?$this->options->remove_custom_taxonomy($slug):$this->options->unregister_custom_taxonomy($slug);
            }
            $data = array();
            if ( $result ) {
				$this->options->update();
                $data['success'] = 1;
                $data['data'] = $remove_all ? add_query_arg(array('slug'=>$slug,'action'=>'delete','page'=>$page),admin_url('admin.php')) : add_query_arg(array('page'=>$page),admin_url('admin.php'));

            } else {
				$data['data'] = __('Something goes wrong, please try again','ptb');
            }
         
            echo wp_json_encode($data);
        }
        wp_die();
    }
    
    /**
     * Ajax handler of register/unregister action
     *
     * @since 1.0.0
     */
    public function ptb_register() {

        if (!empty($_REQUEST['type']) && !empty($_REQUEST['slug']) && !empty($_REQUEST['nonce']) && check_ajax_referer('ptb_register','nonce', false)) {
            $result = false;
            $type = sanitize_text_field($_REQUEST['type']);
            $slug = sanitize_key($_REQUEST['slug']);
            if($type==='cpt'){
                $result = $this->options->is_custom_post_type_registered($slug)?$this->options->unregister_custom_post_type($slug):$this->options->register_custom_post_type($slug);
            }
            elseif($type==='ctx'){
                $result = $this->options->is_custom_taxonomy_registered($slug)?$this->options->unregister_custom_taxonomy($slug):$this->options->register_custom_taxonomy($slug);
            }
            if($result && $this->options->update()){
                echo wp_json_encode(array('data'=>add_query_arg(array('page'=>  $this->plugin_name.'-'.$type),admin_url('admin.php'))));
            }
           
        }
        wp_die();
    }
    
    /**
     * Ajax handler of copyaction
     *
     * @since 1.0.0
     */
    public function ptb_copy() {

        if (!empty($_REQUEST['type']) && !empty($_REQUEST['slug']) && !empty($_REQUEST['nonce']) && check_ajax_referer('ptb_'.$_REQUEST['type'].'_copy','nonce', false)) {
            $type = sanitize_text_field($_REQUEST['type']);
            if($type==='cpt' || $type==='ctx'){
                $slug = $old_slug = sanitize_key($_REQUEST['slug']);
                $i = 1;
                $result = false;
                $data = $type==='cpt'?$this->options->get_custom_post_type($slug):$this->options->get_custom_taxonomy($slug);
                if ($data) {
                    while (true) {
                        $slug = $slug . '-' . $i;
                        if (($type==='cpt' && !$this->options->has_custom_post_type($slug)) || ($type==='ctx' && !$this->options->has_custom_taxonomy($slug))) {
                            break;
                        }
                        $slug = $old_slug;
                        $i++;
                    }
                    $data->slug = $data->id = $slug;
                    if($type==='cpt'){
                        $this->options->add_custom_post_type($data);
                    }
                    else{
                        $this->options->add_custom_taxonomy($data);
                    }
                    $result = true;
                }
                if($result && $this->options->update()){
                    echo wp_json_encode(array('data'=>add_query_arg(array('slug'=>$slug,'action'=>'copy','page'=>'ptb-'.$type,'old_slug'=>$old_slug),admin_url('admin.php'))));
                }
            }
        }
        wp_die();
    }
    
    /**
     * Get post type layout themplate by type
     * Used from dashboards trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_theme() {

        if (isset($_REQUEST['template']) && $_REQUEST['template'] && current_user_can('manage_options')) {

            $name = $_REQUEST['template'];
            $class = 'PTB_Form_PTT_';
			/* all "custom" templates use the same base class */
			$class .= substr( $name, 0, strlen( PTB_Form_PTT_Custom::$prefix ) ) === PTB_Form_PTT_Custom::$prefix ? 'Custom' : strtoupper( $name );

            if (class_exists($class) && $_REQUEST[$this->plugin_name . '-ptt']) {

                $themplate_id = $_REQUEST[$this->plugin_name . '-ptt'];
                $them = new $class($this->plugin_name, $this->version, $themplate_id);

                $them->add_settings_section($name);
            }
            wp_die();
        }
    }
    
    

    /**
     * Save post themplate
     * Used from dashboards trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_theme_save() {
        if (check_ajax_referer($this->plugin_name . '_them_ajax', $this->plugin_name . '_nonce', true) && current_user_can('manage_options')) {
            $themplate_id = $_REQUEST[$this->plugin_name . '-' . PTB_Form_PTT_Them::$key];
            $them = new PTB_Form_PTT_Them($this->plugin_name, $this->version, $themplate_id);
            $them->save_themplate($_POST);
        }
    }

	public function ptb_remove_custom_template() {
		$themplate_id = $_REQUEST[$this->plugin_name . '-' . PTB_Form_PTT_Them::$key];
		$them = new PTB_Form_PTT_Them($this->plugin_name, $this->version, $themplate_id);
		$data = $_POST;
		$data[ $this->plugin_name . '_layout' ] = 0;
		$them->save_themplate( $data );
	}
    
    /**
     * get post_type data
     * Used from shortcode trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_get_post_type() {
        if(!empty($_POST['post_type'])){
            $res = $this->options->get_shortcode_data($_POST['post_type']);
            $res['meta_exists_tooltip'] = __('Show posts, which have not empty %s','ptb');
            $res['meta_like_start_tooltip'] = __('Show posts, which %s starting with your input value','ptb');
            $res['meta_like_end_tooltip'] = __('Show posts, which %s ending with your input value','ptb');
            if($res){
                echo wp_json_encode($res);
            }
        }
        wp_die();
    }
    
    public function ptb_delete_attachment($id){
        $url = wp_get_attachment_url($id);
    
        $upload_info = wp_upload_dir();
        $src_info = pathinfo($url);
        $ext = $src_info['extension'];
        $upload_dir = $upload_info['basedir'];
        $thumb_name = $src_info['filename'] . '-' ;
            $rel_name = $src_info['basename'];
        $rel_path = str_replace(array($upload_info['baseurl'],$src_info['basename']), array('',''), $url);
        $rel_path = trailingslashit($upload_dir.'/'.trim($rel_path,'/').'/');
        unset($upload_dir,$upload_info,$src_info);
        if ($handle = opendir($rel_path) ){

            while (false !== ($file = readdir($handle))){
              
                if($file!=='.' && $file!=='..' && $rel_name!==$file && strpos($file,$thumb_name)!==false && is_file($rel_path.$file)){
                    
                    if(pathinfo($file,PATHINFO_EXTENSION)===$ext){
                        $name = str_replace($thumb_name,'',$file);
                        $name = explode('x',$name);
                        if(is_numeric($name[0])){
                            wp_delete_file($rel_path.$file);
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
	
	public  function check_addons_compatible(){//check compatible of addons
		if(isset($_GET['page']) && $_GET['page']==='themify-license'){
			return;
		}
		
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins=get_plugins();
		$plugin_root = WP_PLUGIN_DIR;
		$needUpdate=false;
		$hasUpdater=null;
		$updaterUrl=null;
		$_messages=array();
		$ptbV=PTB::get_version();
		foreach($plugins as $k=>&$p){
			if(isset($p['Author']) && $p['Author']==='Themify'){
				$slug=dirname($k);
				if($slug==='themify-updater' || strpos($slug,'themify-ptb-')===0){
					if($slug==='themify-updater'){
						if($hasUpdater===null){
							$hasUpdater=is_plugin_active($k);
							$updaterUrl=$k;
						}
					}
					else{
						if(!isset($p['Compatibility'])){
							$data = get_file_data($plugin_root.'/'.$k, array('v'=>'Compatibility'),false);
							$v=$p['Compatibility']=$data['v'];
							$needUpdate=true;
						}
						else{
							$v=$p['Compatibility'];
						}
						$up = '';
						if($v){
						    $v=explode(',',trim($v));
						}
						if(!$v){
						    $up='addons';
						}
						elseif (version_compare(trim($v[0]), $ptbV, '>') || (!empty($v[1]) && version_compare($ptbV, trim($v[1]), '>='))){
						    $up='plugin';
						}
						if($up!==''){
							if(!isset($_messages[$up])){
								$_messages[$up]=array();
							}
							$_messages[$up][]=$p['Name'];
						}
					}
				}
			}
		}
		if($needUpdate===true){
			wp_cache_set( 'plugins', $plugins, 'plugins' );
		}
		if($hasUpdater===false &&  $updaterUrl!==null && !empty($_GET['ptb-activate-updater'])){
			$hasUpdater=activate_plugins($updaterUrl,add_query_arg(array('page'=>'themify-license','promotion'=>2),admin_url()));
		}
		unset($needUpdate,$plugins);
		if(!empty($_messages)){
			foreach($_messages as $k=>$msg):?>	
				<div class="notice notice-error ptb_compatible_erros ptb_<?php echo $k?>_erros">
					<p><strong><?php echo $k==='addons'?__('The following plugin(s) are not compatible with plugin PTB. Please update your plugins:','ptb'):__('Please update plugin PTB. The current PTB verion is incompatible with the following plugin(s):','ptb');?></strong></p>
					<p><?php echo implode(', ',$msg); ?></p>
					<p>
					<?php if($hasUpdater===true):?>
						<a role="button" class="button button-primary" href="<?php echo add_query_arg(array('page'=>'themify-license','promotion'=>2),admin_url())?>"><?php _e('Update them','ptb')?></a>
					<?php elseif($hasUpdater===false):?>
						<?php printf(__('%s','ptb'),'<a role="button" class="button" href="'.add_query_arg(array('ptb-activate-updater'=>1)).'">'.__('Activate Themify Updater','ptb').'</a>')?></a>	
					<?php else:?>
						<?php printf(__('Install %s plugin to auto update them.','ptb'),'<a href="'.add_query_arg(array('page'=>'themify-install-plugins'),admin_url('admin.php')).'" target="_blank">'.__('Themify Updater','ptb').'</a>')?></a>
					<?php endif;?>
					</p>
				</div>
			<?php
			endforeach;
		}
	}

	/**
	 * Setup the display of meta fields in admin columns
	 * Hooked to current_screen
	 */
	public function admin_columns() {
		$screen = get_current_screen();
		if ( $screen->base === 'edit' ) {
			$post_type = $screen->post_type;
			add_filter( "manage_{$post_type}_posts_columns", [ $this, 'manage_posts_columns' ] );
			add_action( "manage_{$post_type}_posts_custom_column", [ $this, 'manage_posts_custom_columns' ], 10, 2 );
		}
	}

	public function manage_posts_columns( $columns ) {
		$post_type = get_current_screen()->post_type;
		$fields = $this->options->get_cpt_cmb_options( $post_type );
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $id => $field ) {
				if ( isset( $field['admin_column'] ) && $field['admin_column'] === true ) {
					$columns[ '__ptb_' . $id ] = PTB_Utils::get_label( $field['name'], $id );
				}
			}
		}

		return $columns;
	}

	/**
	 * Display the value for a PTB column in admin Edit screens
	 *
	 * @uses ptb_get_field to render the field
	 */
	public function manage_posts_custom_columns( $column, $post_id ) {
		if ( substr( $column, 0, 6 ) === '__ptb_' ) {
			$field_name = substr( $column, 6 );
			$field_def = ptb_get_field_definition( $field_name );
			$field_type = $field_def['type'];
			$classname = "PTB_CMB_{$field_type}";
			if ( method_exists( $classname, 'admin_column_display' ) ) {
				$value = get_post_meta( $post_id, "ptb_{$field_name}", true );
				echo $classname::admin_column_display( $value, $field_def );
			}
		}
	}

	/**
	 * Generate the admin page title for Post Types and Templates screens
	 */
	private function page_title() {
		if ( 'ptb-cpt' === $_GET['page'] && 'edit' === $_REQUEST['action'] ) {
			$post_type = $_GET['post_type'];
			$template = $this->options->get_post_type_template_by_type( $post_type );
			$tabs = array(
				[ __('Edit Post Type', 'ptb'), '#', true ],
			);
			if ( $template ) {
				$tabs[] = [ __('Edit Template', 'ptb'), '?page=ptb-ptt&action=edit&ptb-ptt=' . $template->get_id(), false ];
			}
			$post_type_object = get_post_type_object( $post_type );
			$tabs[] = [ sprintf( __('Add New %s', 'ptb'), $post_type_object->labels->singular_name ), add_query_arg( 'post_type', $post_type, admin_url( 'post-new.php' ) ), false ];
			$tabs[] = [ __('Add New Post Type', 'ptb'), sprintf( '?page=%s&action=%s', $_REQUEST['page'], 'add' ), false ];
		} else if ( 'ptb-cpt' === $_GET['page'] && 'add' === $_REQUEST['action'] ) {
			$tabs = array(
				[ __('Add New Post Type', 'ptb'), '#', true ],
			);
		} else if ( 'ptb-ptt' === $_GET['page'] && 'edit' === $_REQUEST['action'] ) {
			$options = $this->options->get_templates_options();
			$id = sanitize_text_field( $_GET['ptb-ptt'] );
			$tabs = [];
			if ( isset( $options[ $id ] ) ) {
				$tabs[] = [ __('Edit Post Type', 'ptb'), '?page=ptb-cpt&action=edit&post_type=' . $options[ $id ]['post_type'], false ];
			}
			$tabs[] = [ __('Edit Template', 'ptb'), '#', true ];
			$post_type_object = get_post_type_object( $options[ $id ]['post_type'] );
			$tabs[] = [ sprintf( __('Add New %s', 'ptb'), $post_type_object->labels->singular_name ), add_query_arg( 'post_type', $post_type_object->name, admin_url( 'post-new.php' ) ), false ];
			$tabs[] = [ __('Add New Post Type', 'ptb'), add_query_arg( [ 'page' => 'ptb-cpt', 'action' => 'add' ], admin_url( 'admin.php' ) ), false ];
		} else if ( 'ptb-ptt' === $_GET['page'] && 'add' === $_REQUEST['action'] ) {
			$tabs = array(
				[ __('Add New Template', 'ptb'), '#', true ],
			);
		}

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab ) {
			echo '<a href="' . esc_url( $tab[1] ) . '" class="nav-tab' . ( $tab[2] ? ' nav-tab-active' : '' ) . '">' . $tab[0] . '</a>';
		}
		echo '</h2>';
	}

	/**
	 * Uses PTB_Utils::get_icon to load UI icons into page
	 */
	public static function ui_icons() {
		foreach ( [ 'ti-plus', 'ti-more', 'ti-close', 'ti-angle-up', 'ti-layout-column3' ] as $icon ) {
			PTB_Utils::get_icon( $icon );
		}
	}

	public static function get_post_type_edit_url( $post_type ) {
		return admin_url( 'admin.php?page=ptb-cpt&action=edit&post_type=' . $post_type );
	}
}