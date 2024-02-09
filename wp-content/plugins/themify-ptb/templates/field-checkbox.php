<?php
/**
 * Checkbox field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-checkbox.php
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

$display = ! empty( $data['display'] ) ? $data['display'] : 'one_line';
$seperator = ! empty( $data['seperator'] ) ? $data['seperator'] : ', ';

if (!$meta_data || empty($args['options'])) {
	return false;
}
if(!is_array($meta_data[$args['key']])){
	$meta_data[$args['key']] = array($meta_data[$args['key']]);
}

$options = array();
foreach ($args['options'] as $opt) {
	if (in_array($opt['id'], $meta_data[$args['key']])) {
		if ( isset( $opt[ $lang ] ) ) {
			$options[] = $opt[ $lang ];
		}
	}
}

$seperator = $display === 'one_line' ? $seperator : false;
$this->get_repeateable_text( $display, $options, $seperator );
