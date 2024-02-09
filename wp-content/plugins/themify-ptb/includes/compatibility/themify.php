<?php
/**
 * Provide compatibility with Themify themes and Themify Builder plugin
 * @link https://themify.me/
 *
 */

class PTB_Themify_Compat {

	public static function init() {
		if ( is_admin() ) {
			add_action( 'themify_builder_admin_enqueue', array( __CLASS__, 'admin_enqueue' ) );
		} else {
			add_action( 'themify_builder_frontend_enqueue', array(__CLASS__, 'admin_enqueue') );
		}
		add_action( 'ptb_template_publicvideo', [ __CLASS__, 'template_video' ], 10, 4);
	}

	/**
	 * Load PTB widget's assets in Themify Builder editor interfac
	 *
	 */
	public static function admin_enqueue() {
		$plugin_dir = PTB::$uri;
		wp_enqueue_script( 'ptb-widget-js', PTB_Utils::enque_min( $plugin_dir . 'admin/js/ptb-widget.js' ), array( 'jquery' ), PTB::get_plugin_name(), true );
		wp_localize_script( 'ptb-widget-js', 'ptbWidget', [
			'css' => PTB_Utils::enque_min( $plugin_dir . 'admin/css/ptb-widget.css' ),
		] );
	}

	/**
	 * Fix local-hosted videos when using Themify themes
	 */
	public static function template_video( $ptb_empty_field, $args, $data, $meta_data ) {
		wp_enqueue_style( 'ptb-themify-compat', PTB::$uri . 'includes/compatibility/css/ptb-themify.css' );
		return $ptb_empty_field;
	}
}