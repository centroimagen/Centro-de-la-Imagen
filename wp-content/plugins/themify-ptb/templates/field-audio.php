<?php
/**
 * Template to display Audio field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-audio.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

if ( empty( $meta_data[ $args['key'] ] ) ) {
	return;
} else if ( is_string( $meta_data[ $args['key'] ] ) ) {
	$meta_data[ $args['key'] ] = array( 'url' => array( $meta_data[ $args['key'] ] ) );
}

$post_data = array();
if ( ! empty( $meta_data[ $args['key'] ]['url'] ) ) {
    $post_data = array_filter( $meta_data[ $args['key'] ]['url'] );
}
?>
<?php if (!empty($post_data)): ?>
    <div class="ptb_extra_video ptb_extra_grid  ptb_extra_columns_<?php echo $data['columns'] ?>">
        <?php foreach ($post_data as $index => $value): ?>
            <?php
            if (!$value) {
                continue;
            }
            $title = !empty($meta_data[$args['key']]['title'][$index]) ? esc_html($meta_data[$args['key']]['title'][$index]) : '';
            $description = !empty($meta_data[$args['key']]['description'][$index]) ? esc_html($meta_data[$args['key']]['description'][$index]) : '';
            ?>
            <div class="ptb_extra_item ptb_extra_audio_item">
                <div class="ptb_extra_audio_overlay_wrap">
                    <audio controls><source src="<?php echo $value ?>"></audio>
                    <div class="ptb_extra_audio_description"><?php echo $description ?></div>
                </div>
                <?php if ( '' !== $title ) : ?><h3 class="ptb_extra_audio_title"><?php echo $title ?></h3><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>