<?php 
	$settings= $args['settings'];
?>
<div id="tbm_<?php echo $args['module']['module_ID']?>"class="tpgs-wrap grid-stack tf_rel" data-cell-height="<?php echo $settings['base_height']; ?>" data-min-width="<?php echo $settings['min_width']; ?>">
	<?php 
	$is_slider=$settings['show_as']==='slider';
	$is_inline_edit_supported=$settings['tiled_posts_display']==='text' && method_exists('Themify_Builder_Component_Base','add_inline_edit_fields');
	foreach ( $args['template'] as $i => $block ) :
		if ( ! isset( $args['res']['items'][ $i ] ) ) {
			continue;
		}
		$item = $args['res']['items'][ $i ];
		if ( ! empty( $settings['caption_length'] ) ) {
			$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			$item['text'] = wp_trim_words( $item['text'], $settings['caption_length'], $excerpt_more );
		}
		?>

		<div data-gs-x="<?php echo $block['x']; ?>" data-gs-y="<?php echo $block['y']; ?>" data-gs-width="<?php echo $block['width']; ?>" data-gs-height="<?php echo $block['height']; ?>" data-hover="<?php echo $settings['effect']; ?>" class="builder_mosaic_item tf_hidden tf_box <?php echo $item['css_classes']; ?>" data-effect="<?php echo $settings['entrance_effect']; ?>">
			<figure class="mosaic-container tbm_eff_<?php echo $settings['effect']; ?> tf_rel tf_box tf_w tf_h tf_overflow tf_textc">
				<?php
				if ( ! empty( $item['image'] ) ) {
					$image_width = ((int) $block['width']) * ((int) $settings['base_height']);
					$image_height = ((int) $block['height']) * ((int) $settings['base_height']);
					// inline image
					echo themify_get_image( array(
							'src'=>$item['image'], 
							'w'=>$image_width, 
							'h'=>$image_height,
							'is_slider'=>$is_slider,
							'alt'=>$item['title'],
							'title'=>$item['title'],
							'attr'=>$is_inline_edit_supported===true && Themify_Builder::$frontedit_active===true?array('data-name'=>'image','data-repeat'=>'static_items','data-index'=>$i):false
					));
				}
				?>
				<figcaption class="tf_abs tf_w tf_h tf_box">
					<div class="tbm_caption_wrapper tf_box">
						<?php if ( ! empty( $item['audio'] ) ) : ?>
							<div class="mediPlayer tf_box">
								<audio class="listen" preload="none" src="<?php echo esc_url( $item['audio'] ); ?>"></audio>
							</div>
						<?php endif; ?>

						<?php if ( $settings['hide_title'] !== 'yes' ) : ?>
								<h2 class="tbm_title"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('title',true,false,'static_items',$i); }?>><?php echo $item['title']?></h2>
						<?php endif; ?>
						<?php if ( $settings['hide_caption'] !== 'yes' ) : ?>
							<div class="tbm_caption tf_box"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('text',true,false,'static_items',$i); }?>><?php echo $item['text']; ?></div>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $item['link'] ) ) : ?>
						<a href="<?php echo $item['link']; ?>"
							title="<?php echo esc_attr( $item['title'] ); ?>"
							<?php if ( !empty( $item['link_lightbox'] )) : ?>
								class="themify_lightbox" 
								data-rel="<?php echo $args['module']['module_ID']; ?>"
								<?php if ( ! empty( $item['lightbox_size'] ) ) : ?>
									data-zoom-config="<?php echo $item['lightbox_size']; ?>"
								<?php endif; ?>
							<?php endif; ?>
						><span class="screen-reader-text"><?php _e('Link','builder-audio'); ?></span></a>
					<?php endif; ?>
				</figcaption>

				<?php if ( $settings['hide_badge'] !== 'yes' && ! empty( $item['badge'] ) ) : ?>
					<span class="tbm_badge"><?php echo $item['badge']; ?></span>
				<?php endif; ?>
			</figure>
		</div><!-- .post -->
		<?php

	endforeach; ?>
</div><!-- .tpgs-wrap -->