<?php

/**
 * Custom meta box class to create Accordion
 *
 * @link       https://themify.me
 * @since      1.4.3
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Accordion extends PTB_CMB_Base {

	private $data = array();

	public function __construct($type, $plugin_name, $version) {
		parent::__construct($type, $plugin_name, $version);
	}

	public function get_assets() {
		return [
			'css' => [
				self::$plugin_name . '-' . $this->type => PTB::$uri . 'public/css/modules/accordion.css',
			],
		];
	}

	/**
	 * Adds the custom meta type to the plugin meta types array
	 *
	 * @since 1.4.3
	 *
	 * @param array $cmb_types Array of custom meta types of plugin
	 *
	 * @return array
	 */
	public function filter_register_custom_meta_box_type($cmb_types) {

		$cmb_types[$this->get_type()] = array(
			'name' => __('Accordion', 'ptb')
		);

		return $cmb_types;
	}

	/**
	 * Renders the meta boxes for themplates
	 *
	 * @since 1.4.3
	 *
	 * @param string $id the metabox id
	 * @param string $type the type of the page(Arhive or Single)
	 * @param array $args Array of custom meta types of plugin
	 * @param array $data saved data
	 * @param array $languages languages array
	 */
	public function action_them_themplate($id, $type, $args, $data = array(), array $languages = array()) {
		?>

		<?php
	}

	/**
	 * Renders the meta boxes on post edit dashboard
	 *
	 * @since 1.4.3
	 *
	 * @param WP_Post $post
	 * @param string $meta_key
	 * @param array $args
	 */
	public function render_post_type_meta($object, $meta_key, $args) {

		$value = PTB_Utils::get_meta_value( $object, $meta_key );
		$title_name = sprintf('%s[title][]', $meta_key);
		$body_name = sprintf('%s[body][]', $meta_key);
		?>
        <fieldset class="ptb_cmb_input">
            <ul id="<?php echo $meta_key; ?>_options_wrapper" class="ptb_cmb_options_wrapper">
				<?php $values = is_array($value) && isset($value['title']) ? $value['title'] : array($value); ?>
				<?php foreach ($values as $index => $v): ?>
					<?php
					$title =  !empty($value['title'][$index]) ? esc_attr($value['title'][$index]) : '';
					$body = !empty($value['body'][$index]) ? esc_textarea($value['body'][$index]) : '';
					?>

                    <li class="<?php echo $meta_key; ?>_option_wrapper ptb_cmb_option">
                        <span class="ptb_cmb_option_sort"><?php echo PTB_Utils::get_icon( 'ti-split-v' ); ?></span>
                        <input type="text" name="<?php echo $title_name; ?>"
                               value="<?php echo $title ?>" placeholder="<?php _e('Accordion Title', 'ptb') ?>" class="ptb_extra_row_margin"/>
                        <textarea name="<?php echo $body_name ?>" placeholder="<?php _e('Accordion Content', 'ptb') ?>"><?php echo $body ?></textarea>
                        <span class="<?php echo $meta_key; ?>_remove remove"><?php echo PTB_Utils::get_icon( 'ti-close' ); ?></span>
                    </li>
				<?php endforeach; ?>
            </ul>
            <div id="<?php echo $meta_key; ?>_add_new" class="ptb_cmb_option_add">
				<?php _e('Add new', 'ptb') ?>
            </div>
        </fieldset>
		<?php
	}

	/**
     *
     * @TODO add description later
	 *
	 * @since 1.4.3
     *
	 * @param $post_type
	 * @param array $args
	 * @param array $module
	 * @param $post
	 * @param $lang
	 * @param $languages
	 */
	public function ptb_submission_form($post_type, array $args, array $module, $post, $lang, $languages) {
		$data = isset( $post->ID ) ? PTB_Utils::get_meta_value( $post, $args['key'] ) : [];
		if ( empty($data['title'])) {
			$data = array('title' => array(false), 'body' => array(false));
			$title = array();
			$body = array();
		} else {
			if (!$data['title']) {
				$data['title'] = array();
			}
			if (!$data['body']) {
				$data['body'] = array();
			}
			$title = $this->ptb_submission_lng_data($data['title'], $args['key'], 'title', $post->ID, $post_type, $languages);
			$body = $this->ptb_submission_lng_data($data['body'], $args['key'], 'body', $post->ID, $post_type, $languages);
		}

		$max = !empty($module['max'])?(int)$module['max']:false;
		if($max===0){
			$max = false;
		}
		$i = 0;
		?>
        <div class="ptb_back_active_module_input ptb-submission-multi-text ptb_extra_submission_images ptb_extra_submission_accordion"<?php if($max):?> data-max="<?php echo $max?>"<?php endif;?>>
            <ul>
		        <?php foreach ($data['title'] as $k => $v): ?>
			        <?php if($max && $i>$max):?>
				        <?php break;?>
			        <?php endif;?>
			        <li class="ptb-submission-text-option">
                        <span title="<?php _e('Sort', 'ptb') ?>" class="ptb-submission-option-sort"></span>
				        <?php PTB_CMB_Base::module_language_tabs('submission', isset($title[$k]) ? $title[$k] : array(), $languages, $args['key'] . '_title', 'text', __('Accordion Title', 'ptb'), true); ?>
				        <?php PTB_CMB_Base::module_language_tabs('submission', isset($body[$k]) ? $body[$k] : array(), $languages, $args['key'] . '_body', 'textarea', __('Accordion Content', 'ptb'), true); ?>
                        <span title="<?php _e('Remove', 'ptb') ?>" class="ptb-submission-remove"></span>
                    </li>
			        <?php ++$i;?>
		        <?php endforeach; ?>
            </ul>
            <div class="ptb-submission-option-add">
				<span class="ptb_submission_add_icon"></span>
		        <?php _e('Add new', 'ptb') ?>
            </div>
			<?php if ( isset( $module['show_description'] ) ) : ?>
				<div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label( $args['description'] ); ?></div>
			<?php endif; ?>
        </div>
		<?php
	}

	/**
     *
     * @TODO add description later
     *
	 * @since 1.4.3
     *
     *
	 * @param $id
	 * @param array $args
	 * @param array $module
	 * @param array $post_support
	 * @param array $languages
	 */
	public function ptb_submission_themplate($id, array $args, array $module, array $post_support, array $languages = array()) {
	    ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_max"><?php _e('Maximum penals', 'ptb-submission') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb-submission-small-input">
                    <input type="number" name="[<?php echo $id ?>][max]" value="<?php echo !empty($module['max'])?(int)$module['max']:0 ?>" min="0"  id="ptb_<?php echo $id ?>_max" />
                    <small class="ptb-submission-small-description"><?php _e('Set "0" to unlimited penals','ptb-submission')?></small>
                </div>
            </div>
        </div>
    <?php

	}

	public function ptb_submission_validate($post_data, array $args, array $module, $post_type, $post_id, $lang, array $languages) {
		$error = false;
		$key = $module['key'];
		$data = $post_id && isset($post_data[$key]['f']) ? $post_data[$key]['f'] : array();
		$penals = isset($post_data[$key]['title']) ? $post_data[$key]['title'] : array();

		$this->data[$key]['title'] = array();
		$this->data[$key]['body'] = array();
		if (isset($post_data[$key . '_title'][$lang])) {
			$max = !empty($module['max'])?(int)$module['max']:false;
			if($max===0){
				$max = false;
			}
			if($max && count($post_data[$key . '_title'][$lang])>$max){
				$post_data[$key . '_title'][$lang] = array_slice($post_data[$key . '_title'][$lang], $max);
			}
			foreach ($post_data[$key . '_title'][$lang] as $k => $v) {
				$error = false;
				if ( empty($v) ) continue;
                foreach ($languages as $code => $lng) {
                    $this->data[$key]['body'][$code][$k] = isset($post_data[$key . '_body'][$code][$k]) ? sanitize_textarea_field($post_data[$key . '_body'][$code][$k]) : '';
                    $this->data[$key]['title'][$code][$k] = isset($post_data[$key . '_title'][$code][$k]) ? sanitize_text_field($post_data[$key . '_title'][$code][$k]) : '';
                }
			}
		}

		if (isset($module['required']) && empty($this->data[$key]['title'])) {
			return $error ? $error : sprintf( __('%s is required', 'ptb'), PTB_Utils::get_label($args['name']) );
		}
		if(empty($this->data[$key]['title'])){
			$this->data[$key]['title'] = array();
			$this->data[$key]['body'] = array();
		}
		return $this->data;
	}

	/**
     * @TODO add description later
     *
     * @since 1.4.3
     *
	 * @param array $m
	 * @param $key
	 * @param array $post_data
	 * @param $post_id
	 * @param $lng
	 *
	 * @return array
	 */
	public function ptb_submission_save(array $m, $key, array $post_data, $post_id, $lng) {
		return array('title' => !empty($this->data[$key]['title'][$lng]) ? $this->data[$key]['title'][$lng] : array(),
		             'body' =>isset($this->data[$key]['body'][$lng]) ? $this->data[$key]['body'][$lng] : array()
		);
	}

}
