<?php
/**
 * Template to display Slider field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-slider.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

if (!empty($meta_data[$args['key']]) && !empty(array_filter($meta_data[$args['key']]['url'])) ) {
	$slider_data = shortcode_atts( [
		'minSlides' => 1,
		'autoHover' => 1,
		'pause' => 3,
		'pager' => 1,
		'controls' => 1,
		'speed' => 200,
		'mode' => 'horizontal'
	], $data );
    $caption = ! empty( $data['captions'] );
    ?>
	<div
		class="ptb_slider tf_swiper-container"
		data-visible="<?php echo $slider_data['minSlides'] ?>"
		data-pause_hover="<?php echo $slider_data['autoHover'] ?>"
		data-auto="<?php echo $slider_data['pause'] ?>"
		data-pager="<?php echo $slider_data['pager'] ?>"
		data-slider_nav="<?php echo $slider_data['controls'] ?>"
		data-mode="<?php echo $slider_data['mode'] ?>"
		data-speed="<?php echo $slider_data['speed'] ?>"
	>
		<div class="tf_swiper-wrapper">
			<?php foreach ($meta_data[$args['key']]['url'] as $index => $slider): ?>
				<?php if ($slider): ?>
					<?php
					$title = !empty($meta_data[$args['key']]['title'][$index]) ? esc_attr($meta_data[$args['key']]['title'][$index]) : '';
					if ($caption) {
						$title.=!empty($meta_data[$args['key']]['description'][$index]) ? (' - ' . esc_attr($meta_data[$args['key']]['description'][$index])) : '';
					}
					$video = !in_array(pathinfo($slider, PATHINFO_EXTENSION), array('png', 'jpg', 'gif', 'jpeg', 'bmp'), true);
					$slider = esc_url($slider);
					$link =!empty($meta_data[$args['key']]['link'][$index])?esc_url($meta_data[$args['key']]['link'][$index]):false;
					?>
					<div class="tf_swiper-slide">
					   
						<?php if (!$video): ?>
							<img class="ptb_extra_image" src="<?php echo $slider ?>" alt="<?php echo $title ?>" title="<?php echo $title ?>" />
						<?php else: ?>
							<?php
							$remote = strpos($slider, 'vimeo.com') !== false || strpos($slider, 'youtu.be') !== false || strpos($slider, 'youtube.com') !== false;
							?>
							<?php if ($remote): ?>
								<?php
								global $wp_embed;
								echo $wp_embed->run_shortcode('[embed]' . $slider . '[/embed]');
								?>
							<?php else: ?>
								<video width="100%" controls><source src="<?php echo $slider ?>"></video>
							<?php endif; ?>
						<?php endif; ?>
						<?php if($link):?>
							<a class="ptb_slider_link" href="<?php echo $link ?>"></a>
						<?php endif;?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
    <?php
}