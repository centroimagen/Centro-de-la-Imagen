<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    public static $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of the plugin.
     */
    public static $version;

    /**
     * Absolute path to the plugin's main directory
     *
     * @var string
     */
    public static $dir;

    /**
     * URL to plugin's main directory
     *
     * @var string
     */
    public static $uri;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the Dashboard and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public static $options = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @return	A single instance of this class.
     */
    public static function get_instance() {
        static $instance = null;
        return null === $instance ? $instance = new self : $instance;
    }

    private function __construct() {
        self::$plugin_name = 'ptb';
        $this->load_dependencies();
        add_action( 'init', array( $this, 'load_compatibility_patches' ) );
        $this->set_locale();
		if ( ! is_admin() ) {
			add_action( 'template_redirect', array( $this, 'load_pluggable' ) );
		}
    }

    public function set_constants($version, $dir, $uri) {
        self::$version = $version;
        self::$dir = $dir;
        self::$uri = $uri;
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - PTB_Loader. Orchestrates the hooks of the plugin.
     * - PTB_i18n. Defines internationalization functionality.
     * - PTB_Admin. Defines all hooks for the dashboard.
     * - PTB_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        $plugindir = plugin_dir_path(dirname(__FILE__));

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once $plugindir . 'includes/class-ptb-i18n.php';
        require_once $plugindir . 'includes/class-ptb-utils.php';
        require_once $plugindir . 'includes/class-ptb-cmb-base.php';
        require_once $plugindir . 'includes/class-ptb-cmb-text.php';
        require_once $plugindir . 'includes/class-ptb-cmb-email.php';
        require_once $plugindir . 'includes/class-ptb-cmb-textarea.php';
        require_once $plugindir . 'includes/class-ptb-cmb-select.php';
        require_once $plugindir . 'includes/class-ptb-cmb-checkbox.php';
        require_once $plugindir . 'includes/class-ptb-cmb-radio-button.php';
        require_once $plugindir . 'includes/class-ptb-cmb-image.php';
        require_once $plugindir . 'includes/class-ptb-cmb-link-button.php';
        require_once $plugindir . 'includes/class-ptb-cmb-number.php';
        require_once $plugindir . 'includes/class-ptb-cmb-map.php';
        require_once $plugindir . 'includes/class-ptb-cmb-video.php';
        require_once $plugindir . 'includes/class-ptb-cmb-audio.php';
        require_once $plugindir . 'includes/class-ptb-cmb-slider.php';
        require_once $plugindir . 'includes/class-ptb-cmb-gallery.php';
        require_once $plugindir . 'includes/class-ptb-cmb-file.php';
        require_once $plugindir . 'includes/class-ptb-cmb-event-date.php';
        require_once $plugindir . 'includes/class-ptb-cmb-rating.php';
        require_once $plugindir . 'includes/class-ptb-cmb-progress-bar.php';
        require_once $plugindir . 'includes/class-ptb-cmb-icon.php';
        require_once $plugindir . 'includes/class-ptb-cmb-telephone.php';
        require_once $plugindir . 'includes/class-ptb-cmb-accordion.php';
        require_once $plugindir . 'includes/class-ptb-cpt.php';
        require_once $plugindir . 'includes/class-ptb-ctx.php';
        require_once $plugindir . 'includes/class-ptb-ptt.php';
        require_once $plugindir . 'includes/class-ptb-options.php';
        require_once $plugindir . 'includes/class-ptb-form-cpt.php';
        require_once $plugindir . 'includes/class-ptb-form-ctx.php';
        require_once $plugindir . 'includes/class-ptb-form-ptt.php';
        require_once $plugindir . 'includes/class-ptb-form-import-export.php';
        require_once $plugindir . 'includes/class-ptb-form-settings.php';
        require_once $plugindir . 'includes/img.php';
        require_once $plugindir . 'includes/ptb-api.php';
        require_once $plugindir . 'includes/class-ptb-template-loader.php';
        require_once $plugindir . 'includes/class-ptb-template-item.php';

        //classes for working with themplates
        require_once $plugindir . 'includes/class-ptb-form-ptt-them.php';
        require_once $plugindir . 'includes/class-ptb-form-ptt-archive.php';
        require_once $plugindir . 'includes/class-ptb-form-ptt-single.php';
        require_once $plugindir . 'includes/class-ptb-form-ptt-custom.php';

        require_once $plugindir . 'includes/class-ptb-list-cpt.php';
        require_once $plugindir . 'includes/class-ptb-list-ctx.php';
        require_once $plugindir . 'includes/class-ptb-list-ptt.php';
        require_once $plugindir . 'includes/widgets/class-ptb-widget-recent-posts.php';
        require_once $plugindir . 'includes/widgets/class-ptb-widget-taxonomies.php';
        /**
         * The class responsible for defining all actions that occur in the Dashboard.
         */
        require_once $plugindir . 'admin/class-ptb-admin.php';
        require_once $plugindir . 'admin/class-ptb-term-meta.php';
        require_once $plugindir . 'admin/class-ptb-term-image.php';

        require_once $plugindir . 'includes/class-ptb-icon-manager.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once $plugindir . 'public/class-ptb-public.php';

        do_action( 'themify_ptb_loaded' );
    }

	/**
	 * Load compatibility patches for third party plugins or themes
	 *
	 * @since 1.5.3
	 */
	function load_compatibility_patches() {
		$dir = PTB::$dir;
		if ( function_exists( 'pdfprnt_print' ) ) {
			include( $dir . 'includes/compatibility/pdf-print.php' );
			PTB_PDF_Print::get_instance();
		}

		if ( function_exists( 'themify_make_lazy' ) ) {
			include( $dir . 'includes/compatibility/themify.php' );
			PTB_Themify_Compat::init();
		}

		/* Yoast SEO */
		if ( function_exists( 'wpseo_init' ) ) {
			include( $dir . 'includes/compatibility/wordpressSeo.php' );
		}

		if ( function_exists( 'WC' ) ) {
			include( $dir . 'includes/compatibility/wc.php' );
		}

		/* compatibility fixes for specific themes */
		$theme = get_template();
		if ( file_exists( $dir . 'includes/compatibility/' . $theme . '.php' ) ) {
			include $dir . 'includes/compatibility/' . $theme . '.php';
			PTB_Theme_Compat::init();
		}
	}

	/**
	 * Pluggable functions. Loaded after the theme and other plugins have initialized.
	 *
	 * @since 1.7.6
	 */
	function load_pluggable() {
		include_once( self::$dir . 'includes/ptb-template-tags.php' );
	}

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the PTB_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new PTB_i18n();
        $plugin_i18n->set_domain(self::get_plugin_name());
        add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public static function get_plugin_name() {
        return self::$plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public static function get_version() {
        return self::$version;
    }

    /**
     * Register all of the hooks related to the dashboard functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new PTB_Admin(self::$plugin_name, self::$version, self::$options);
        add_action('init', array(self::$options, 'ptb_register_custom_taxonomies'));
        add_action('init', array(self::$options, 'ptb_register_custom_post_types'));
        add_action( 'widgets_init', array( self::$options, 'ptb_load_widgets' ) );
        add_action('save_post', array(self::$options, 'save_custom_meta'), 10, 3);
        add_action('delete_attachment',array($plugin_admin,'ptb_delete_attachment'),10,1);
        //Ajax actions registration
        if (PTB_Utils::is_ajax()) {
            add_action('wp_ajax_ptb_ajax_post_type_name_validate', array($plugin_admin, 'ptb_ajax_post_type_name_validate'));
            add_action('wp_ajax_ptb_ajax_taxonomy_name_validate', array($plugin_admin, 'ptb_ajax_taxonomy_name_validate'));
            add_action('wp_ajax_ptb_remove_dialog', array($plugin_admin, 'remove_disalog'));
            add_action('wp_ajax_ptb_ajax_remove', array($plugin_admin, 'ptb_remove'));
            add_action('wp_ajax_ptb_register', array($plugin_admin, 'ptb_register'));
            add_action('wp_ajax_ptb_copy', array($plugin_admin, 'ptb_copy'));
            add_action('wp_ajax_ptb_ajax_get_post_type', array($plugin_admin, 'ptb_ajax_get_post_type'));
            add_action('wp_ajax_ptb_ajax_themes', array($plugin_admin, 'ptb_ajax_theme'));
            add_action('wp_ajax_ptb_ajax_themes_save', array($plugin_admin, 'ptb_ajax_theme_save'));
            add_action('wp_ajax_ptb_remove_custom_template', array($plugin_admin, 'ptb_remove_custom_template'));
            PTB_Public::get_instance()->init();
        } else {
            add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
            add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
            add_action('admin_init', array($plugin_admin, 'register_plugin_settings'), 11);
			add_action('admin_notices', array($plugin_admin, 'check_addons_compatible'), 11);
			add_action( 'current_screen', array( $plugin_admin, 'admin_columns' ) );
        }
        add_action('init', array($plugin_admin, 'add_ptb_shortcode'));
		add_action( 'wp_enqueue_editor', [ $plugin_admin, 'wp_enqueue_editor' ] );
		add_action( 'admin_init', [ 'PTB_Term_Meta', 'init' ], 100 );
		add_action( 'admin_init', [ 'PTB_Term_Images', 'admin_init' ], 100 );
        $this->init_custom_meta_box_types();
    }

	/**
	 * [ptb_field] shortcode handler
	 *
	 * @return string
	 */
	function ptb_field_shortcode( $atts, $content = '' ) {
		$atts = shortcode_atts( [
			'name' => '',
		], $atts, 'ptb_field' );

		if ( $atts['name'] !== '' ) {
			$field_name = $atts['name'];
			unset( $atts['name'] );
			return ptb_get_field( $field_name, $atts );
		}
	}

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

       PTB_Public::get_instance()->init();

    }

    /**
     * Creates instances of custom meta boxes
     *
     * @since 1.0.0
     */
    private function init_custom_meta_box_types() {
        new PTB_CMB_Text('text', self::$plugin_name, self::$version);
        new PTB_CMB_Email('email', self::$plugin_name, self::$version);
        new PTB_CMB_Textarea('textarea', self::$plugin_name, self::$version);
        new PTB_CMB_Radio_Button('radio_button', self::$plugin_name, self::$version);
        new PTB_CMB_Checkbox('checkbox', self::$plugin_name, self::$version);
        new PTB_CMB_Select('select', self::$plugin_name, self::$version);
        new PTB_CMB_Image('image', self::$plugin_name, self::$version);
        new PTB_CMB_Link_Button('link_button', self::$plugin_name, self::$version);
        new PTB_CMB_Number('number', self::$plugin_name, self::$version);
        new PTB_CMB_Map('map', 'ptb', self::$version);
        new PTB_CMB_Slider('slider', 'ptb', self::$version);
        new PTB_CMB_Gallery('gallery', 'ptb', self::$version);
        new PTB_CMB_Event_Date('event_date', 'ptb', self::$version);
        new PTB_CMB_Video('video', 'ptb', self::$version);
        new PTB_CMB_Audio('audio', 'ptb', self::$version);
        new PTB_CMB_File('file', 'ptb', self::$version);
        new PTB_CMB_Rating('rating', 'ptb', self::$version);
        new PTB_CMB_Progress_Bar('progress_bar', 'ptb', self::$version);
        new PTB_CMB_Icon('icon', 'ptb', self::$version);
        new PTB_CMB_Telephone('telephone', 'ptb', self::$version);
        new PTB_CMB_Accordion('accordion', 'ptb', self::$version);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        self::get_option();
        $this->define_admin_hooks();
        if (!is_admin()) {
            $this->define_public_hooks();
        }
    }

    public static function get_option() {
        if (!isset(self::$options)) {
            self::$options = new PTB_Options(self::$plugin_name, self::$version);
        }
        return self::$options;
    }

    /**
     * Returns current plugin version.
     * 
     * @return string Plugin version
     */
    public static function get_plugin_version($plugin_url) {
        return get_file_data($plugin_url, array('Version'))[0];
    }

}
