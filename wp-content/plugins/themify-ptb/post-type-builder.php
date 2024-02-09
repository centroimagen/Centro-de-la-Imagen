<?php
/**
 * Old plugin file, loads the themify-ptb.php file
 * Needed for backward compatibility, can be removed in mid 2024
 */

add_action( 'admin_init', function() {
	$network_wide = is_multisite() && is_plugin_active_for_network( 'themify-ptb/post-type-builder.php' );
	deactivate_plugins( 'themify-ptb/post-type-builder.php', true, $network_wide );
	activate_plugins( 'themify-ptb/themify-ptb.php', '', $network_wide, true );
} );

include_once( dirname(__FILE__) . '/themify-ptb.php' );