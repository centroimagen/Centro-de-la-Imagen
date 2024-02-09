<?php
/**
 * Template to display Gallery field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-gallery.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

if ( empty( $data['layout'] ) ) {
	$data['layout'] = 'grid'; /* default display as grid */
}
if (!empty($meta_data[$args['key']]) && !empty(array_filter($meta_data[$args['key']]['url'])) ): ?>
    <?php
    $class = array();
    if ($data['layout'] !== 'showcase') {
        $class[] = 'ptb_extra_columns_' . $data['columns'];
        if ( isset($data['masonry']) ) {
            $class[] = 'ptb_extra_gallery_masonry';
        }
    }
    $class[] = 'ptb_extra_' . $data['layout'];
    if (isset($data['assign'])) {
        if (!is_array($data['assign'])) {
            $data['assign'] = array($data['assign']);
        }
        foreach ($data['assign'] as $styles) {
            $styles = str_replace('-', '_', $styles);
            $class[] = 'ptb_extra_' . $styles;
        }
    }
    $class = implode(' ', $class);
    $close_tag = true;
    $size = isset($data['size']) && $data['size'] !== 'f' ? $data['size'] : false;
    if ($size) {
        $width = get_option("{$size}_size_w");
        $height = get_option("{$size}_size_h");
    }
    ?>
    <div  class="<?php echo $class ?>">
        <?php if ($data['layout'] === 'showcase'): ?>
            <div class="ptb_extra_main_image"></div>
        <?php endif; ?>
        <div class="ptb_extra_gallery">
            <?php foreach ($meta_data[$args['key']]['url'] as $index => $slider): ?>
                <?php if ($slider): ?>
                    <?php
                    $title = !empty($meta_data[$args['key']]['title'][$index]) ? esc_attr($meta_data[$args['key']]['title'][$index]) : '';
                    $link = !empty($meta_data[$args['key']]['link'][$index]) ? esc_attr($meta_data[$args['key']]['link'][$index]) : '';
                    $description = !empty($meta_data[$args['key']]['description'][$index]) ? esc_textarea($meta_data[$args['key']]['description'][$index]) : ''
                    ?>
                    <div class="<?php if ($data['layout'] !== 'showcase'): ?>ptb_extra_item <?php endif; ?>ptb_extra_gallery_item">

                        <?php if ($data['layout'] === 'lightbox'): ?>
                            <a <?php if ($description): ?>title="<?php echo $title ?>"<?php endif; ?> data-rel="lightcase:collection:<?php echo $args['key'] ?>" href="<?php echo!empty($link) ? $link : $slider; ?>">
                            <?php elseif (!$is_single && $data['layout'] === 'grid' && !empty($data['link'])): ?>
                                <a <?php if ($data['link'] === 'lightbox'): ?>
                                        data-href="<?php echo admin_url('admin-ajax.php?id=' . $meta_data['ID'] . '&action=ptb_single_lightbox') ?>" class="ptb_open_lightbox"
                                    <?php elseif ($data['link'] === 'new_window'): ?>
                                        target="_blank"<?php endif; ?>  href="<?php echo $meta_data['post_url'] ?>">
                                    <?php else: ?>
                                        <?php $close_tag = false; ?>
                                    <?php endif; ?>
                                    <?php
                                    if ($size) {
                                        $slider = PTB_CMB_Base::ptb_resize($slider, $width, $height);
                                    }
                                    ?>
                                    <?php $data['layout'] === 'grid' && !empty($link) && printf('<a href="%s">', $link) && ( $close_tag = true ); ?>
                                <img class="ptb_image ptb_extra_icon"
                                     src="<?php echo $slider; ?>"
                                     alt="<?php echo $title ? esc_attr($title) : $description; ?>"
                                     title="<?php echo $title ? esc_attr($title) : $description; ?>"
                                     <?php $data['layout'] === 'showcase' && !empty($link) && printf('data-ptb-image-link="%s"', $link); ?> />
                                     <?php if ($close_tag): ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($data['layout'] !== 'showcase' && $description): ?>
                                <span class="ptb_extra_gallery_description">
                                    <?php echo $description ?>
                                </span>
                            <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
	</div>
<?php endif; ?>