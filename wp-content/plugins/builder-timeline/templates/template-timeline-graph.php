<?php
/**
 * Timeline TimelineJS template
 *
 * @var $items
 * @var $args['settings']
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


$data = array();
foreach( $args['settings']['items'] as $item ) {
    $date = date_parse(  $item['date'] );
    unset($date['hour'],$date['minute'],$date['second']);
	$item_data = array(
		'start_date' => $date,
		'text' => array(
		    'headline'=>isset( $item['link'] )
			? '<a href="' . $item['link'] . '">' . $item['title'] . '</a>'
			: $item['title']
		)
	    
	);
	if( $item['hide_featured_image'] ===false ) {
	    $item_data['media'] = array('url'=>$item['image'],'alt'=>$item['title'],'title'=>$item['title']);
	    if(isset( $item['link'] )){
		$item_data['media']['link'] = $item['link'];
	    }
	}
	if($item['hide_content']===false){
	    $item_data['text']['text']=$item['content'];
	}
	$data[] = $item_data;
}
?>
<div class="tf_w storyjs-embed" style="height:634px" id="timeline-embed-<?php echo $args['module_ID']; ?>" data-start-end="<?php echo $args['settings']['start_at_end'] === 'yes'?1:0?>" data-id="<?php echo $args['module_ID']; ?>" data-events="<?php esc_attr_e(json_encode( $data )); ?>">
    <div id="story-js-<?php echo $args['module_ID']?>"></div>
</div>