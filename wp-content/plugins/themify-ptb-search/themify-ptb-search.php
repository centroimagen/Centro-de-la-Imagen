<?php
/**
 * Plugin Name:       PTB Search
 * Plugin URI:        https://themify.me/ptb-addons/search
 * Description:       Addon to use with Post Type Builder plugin that allows users to create search forms for PTB custom post types.
 * Version:           2.0.2
 * Author:            Themify
 * Author URI:        https://themify.me
 * Text Domain:       ptb-search
 * Domain Path:       /languages
 * Compatibility:     2.0.0
 *
 * @link              https://themify.me
 * @since             1.0.0
 * @package           PTB
 */
// If this file is called directly, abort.

defined('ABSPATH') or die('-1');

add_action( 'themify_ptb_loaded', 'ptb_search' ); /* PTB 2.0+ */
add_action( 'ptb_loaded', 'ptb_search_admin_notice' ); /* deprecated hook in old PTB */

function ptb_search() {
    include_once plugin_dir_path(__FILE__) . 'includes/class-ptb-search.php';
    new PTB_Search('2.0.2');
}

function ptb_search_admin_notice() {
	add_action( 'admin_notices', function() {
		?>
		<div class="error">
			<p><?php _e('PTB Search plugin requires the latest version of PTB plugin. Please update the PTB plugin.', 'ptb-search'); ?></p>
		</div>
		<?php
	} );
}

add_filter( 'plugin_row_meta', 'themify_ptb_search_meta', 10, 2 );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'themify_ptb_search_action_links' );
function themify_ptb_search_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		$row_meta = array(
		  'changelogs'    => '<a href="' . esc_url( 'https://themify.org/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'ptb-search' ) . '">' . esc_html__( 'View Changelogs', 'ptb-search' ) . '</a>'
		);
 
		return array_merge( $links, $row_meta );
	}
	return (array) $links;
}
function themify_ptb_search_action_links( $links ) {
	if ( is_plugin_active( 'themify-updater/themify-updater.php' ) ) {
		$tlinks = array(
		 '<a href="' . admin_url( 'index.php?page=themify-license' ) . '">'.__('Themify License', 'ptb-search') .'</a>',
		 );
	} else {
		$tlinks = array(
		 '<a href="' . esc_url('https://themify.me/docs/themify-updater-documentation') . '">'. __('Themify Updater', 'ptb-search') .'</a>',
		 );
	}
	return array_merge( $links, $tlinks );
}
