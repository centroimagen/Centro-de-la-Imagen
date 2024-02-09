<?php

/**
 * Fired during plugin activation
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        set_transient( 'ptb_welcome_page', true, 30 );

		$ptb_options = PTB::get_option();
		$ptb_options->ptb_register_custom_taxonomies();
		$ptb_options->ptb_register_custom_post_types();
		flush_rewrite_rules();
    }
}
