<?php
/**
 * Post type template edit or add form
 *
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
?>

<div id="themify_builder_lightbox_parent"></div>
<div class="wrap ptb-wrapper">

    <?php $this->page_title(); ?>

    <?php
    if (isset($_REQUEST['action'])) {
        if ($_REQUEST['action'] === 'add' && !empty($_REQUEST['slug']) && $this->options->has_custom_post_type($_REQUEST['slug']) && isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] === admin_url('admin.php?page=ptb-cpt&action=add')) {
            $slug = esc_attr($_REQUEST['slug']);
            $post_type = $this->options->get_custom_post_type($slug);
            if (isset($post_type)) {
                $lang = PTB_Utils::get_current_language_code();
                printf('<div id="message" class="updated below-h2"><p>%s</p></div>', sprintf(__('Post Type %s created. Now edit Archive and Single Template', 'ptb'), $post_type->singular_label[$lang])
                );
            }
        } elseif ($_REQUEST['action'] === 'edit') {
            if (!empty($_REQUEST['ptb-ptt']) && (isset($_REQUEST['settings-updated']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], admin_url('admin.php?page=ptb-ptt&action=add')) !== false))) {
                $t_id = esc_attr($_REQUEST['ptb-ptt']);
                $themplate = $this->options->get_post_type_template($t_id);
                if (isset($themplate)) {
                    $post_type = $this->options->get_custom_post_type($themplate['post_type']);
                    $lang = PTB_Utils::get_current_language_code();
                    if (isset($post_type)) {
                        $pre_build = false;
                        if (!isset($themplate['archive']) || !isset($themplate['archive']['layout'])) {
                            $pre_build = true;
                            $themplate['archive'] = PTB_Post_Type_Template::get_default_archive_template();
                        }
                        if (!isset($themplate['single']) || !isset($themplate['single']['layout'])) {
                            $pre_build = true;
                            $themplate['single'] = PTB_Post_Type_Template::get_default_single_template();
                        }
                        if ($pre_build) {
                            $this->options->option_post_type_templates[$t_id] = $themplate;
                            $this->options->update();
                        }
                        ?>
                        <div id="message" class="updated below-h2">
                            <p>
                                <?php _e('Template is done. Now go add new ', 'ptb') ?>
                                <a href="<?php echo admin_url('post-new.php?post_type=' . $post_type->slug) ?>">"<?php echo $post_type->singular_label[$lang]; ?>"</a>
                                <?php _e('posts', 'ptb') ?>
                            </p>
                        </div>
                        <?php
                    }
                }
            }
        }
    }
    ?>

    <form method="post" action="options.php">

        <?php settings_fields('ptb_plugin_options'); ?>
        <?php do_settings_sections($this->plugin_name . '-ptt') ?>

        <?php
        if (isset($_REQUEST['action']) && 'add' === $_REQUEST['action']) {
            submit_button(__('Save', 'ptb'));
        } else {
            submit_button(__('Update', 'ptb'));
        }
        ?>

    </form>
</div><!-- .wrap -->
