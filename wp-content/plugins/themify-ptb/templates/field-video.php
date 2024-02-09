<?php
/**
 * Template to display Video field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-video.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */
if ( empty( $meta_data[ $args['key'] ] ) ) {
	return;
} else if ( is_string( $meta_data[ $args['key'] ] ) ) {
	$meta_data[ $args['key'] ] = array( 'url' => array( $meta_data[ $args['key'] ] ) );
}

if ( count( array_filter($meta_data[$args['key']]['url']) ) > 0 ): ?>
	<?php
	$class = array();
	$class[] = 'ptb_extra_columns_' . ( isset( $data['columns'] ) ? $data['columns'] : 1 );
	$preview = isset($data['preview']);
	if ($preview) {
		$class[] = 'ptb_extra_video_preview';
	}
	$lightbox = isset($data['lightbox']) && $preview;
	$permalink = isset($data['link']) && $data['link'] === '1' && $preview;
	if ($lightbox) {
		$class[] = 'ptb_extra_lightbox';
	} elseif ($permalink) {
		$class[] = 'ptb_extra_permalink';
	}
	$class = implode(' ', $class);
	global $wp_embed;
	$ptb_empty_field = true;
	?>
	<div class="ptb_extra_video ptb_extra_grid  <?php echo $class ?>">
		<?php foreach ($meta_data[$args['key']]['url'] as $index => $value): ?>
			<?php
			if ( ! $value ) {
				continue;
			}
			$value = esc_url_raw( $value );
			$title = !empty($meta_data[$args['key']]['title'][$index]) ? $meta_data[$args['key']]['title'][$index] : '';
			$description = !empty($meta_data[$args['key']]['description'][$index]) ? $meta_data[$args['key']]['description'][$index] : '';
			$remote = strpos($value, 'vimeo.com') !== false || strpos($value, 'youtu.be') !== false || strpos($value, 'youtube.com') !== false;
			$hqthumb = '';
			if ( $remote ) {
				if ( $preview ) {
					$hqthumb = PTB_CMB_Video::parse_video_url( $value, 'hqthumb' );
					if ( $hqthumb ) {
						$hqthumb = $hqthumb['data'];
					}
					if ( ! $lightbox ) {
						$embed = PTB_CMB_Video::parse_video_url( $value, 'embed' );
						if ( $embed ) {
							$value = add_query_arg( 'autoplay', 1, $embed['url'] );
						}
					}
				}
			}
			if ( $lightbox ) {
				$link = $value;
			} elseif ( $permalink ) {
				$link = get_permalink();
			}
			$ptb_empty_field = false;
			?>
			<div class="ptb_extra_item ptb_extra_video_item" data-type="<?php echo $remote ? 'remote' : 'local'; ?>">
				<div class="ptb_extra_video_overlay_wrap">

					<?php if ( isset( $link ) ) : ?>
						<a href="<?php echo esc_url_raw( $link ) ?>" title="<?php esc_attr_e($title) ?>" class="<?php if ($lightbox): ?>ptb_extra_video_lightbox<?php endif; ?> ptb_extra_video_overlay"></a>
					<?php endif; ?>

					<?php if ( $preview ) : ?>
						<div class="ptb_extra_play_icon<?php echo ! $lightbox ? ' ptb_extra_show_video' : '' ?>"<?php if ( ! $lightbox && $remote ) : ?> data-url="<?php echo $value; ?>"<?php endif; ?>><?php echo PTB_Utils::get_icon( 'fa-play' ); ?></div>
					<?php endif; ?>

					<?php if ( ! $remote ) : ?>
						<video preload="metadata" <?php if ( ! $preview ) : ?>controls="controls"<?php endif; ?>>
							<source src="<?php echo $value ?>#t=0.1">
						</video>
					<?php else: ?>
						<?php if ( $preview && ! empty( $hqthumb ) ) : ?>
							<img class="ptb_image" src="<?php echo $hqthumb ?>" alt="<?php esc_attr_e($title) ?>" title="<?php esc_attr_e($title) ?>" />
						<?php else : ?>
							<div class="fluid-width-video-wrapper">
								<?php echo $wp_embed->run_shortcode('[embed]' . $value . '[/embed]'); ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>

				</div>
				<?php if ( '' !== $title ) : ?><h3 class="ptb_extra_video_title"><?php echo $title ?></h3><?php endif; ?>
				<div class="ptb_extra_video_description"><?php echo esc_html($description); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
