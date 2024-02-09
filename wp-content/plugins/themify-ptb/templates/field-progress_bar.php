<?php
/**
 * Template to display Progress Bar field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-progress_bar.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

if (!empty($args['options'])): ?>
    <div class="ptb_extra_progress_<?php echo $args['orientation'] ?>">
        <?php foreach ($args['options'] as $opt): ?>
            <?php
            $value = isset($meta_data[$args['key']]) ? $meta_data[$args['key']] : FALSE;
            $value = $value && !empty($value[$opt['id']]) ? floatval($value[$opt['id']]) : 0;
            $hide_label = isset($data[$opt['id'] . '_hide']);
            ?>
            <div class="ptb_extra_progress_item">
                <?php if (!$hide_label && $args['orientation'] === 'horizonal'): ?>
                    <div class="ptb_extra_progress_bar_label"><?php echo PTB_Utils::get_label($opt); ?></div>
                <?php endif; ?>
                <div data-meterorientation="<?php echo $args['orientation'] ?>" 
                     data-barcolor="<?php echo isset($data[$opt['id'] . '_barcolor']) ? $data[$opt['id'] . '_barcolor'] : '' ?>"
                     data-raised="<?php echo $value ? floatval($value) : 0 ?>" 
                     <?php if (isset($data[$opt['id'] . '_display'])): ?>
                         data-displaytotal="1"
                     <?php endif; ?>
                     class="ptb_extra_progress_bar">
                </div>
                <?php if (!$hide_label && $args['orientation'] !== 'horizonal'): ?>
                    <div  class="ptb_extra_progress_bar_label"><?php echo PTB_Utils::get_label($opt); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
