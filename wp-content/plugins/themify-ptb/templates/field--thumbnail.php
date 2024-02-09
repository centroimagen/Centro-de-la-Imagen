<?php
/**
 * Thumbnail field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--thumbnail.php
 *
 * @var string $type
 * @var array $args
 * @var array $data
 * @var array $meta_data
 * @var array $lang
 * @var boolean $is_single single page
 * @var string $index index in themplate
 *
 * @package Themify PTB
 */
?>

<?php if (has_post_thumbnail()): ?>

	<?php
	$thumb_id = get_post_thumbnail_id();
	$thumb = get_post(get_post_thumbnail_id());
	$image = themify_ptb_do_img( $thumb_id, $data['width'], $data['height'] );
	$title = ! empty( $thumb ) && $thumb->post_title ? $thumb->post_title : (isset($meta_data['post_title']) ? $meta_data['post_title'] : '');
	$alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
	if (!$alt) {
		$alt = $title;
	}
	$class = isset( $data['thumbnail_link'] ) && $data['thumbnail_link'] == 'lightbox' ? ' class="ptb_lightbox"' : '';
	?>
	<figure class="ptb_post_image tf_clearfix">
		<?php
		if (!empty($data['thumbnail_link'])): echo '<a' . $class . ($data['thumbnail_link'] == 'new_window' ? ' target="_blank"' : '' ) . ' href="' . $meta_data['post_url'] . '">';
		endif;
		?>
		<img src="<?php echo $image['url'] ?>" alt="<?php echo $alt ?>"
			<?php if ( $image['width'] ) : ?> width="<?php echo $image['width']; ?>" <?php endif; ?>
			<?php if ( $image['height'] ) : ?> height="<?php echo $image['height']; ?>" <?php endif; ?>
			title="<?php echo $title ?>"/>
			<?php
			if (!empty($data['thumbnail_link'])): echo '</a>';
			endif;
			 ?>
	</figure>
<?php endif; ?>
