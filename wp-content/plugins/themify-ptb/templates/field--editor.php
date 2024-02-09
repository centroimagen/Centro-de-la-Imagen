<?php
/**
 * Editor field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--editor.php
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
<div class="ptb_entry_content" itemprop="articleBody">
	<?php the_content(); ?>
    <?php if($is_single) {
        wp_link_pages(array('before' => '<p class="post-pagination"><strong>'.__('Pages:','ptb').'</strong> ', 'after' => '</p>', 'next_or_number' => 'number'));
    } ?>
</div>