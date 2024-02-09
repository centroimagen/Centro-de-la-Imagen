<?php
/**
 * Date field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-date.php
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

<time class="ptb_post_date ptb_post_meta" datetime="<?php echo date('Y-m-d', strtotime($meta_data['post_date'])) ?>" itemprop="datePublished">
	<?php if (!empty($data['date_format'])): ?>
		<?php echo date_i18n($data['date_format'], strtotime($meta_data['post_date'])) ?>
	<?php else: ?>
		<?php echo $meta_data['post_date'] ?>
	<?php endif; ?>
</time>
