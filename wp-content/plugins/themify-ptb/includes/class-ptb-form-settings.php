<?php

class PTB_Form_Settings {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The options management class of the the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      PTB_Options $options Manipulates with plugin options
	 */
	protected $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param string $plugin_name
	 * @param string $version
	 * @param PTB_Options $options the plugin options instance
	 *
	 */
	public function __construct($plugin_name, $version, $options) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = $options;
	}

	public function add_settings_fields( $admin_slug ) {
		add_settings_section( $this->plugin_name . '-options', '', array( $this, 'display_options' ), $admin_slug );
		add_settings_section( $this->plugin_name . '-googlemaps', __( 'Google Maps', 'ptb' ), array( $this, 'google_maps' ), $admin_slug );
		add_settings_section( $this->plugin_name . '-customcss', __( 'Custom CSS', 'ptb' ), array( $this, 'custom_css' ), 'ptb-customcss' );
	}

	public function display_options() {
		$default_lang = PTB_Utils::get_default_language_code();
		$languages = $this->options->get_plugin_setting( 'languages' );
		$all_languages = PTB_Utils::get_all_languages( false );
		?>
		<h2><?php _e( 'Enabled Languages', 'ptb' ); ?></h2>
		<div class="ptb_option_languages">
			<?php foreach ( $all_languages as $code => $lang ) : ?>
				<div>
					<label><input type="checkbox" name="ptb_plugin_options[languages][<?php echo $code; ?>]"<?php echo isset( $languages[ $code ] ) || $code === $default_lang ? ' checked' : ''; ?><?php echo $code === $default_lang ? ' disabled' : ''; ?>><?php echo $lang['name']; ?></label>
				</div>
			<?php endforeach; ?>
			<?php /* site's default language is always enabled */ ?>
			<input type="hidden" name="ptb_plugin_options[languages][<?php echo $default_lang; ?>]" value="on">
		</div>
		<?php
	}

	public function custom_css() {
		$custom_css = $this->options->get_custom_css();
		?>
		<textarea name="ptb_plugin_options[<?php echo $this->plugin_name . '_css' ?>]" class="large-text" rows="25" style="direction: ltr;"><?php if ($custom_css): ?><?php echo esc_attr($custom_css); ?><?php endif; ?></textarea>    
		<?php
	}

	public function google_maps() {
		$value = $this->options->get_google_map_key();
		?>

		<label for="ptb_google_map_key"><?php _e( 'Google Map Key','ptb' )?></label>
		<div class="ptb_extra_map_input">
			<input class="regular-text" type="text" name="ptb_plugin_options[ptb_google_map_key]" id="ptb_google_map_key" value="<?php echo $value ?>"/>
			<br/>
			<small><?php _e('Google API key is required to use PTB Map module', 'ptb')?>. </small> <a href="//developers.google.com/maps/documentation/javascript/get-api-key#key" target="_blank"><?php _e('Generate Api key', 'ptb' )?></a>
		</div>
		<?php
	}

	/**
	 * @param array $input The inputs array of custom taxonomy
	 *
	 * @since    1.0.0
	 */
	public function process_options( $input ) {
		$css = sanitize_text_field( $input[ $this->plugin_name . '_css' ] );
		$this->options->set_custom_css( $css );
		unset( $input[ $this->plugin_name . '_css' ] );

		if ( isset( $input['ptb_google_map_key'] ) ) {
			$map_key = sanitize_text_field( $input['ptb_google_map_key'] );
            update_option( 'ptb_google_map_key', $map_key, false );
			unset( $input['ptb_google_map_key'] );
		} else {
			delete_option( 'ptb_google_map_key' );
		}

		$settings = $input;

		$this->options->set_plugin_settings( $settings );
	}
}