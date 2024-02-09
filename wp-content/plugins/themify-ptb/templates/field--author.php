<?php
/**
 * Author field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--author.php
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

<?php if ( ! empty( $meta_data['post_author'] ) ) : ?>
	<span class="ptb_post_author ptb_post_meta">
		<span class="ptb_author" itemprop="author" itemscope itemtype="https://schema.org/Person">
			<a href="<?php echo esc_url( get_author_posts_url( $meta_data['post_author'] ) ) ?>" rel="author" itemprop="url">
				<span itemprop="name"><?php echo get_the_author_meta( 'display_name', $meta_data['post_author'] ); ?></span>
			</a>
		</span>
	</span>
<?php endif; ?>
