<?php
/**
 * Image field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-image.php
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

$url = $img_id = false;
$alt = $title = '';
$value = $meta_data[ $args['key'] ];
if ( ! empty( $value[0] ) ) {
	if ( is_numeric( $value[0] ) ) {
		$url = wp_get_attachment_url( $value[0] );
		$img_id = $value[0];
	} else {
		$url = $value[0];
	}
} elseif ( ! empty( $value[1] ) ) {
	$url = $value[1];
}

if ( $img_id === false ) {
	$img_id = themify_ptb_get_attachment_id_from_url( $url );
}
        
$url = PTB_CMB_Base::ptb_resize( $url, $data['width'], $data['height'] );
if( $url ) :
?>
<div class="ptb_image">
    <?php
		if($img_id){
			$title = get_the_title($img_id);
			$alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
			if(!$alt && $title){
				$alt = $title;
			}
			elseif(!$title && $alt){
				$title = $alt;
			}
		}
		$link = ! empty( $value[2] ) ? esc_url( $value[2] ) : false;
		$link = ! $link && ! empty( $data['custom_url'] ) ? esc_url( $data['custom_url'] ) : $link;
		$link = ! $link && isset( $data['permalink'] ) ? $meta_data['post_url'] : $link;
		$output = sprintf( '<img src="%s" alt="%s" title="%s"/>', $url,esc_attr($alt),esc_attr($title) );
		if ( $link ) {
			$output = sprintf( '<a href="%s">%s</a>', $link, $output );
		}
		echo '<figure class="ptb_post_image tf_clearfix">' . $output . '</figure>';
    ?>
</div>
<?php endif; ?>