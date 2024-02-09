<?php 
/**
 * Template to display Icon field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-icon.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

$icons = $meta_data[$args['key']];
if ( is_string( $icons ) ) {
	$icons = array( 'icon' => array( $icons ), 'label' => array( '' ) );
}

 if (isset($icons['icon']) && !empty(array_filter($icons['icon']))): ?>
    <?php
    $size = isset($data['size']) ? 'ptb_extra_icons_' . $data['size'] : '';
    $classes = array();
    $new_window = false;
    $classes[] = 'ptb_extra_icon_link';
    if(isset($data['icon_link']) ){
        if($data['icon_link'] === 'lightbox') {
            $classes[] = 'ptb_lightbox';
        } elseif ($data['icon_link'] === 'new_window') {
            $new_window = true;
        } 
    }
    $classes = implode(' ', $classes);
    ?>
    <ul class="ptb_extra_icons <?php echo $size; ?>">
        <?php foreach ($icons['icon'] as $key => $ic) :
			$color = !empty($icons['color'][$key]) ? 'style="color:' . esc_attr($icons['color'][$key]) . ';"' : '';
			?>
            <li class="ptb_extra_icon">
                <?php if (!empty($icons['url'][$key])): ?>
                    <a <?php echo $color ?> class="<?php echo $classes ?>" <?php if ($new_window): ?>target="_blank"<?php endif; ?> href="<?php echo esc_url($icons['url'][$key]) ?>">
                        <span><?php echo PTB_Utils::get_icon( $ic ); ?></span>
                        <span class="ptb_extra_icon_label"><?php esc_attr_e($icons['label'][$key]) ?></span>
                    </a>
                <?php else: ?>
					<span <?php echo $color ?>><?php echo PTB_Utils::get_icon( $ic ); ?></span>
                    <span <?php echo $color ?> class="ptb_extra_icon_label"><?php esc_attr_e($icons['label'][$key]) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
