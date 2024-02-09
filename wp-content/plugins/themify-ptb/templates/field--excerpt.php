<?php
/**
 * Excerpt field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--excerpt.php
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

<?php
$excerpt = get_the_excerpt();
?>
<div itemprop="articleBody">
	<?php echo $excerpt!=='' && $data['excerpt_count'] > 0 ? wp_trim_words($excerpt, $data['excerpt_count']) : $excerpt;
	if( isset($data['readmore']) ){
        $text = !empty( $data['readmore_text'] ) ? $data['readmore_text'] : __('Read More..', 'ptb');
		?>
		<a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>" class="ptb_excerpt_readmore_link"><?php echo esc_html( $text ); ?></a>
		<?php
    } ?>
</div>