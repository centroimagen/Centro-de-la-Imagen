<?php
/**
 * Title field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--title.php
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
global $post;
$tag = $data['title_tag'] === 'p' || $data['title_tag'] === 'div' ? $data['title_tag'] : 'h' . $data['title_tag'];
?>

<<?php echo $tag; ?> class="ptb_post_title ptb_entry_title" itemprop="name">
	<?php
	if (isset($data['text_before'][$lang])) {
		PTB_CMB_Base::get_text_after_before($data['text_before'][$lang], true);
	}
	if (!empty($data['title_link'])) {
		echo '<a' . ( $data['title_link'] == 'lightbox' ? ' class="ptb_lightbox"' : '' ) . ( $data['title_link'] == 'new_window' ? ' target="_blank"' : '' ) . ' href="' . $meta_data['post_url'] . '">';
	}
	echo $post->post_title;
	if (!empty($data['title_link'])) {
		echo '</a>';
	}
	?>
</<?php echo $tag; ?>>
