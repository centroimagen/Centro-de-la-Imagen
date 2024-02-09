<?php
/**
 * Template to display Map field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-map.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

$data = wp_parse_args( $data, [
	'width' => '',
	'height' => '',
	'zoom' => '8',
	'road_type' => 'ROADMAP',
	'width_t' => '',
] );
$vals = $meta_data[$args['key']];
$is_empty = empty($data['default']);
if ($is_empty && (!$vals || empty($vals['place']))) {
    return;
}
$place = json_decode($vals['place'], TRUE);
if ($is_empty && empty($place['place']) && empty($place['location'])) {
    return;
}
$data['width'] = floatval($data['width']);
$data['height'] = floatval($data['height']);
if ($data['width'] <= 0) {
    $data['width'] = 100;
}
if ($data['height'] <= 0) {
    $data['height'] = 300;
}
$json_data = array();
$json_data['display'] = isset($data['displayAs'])?$data['displayAs']:'map';
$json_data['zoom'] = (int) $data['zoom'];
$json_data['mapTypeId'] = $data['road_type'];
$json_data['info'] = !empty($vals['info']) ? $vals['info'] : '';
$json_data['place'] = $place;
if(!$is_empty){
    $json_data['default'] =$data['default'];
}
$json_data['height'] = $data['height'];
$json_data['width'] = $data['width'];
$json_data['width_t'] = $data['width_t'];
$json_data['scroll'] = !empty($data['scroll']) ? 1 : 0;
$json_data['drag'] =  !empty($data['drag']) ? 1 : 0;
$json_data['drag_m'] =  !empty($data['drag_m'])  ? 1 : 0;
?>
<div data-map='<?php esc_attr_e(wp_json_encode($json_data)) ?>' class="ptb_extra_map ptb_extra_<?php echo $args['key'] ?>"></div>