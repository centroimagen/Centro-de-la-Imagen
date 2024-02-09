<?php
/**
 * Template for displaying the loop
 */
$param_image=array(
    'w'=>$fields_args['img_width'],
    'h'=>$fields_args['img_height']
);
if ($fields_args['image_size'] !== '') {
    $param_image['image_size']= $fields_args['image_size'];
}
$is_inline_edit_supported=method_exists('Themify_Builder_Component_Base','add_inline_edit_fields');
if(Themify_Builder::$frontedit_active===true){
	$param_image['attr']=array('data-w'=>'img_width', 'data-h'=>'img_height');
}
$is_comment_open = themify_builder_get('setting-comments_posts');
if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
        ?>

        <div <?php post_class(array('post')); ?>>

            <div class="infinite-post-cover tf_abs tf_hide" style="background-color:<?php echo Themify_Builder_Stylesheet::get_rgba_color($fields_args['overlay_color']); ?>"></div>
			
			<?php if( $fields_args['hide_post_image'] !== 'yes' || in_array( $fields_args['layout'], array( 'parallax', 'overlay' ),true)  ) : ?>
				<div class="infinite-post-image">
					<?php if (( $fields_args['layout'] === 'list' || $fields_args['layout'] === 'grid' ) && $fields_args['unlink_image'] !== 'yes') : ?>
						<a href="<?php echo get_permalink(); ?>" <?php if ($fields_args['permalink'] === 'newwindow') echo 'target="_blank"'; ?> class="<?php if ($fields_args['permalink'] === 'lightboxed') echo 'themify_lightbox'; ?>">
							<?php echo themify_get_image($param_image); ?>
						</a>
					<?php else : ?>
						<?php echo themify_get_image($param_image); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

            <div class="infinite-post-inner-wrap">
                <div class="infinite-post-inner tf_w tf_h">

                    <div class="bip-post-text">

                        <?php if ($fields_args['hide_post_date'] !== 'yes') : ?>
                            <time datetime="<?php the_time('o-m-d') ?>" class="post-date entry-date updated"><?php the_time( get_option( 'date_format' ) ) ?></time>
                        <?php endif; ?>

                        <?php if ($fields_args['hide_post_title'] !== 'yes') : ?>
                            <<?php echo $fields_args['title_tag']; ?> class="post-title">
                                <?php if ($fields_args['unlink_post_title'] !== 'yes') : ?>
                                    <a href="<?php echo get_permalink(); ?>" <?php if ($fields_args['permalink'] === 'newwindow') echo 'target="_blank"'; ?> class="<?php if ($fields_args['permalink'] === 'lightboxed') echo 'themify_lightbox'; ?>">
                                        <?php the_title(); ?>
                                    </a>
                                <?php else : ?>
                                    <?php the_title(); ?>
                                <?php endif; ?>
                            </<?php echo $fields_args['title_tag']; ?>>
                        <?php endif; ?>

                        <?php if ($fields_args['hide_post_meta'] !== 'yes') : ?>
                            <p class="post-meta entry-meta">

                                <span class="post-author"><?php echo themify_get_author_link() ?></span>
                                <span class="post-category"><?php the_category(', ') ?></span>
                                <?php the_tags(' <span class="post-tag">', ', ', '</span>'); ?>
                                <?php if (!$is_comment_open && comments_open()) : ?>
                                    <span class="post-comment"><?php comments_popup_link(__('0 Comments', 'builder-infinite-posts'), __('1 Comment', 'builder-infinite-posts'), __('% Comments', 'builder-infinite-posts')); ?></span>
                                <?php endif; //post comment ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($fields_args['display_content'] === 'excerpt') : ?>
                            <div class="bip-post-content">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php elseif ($fields_args['display_content'] === 'content') : ?>
                            <div class="bip-post-content">
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($fields_args['hide_read_more_button'] !== 'yes') : ?>
                            <div class="read-more-button-wrap">
                                <a href="<?php echo get_permalink(); ?>" <?php if ($fields_args['permalink'] === 'newwindow') echo 'target="_blank"'; ?> class="read-more-button button-size-<?php echo $fields_args['read_more_size']; ?> <?php echo ( $fields_args['color_button'] !== 'default' ) ? 'ui builder_button ' . $fields_args['color_button'] : ''; ?> <?php if ($fields_args['permalink'] === 'lightboxed') echo 'themify_lightbox'; ?> button-style-<?php echo $fields_args['buttons_style']; ?>"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('read_more_text'); }?>>
                                    <?php echo $fields_args['read_more_text']; ?>
                                </a>
                            </div>
                        <?php endif; ?>

                    </div><!-- .bip-post-text -->
                </div><!-- .infinite-post-inner -->

            </div><!-- .infinite-post-inner -->

            <?php
            if ($fields_args['layout'] === 'parallax') {
                echo '<style>
				#', $args['module_ID'] ,'  .post-' , $post->ID , ' {background-image:url(' ,wp_get_attachment_url(get_post_thumbnail_id($post->ID)) , ')}
			</style>';
            }
            ?>

        </div><!-- .post -->
<?php
endwhile;
wp_reset_postdata();

elseif ( isset( $fields_args['no_posts'], $fields_args['no_posts_msg'] ) ) :
	echo '<div class="tb_no_posts">' . $fields_args['no_posts_msg'] . '</div>';
endif;
