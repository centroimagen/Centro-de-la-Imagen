<?php
/**
 * Base class for Builder_Data_Provider classes
 *
 * @package Themify
 */
if ( ! class_exists( 'Builder_Data_Provider' ) ) :
class Builder_Data_Provider {
	/* instances of data providers for this module */
	private static $providers;
	
	function is_available() {
		return true;
	}

	function get_id() {}

	function get_label() {}

	function get_options() {
		return array();
	}

	function get_error() {
		return '';
	}

	function get_items( $settings, $limit, $paged ) {}
	
	/**
	 * Check if page is currently displayed inside lightbox window
	 *
	 * @return bool
	 */
	protected static function is_lightbox( $key = 'tb-mosaic-lightbox' ) {
		return isset( $_GET[$key], $_GET['iframe'] )
			// this parameter is sent by getiFrameLink() in themify.gallery.js
			&& $_GET['iframe'] === 'true';
	}
	
	
	/**
	 * Initialize data providers for the module
	 *
	 * Other plugins or themes can extend or add to this list
	 * by using the "builder_tiled_posts_providers" filter.
	 */
	private static function init_providers($type='all') {
		$providers = apply_filters( 'builder_data_providers', array(
			'posts' => 'Builder_Data_Provider_Posts',
			'terms' => 'Builder_Data_Provider_Terms',
			'text' => 'Builder_Data_Provider_Text',
			'gallery' => 'Builder_Data_Provider_Gallery',
			'wc' => 'Builder_Data_Provider_WooCommerce',
			'portfolio' => 'Builder_Data_Provider_Portfolio',
			'ptb' => 'Builder_Data_Provider_PTB',
			'tep' => 'Builder_Data_Provider_Event_Posts',
			'nextgen' => 'Builder_Data_Provider_NextGen_Gallery',
			'rss' => 'Builder_Data_Provider_RSS',
			'files' => 'Builder_Data_Provider_Files',
		) );

		if ( $type !== 'all' ) {
			if ( ! isset( $providers[ $type ] ) ) {
				return false;
			}
			$providers = array( $type => $providers[ $type ] );
		}

		$dir = trailingslashit( dirname( __FILE__ ) );
		foreach ( $providers as $id => $provider ) {
			if ( ! isset( self::$providers[ $id ] ) ) {
				if ( is_file( $dir . '/' . $id . '.php' ) ) {
					include_once( $dir . '/' . $id . '.php' );
				}
				if ( class_exists( $provider ) ) {
					$p = new $provider();
					if ( $p->is_available() ) {
						self::$providers[ $id ] = $p;
					}
				}
			}
		}
	}

	/**
	 * Helper function to retrieve a provider instance
	 *
	 * @return object
	 */
	public static function get_providers( $id='all' ) {
		if(!isset( self::$providers[ $id ] )){
			self::init_providers($id);
		}
		if($id==='all'){
			return self::$providers;
		}
		return isset( self::$providers[ $id ] ) ? self::$providers[ $id ] : false;
	}
}

endif;