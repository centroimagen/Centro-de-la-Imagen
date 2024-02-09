<?php
/*
Plugin Name:  Builder Timeline
Plugin URI:   https://themify.me/addons/timeline
Version:      3.0.1
Author:       Themify
Author URI:   https://themify.me
Description:  Display content in a timeline-styled layouts. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:  builder-timeline
Domain Path:  /languages
Compatibility: 7.0.0
*/

defined( 'ABSPATH' ) or die( '-1' );

class Builder_Timeline {
	private static $timeline_sources;
	public static $url;
	public static $version;

	 /**
     * Init Builder Tiles
     */
    public static function init() {
		self::constants();
		add_action( 'init', array( __CLASS__, 'i18n' ) );
		add_action( 'themify_builder_setup_modules', array( __CLASS__, 'register_module' ) );
		if(is_admin()){
		    add_filter( 'plugin_row_meta', array( __CLASS__, 'themify_plugin_meta'), 10, 2 );
		    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( __CLASS__, 'action_links') );
		}
	}

	private static function constants() {
		$data = get_file_data( __FILE__, array( 'Version' ) );
		self::$version = $data[0];
		self::$url = trailingslashit( plugin_dir_url( __FILE__ ) );
	}

	public static function themify_plugin_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$row_meta = array(
			  'changelogs'    => '<a href="' . esc_url( 'https://themify.me/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'themify' ) . '">' . esc_html__( 'View Changelogs', 'themify' ) . '</a>'
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
	public static function i18n() {
		load_plugin_textdomain( 'builder-timeline', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public static function register_module() {
            $dir = trailingslashit( plugin_dir_path( __FILE__ ) );
            if(method_exists('Themify_Builder_Model', 'add_module')){
                Themify_Builder_Model::add_module($dir . 'modules/module-timeline.php' );
            }
            else{
                Themify_Builder_Model::register_directory('templates', $dir . 'templates');
                Themify_Builder_Model::register_directory('modules', $dir . 'modules');
            }
	}


	private static function load_sources($item='all') {
		$items=array( 'post', 'text', 'twitter' );
		if($item!=='all'){
			if(in_array($item,$items,true)){
				$items=array($item);
			}
			else{
				return false;
			}
		}
		$dir = trailingslashit( plugin_dir_path( __FILE__ ) );
		foreach($items as $name ) {
			include( $dir . 'includes/timeline-source-' . $name . '.php' );
			$class_name = "Builder_Timeline_{$name}_Source";
			if( class_exists( $class_name ) ) {
				self::$timeline_sources[$name] = new $class_name;
			}
		}
	}

	public static function get_sources( $name='all' ) {
		if(isset( self::$timeline_sources[$name] )){
			return self::$timeline_sources[$name];
		}
		self::load_sources($name);
		if($name==='all'){
			return apply_filters( 'builder_timeline_sources', self::$timeline_sources );
		}
		return isset( self::$timeline_sources[$name] )?self::$timeline_sources[$name]:false;
	}
}
Builder_Timeline::init();
