<?php
/**
 * Text field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-text.php
 *
 * @var string $type
 * @var array $args
 * @var array $data
 * @var array $meta_data
 * @var array $lang
 * @var boolean $is_single single page
 *
 * @package Themify PTB
 */

if ( empty( $meta_data[$args['key']] ) ) {
	return;
}
$meta_data = $meta_data[$args['key']];
if (!isset($data['display'])) {
	$data['display'] = 'one_line';
}
if (empty($data['seperator'])) {
	$data['seperator'] = ', ';
}
$data['seperator'] = '<span class="ptb_field_separator">' . trim( $data['seperator'] ) . '</span> ';
if ( ! empty( $args['repeatable'] ) ) {
	if (!is_array($meta_data)) {
		$meta_data = array($meta_data);
	}
	$seperator = $data['display'] === 'one_line' ? $data['seperator'] : FALSE;
	$this->get_repeateable_text( $data['display'], $meta_data, $seperator );
} else {
	if (is_array($meta_data)) {
		if ($data['display'] === 'one_line') {
			$text = implode($data['seperator'], $meta_data);
		}
	} else {
		$text = $meta_data;
	}
	if ( ! empty( $data['tag'] ) ) {
		if ( ! empty( $text ) || $text === '0' ) {
			echo '<' . $data['tag'] . '>' . $text . '</' . $data['tag'] . '>';
		}
	} else {
		$this->get_text($text);
	}
}