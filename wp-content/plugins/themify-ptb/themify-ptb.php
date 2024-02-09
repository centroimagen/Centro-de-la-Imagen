<?php

/**
 * Plugin Name:       Post Type Builder (PTB)
 * Plugin URI:        https://themify.me/post-type-builder
 * Description:       This "all-in-one" plugin allows you to create Custom Post Types, Meta Boxes, Taxonomies, and Templates.
 * Version:           2.0.5 
 * Author:            Themify
 * Author URI:        https://themify.me
 * Text Domain:       ptb
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ptb-activator.php
 */
function activate_ptb() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-ptb-activator.php';
    PTB_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ptb-deactivator.php
 */
function deactivate_ptb() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-ptb-deactivator.php';
    Ptb_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ptb');
register_deactivation_hook(__FILE__, 'deactivate_ptb');
add_filter( 'plugin_row_meta', 'themify_ptb_plugin_row_meta', 10, 2 );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'themify_ptb_action_links' );
function themify_ptb_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		$row_meta = array(
		  'changelogs'    => '<a href="' . esc_url( 'https://themify.org/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'ptb' ) . '">' . esc_html__( 'View Changelogs', 'ptb' ) . '</a>'
		);

		return array_merge( $links, $row_meta );
	}
	return (array) $links;
}

function themify_ptb_action_links( $links ) {
	if ( is_plugin_active( 'themify-updater/themify-updater.php' ) ) {
		$tlinks = array(
		 '<a href="' . admin_url( 'index.php?page=themify-license' ) . '" target="blank">'.__('Themify License', 'ptb') .'</a>',
		 );
	} else {
		$tlinks = array(
		 '<a href="' . esc_url('https://themify.me/docs/themify-updater-documentation') . '" target="blank">'. __('Themify Updater', 'ptb') .'</a>',
		 );
	}
	return array_merge( $links, $tlinks );
}

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-ptb.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ptb() {
    PTB::get_instance()->set_constants( '2.0.5', plugin_dir_path( __FILE__ ), plugin_dir_url( __FILE__ ) );
    PTB::get_instance()->run();
}

run_ptb();
