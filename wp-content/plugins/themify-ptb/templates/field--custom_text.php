<?php
/**
 * Custom Text field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--custom_text.php
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

<?php if (!empty($data['text'][$lang])):
	$text = do_shortcode(PTB_CMB_Base::format_text($data['text'][$lang],$is_single));
	echo $text;
endif; ?>