<?php
/**
 * Custom Image field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--custom_image.php
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

<?php if (!empty($data['image'])): ?>
	<?php
	$url = PTB_CMB_Base::ptb_resize($data['image'], $data['width'], $data['height']);
        $alt = $title = '';
        $img_id = themify_ptb_get_attachment_id_from_url($data['image']);
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
	?>
	<figure class="ptb_post_image tf_clearfix">
		<?php
		if (!empty($data['link'])): echo '<a href="' . $data['link'] . '">';
		endif;
		?>
		<img src="<?php echo $url ?>" alt="<?php esc_attr_e($alt)?>" title="<?php esc_attr_e($title)?>"/>
		<?php
		if (!empty($data['link'])): echo '</a>';
		endif;
		?>
	</figure>
<?php endif; ?>
