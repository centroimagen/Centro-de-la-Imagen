<?php
/**
 * Category field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--category.php
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
$key = $type === 'post_tag' ? 'tags_input' : 'post_category';
$link = isset( $data['link'] ) && $data['link'] === 'no' ? false : true;

if (!empty($meta_data[$key])): ?>
	<span class="ptb_post_category ptb_post_meta">
		<?php
		if (!$data['seperator']) {
			$data['seperator'] = ',';
		}
		?>
		<?php echo ptb_get_the_term_list( get_the_ID(), $type, '', $data['seperator'], '', $link ); ?>
	</span>   
<?php endif; ?>
