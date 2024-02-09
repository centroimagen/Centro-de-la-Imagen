<?php
/*
Plugin Name:  Builder Mosaic
Plugin URI:   https://themify.me/addons/mosaic
Version:      3.0.4
Author:       Themify
Author URI:   https://themify.me
Description:  Make beautiful mosaic/masony tile layouts with Themify Builder. The content can be pulled dynamically from blog posts, WooCommerce products, gallery images, RSS feeds, custom post types, directory listing, and more.
Text Domain:  builder-mosaic
Domain Path:  /languages
Compatibility: 7.0.0
WC tested up to: 4.9.0
*/

defined( 'ABSPATH' ) or die( '-1' );

class Builder_Mosaic {

	public static $url;
	public static $dir;

	 /**
     * Init Builder Mosaic
     */
    public static function init() {
		add_action( 'init', array( __CLASS__, 'i18n' ) );
		add_action( 'themify_builder_setup_modules', array( __CLASS__, 'register_module' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'constants' ) );
		if ( is_admin() ) {
			add_action( 'wp_ajax_builder_tiled_posts_save_preset', array( __CLASS__, 'save_preset' ) );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'themify_plugin_meta'), 10, 2 );
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( __CLASS__, 'action_links') );
			/* load Term Cover feature in Themify framework */
			add_theme_support( 'themify-term-cover' );
		}
		add_action('themify_builder_active_enqueue', array(__CLASS__, 'admin_enqueue'), 15);
	}

    public static function get_version(){
        return '3.0.4';
    }

	public static function constants() {
	    self::$url = trailingslashit( plugin_dir_url( __FILE__ ) );
	    self::$dir = trailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public static function i18n() {
		load_plugin_textdomain( 'builder-mosaic', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	
	public static function themify_plugin_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$row_meta = array(
			  'changelogs'    => '<a href="' . esc_url( 'https://themify.org/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'themify' ) . '">' . esc_html__( 'View Changelogs', 'themify' ) . '</a>'
			);
	 
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
	
	public static function action_links( $links ) {
		if ( is_plugin_active( 'themify-updater/themify-updater.php' ) ) {
			$tlinks = array(
			 '<a href="' . admin_url( 'index.php?page=themify-license' ) . '">'.__('Themify License', 'themify') .'</a>',
			 );
		} else {
			$tlinks = array(
			 '<a href="' . esc_url('https://themify.me/docs/themify-updater-documentation') . '">'. __('Themify Updater', 'themify') .'</a>',
			 );
		}
		return array_merge( $links, $tlinks );
	}


	public static function admin_enqueue() {
		global $wp_version;
        $ver=self::get_version();
		themify_enque_script( 'themify-builder-mosaic-admin', self::$url . 'assets/admin.js',$ver,  array('themify-builder-app-js'));
		// Retrieves the variables passed to the addon's admin.js
		wp_localize_script( 'themify-builder-mosaic-admin', 'builderMosaicAdmin', array(
			'path' => self::$url,
			'jqui' => includes_url( 'js/jquery/ui/' ),
			'ui_widget' => version_compare( $wp_version, '5.6', '<' ),
			'admin_css'=>themify_enque(self::$url . 'assets/admin.css'),
			'presets' => self::get_custom_presets(),
			'v'=>  $ver,
			'labels'=>array(
			    'custom'=>__( 'Custom:', 'builder-mosaic' ),
			    'name'=> __( 'Name', 'builder-mosaic' ),
			    'template'=>__( 'Tile Templates', 'builder-mosaic' ),
			    'add'=>__( 'Add Tile', 'builder-mosaic' ),
			    'remove'=>__( 'Remove Selected Tile', 'builder-mosaic' ),
			    'save'=>__( 'Save Current', 'builder-mosaic' )
			)
		) );
	}

	public static function register_module() {
                if(method_exists('Themify_Builder_Model', 'add_module')){
                    Themify_Builder_Model::add_module(self::$dir . 'modules/module-mosaic.php' );
                }
                else{
                   Themify_Builder_Model::register_directory( 'modules', self::$dir . 'templates' );
                   Themify_Builder_Model::register_directory( 'modules', self::$dir . 'modules' );
                }
		
		include self::$dir . 'providers/base.php';
		
	}

	/**
	 * Returns a list of tile presets, saved by user
	 *
	 * @return array
	 */
	private static function get_custom_presets() {
		$presets = array();
		$posts = get_posts( array(
			'post_type' => 'tftp_template',
			'no_found_rows'=>true,
			'posts_per_page' => -1,
		) );
		foreach($posts as $template ) {
			$presets[ $template->ID ] = array(
				'title' => $template->post_title,
				'content' => $template->post_content,
			);
		}

		return $presets;
	}

	public static function save_preset() {
		if ( isset( $_POST['name'],$_POST['grid'] )) {
                    check_ajax_referer('tf_nonce', 'nonce');
                    $result = wp_insert_post( array(
                            'post_title' => stripslashes( $_POST['name'] ),
                            'post_content' => stripslashes( $_POST['grid'] ),
                            'post_status' => 'publish',
                            'post_type' => 'tftp_template'
                    ) );
                    echo is_wp_error( $result ) ?$result->get_error_message():$result;
		}
		wp_die();
	}
}
Builder_Mosaic::init();
