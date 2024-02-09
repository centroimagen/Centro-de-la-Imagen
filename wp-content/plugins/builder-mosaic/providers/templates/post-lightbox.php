<?php
/**
 * Template to display single post lightboxes in Post provider
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
		<div class="tb_mosaic_wrap">
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="tb_mosaic_post_thumbnail">
						<?php the_post_thumbnail( 'medium' ); ?>
					</figure>
				<?php endif; ?>

				<?php the_title( '<h2 class="tb_mosaic_post_title">', '</h2>' ) ?>

				<div class="tb_mosaic_post_content">
					<?php the_content(); ?>
				</div>

			<?php endwhile; ?>
		</div><!-- .tb_mosaic_lightbox -->
		<?php wp_footer(); ?>
	</body>
</html>