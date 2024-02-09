<?php
/**
 * Template to display Accordion field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-accordion.php
 *
 * @since 1.4.3
 * @author Themify
 * @package PTB Extra Fields
 */

if ( !empty($meta_data[$args['key']]) && count( array_filter($meta_data[$args['key']]['title']) ) > 0 ):

    $accordion_titles = $meta_data[$args['key']]['title'];
    $accordion_bodies = $meta_data[$args['key']]['body'];

    ?>
    <div class="ptb_extra_accordion ptb_extra_<?php echo $args['key']; ?>">
    <?php foreach ($accordion_titles as $key => $accodion): ?>
		<?php if ( empty($accodion) ) continue; ?>
        <div class="ptb_accordion_title"><?php esc_html_e( $accodion ); ?></div>
        <div class="ptb_accordion_panel"><?php if ( !empty( $accordion_bodies[$key] ) ) { echo $accordion_bodies[$key]; } ?></div>
    <?php endforeach; ?>
    </div>
<?php endif;