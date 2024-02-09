<?php
/**
 * Select field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-select.php
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
?>

<?php

if (!$meta_data || empty($args['options'])) {
	return false;
}

if (!isset($data['display'])) {
	$data['display'] = 'list';
}
if(!is_array($meta_data[$args['key']])){
	$meta_data[$args['key']] = array($meta_data[$args['key']]);
}
$options = array();
foreach ($args['options'] as $opt) {
	if (in_array($opt['id'], $meta_data[$args['key']])) {
		$options[] = $opt[$lang];
	}
}
if (!empty($args['multipleSelects'])){
	$seperator = $data['display']==='one_line' && !empty($data['seperator'])?$data['seperator']:false;
	$this->get_repeateable_text($data['display'], $options,$seperator);
}
else{
	$this->get_text(current($options));
}
