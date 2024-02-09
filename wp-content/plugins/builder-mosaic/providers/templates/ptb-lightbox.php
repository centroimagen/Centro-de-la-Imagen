<?php
/**
 * Template to display single post lightboxes in PTB provider
 *
 *@package Themify
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>

		<?php
		global $post;
		if ( $post && $post->post_status === 'publish' ) {
			$short_code = '[ptb post_id=' . $post->ID . ' type=' . $post->post_type . ']';
			echo '<div class="ptb_single_lightbox">' . do_shortcode( $short_code ) . '</div>';
		}
		?>

		<?php wp_footer(); ?>
	</body>
</html>