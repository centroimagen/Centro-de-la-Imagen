<?php
/**
 * Template to display PTB posts in lightbox window
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/lightbox.php
 *
 * @package Themify PTB
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>

	<head>
		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>

		<?php wp_body_open(); ?>

		<?php echo PTB_Public::get_instance()->get_lightbox_content(); ?>

		<?php wp_footer(); ?>
	</body>

</html>