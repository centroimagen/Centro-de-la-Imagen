<?php $type = sanitize_text_field($_REQUEST['type']);
      $slug = sanitize_key($_REQUEST['slug']);
      $is_registered = $type==='cpt'?$this->options->is_custom_post_type_registered($slug):$this->options->is_custom_taxonomy_registered($slug);
      $confirm =  $type==='cpt'?__('All posts and template will be deleted. Do you want to delete this?', 'ptb'):__('All terms be deleted. Do you want to delete this?', 'ptb');
?>
<form class="ptb_remove_dialog_form" action="" method="POST">
    <p><?php _e('WARNING: Removing the post type can not be undone. It will remove its posts and templates as well. Choose unregister if you want to retrieve it later.','ptb')?></p>
    <input type="hidden" value="<?php echo wp_create_nonce($this->plugin_name . '_remove_'.$slug); ?>" name="nonce"/>
    <input type="hidden" value="<?php echo $type?>" name="type" />
    <input type="hidden" value="<?php echo $slug?>" name="slug" />
    <input type="hidden" value="<?php echo $this->plugin_name ?>_ajax_remove" name="action" />
    <input type="hidden" value="0" name="remove" />
    <input type="submit" data-confirm="<?php esc_attr_e($confirm)?>" class="ptb_left button button-primary" value="<?php echo $type==='cpt'?__('Remove posts as well','ptb'):__('Remove terms as well','ptb')?>"/>
    <input type="submit" <?php if(!$is_registered):?>disabled="disabled"<?php endif;?>class="ptb_right button button-primary"value="<?php echo $type==='cpt'?__('Unregister the post type','ptb'):__('Unregister the taxonomy','ptb')?>"/>
    <div class="ptb_alert busy"></div>
</form>
