<?php
/**
 * Link Button field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-link_button.php
 *
 * @var string $type
 * @var array $args
 * @var array $data
 * @var array $meta_data
 * @var array $lang
 * @var boolean $is_single single page
 *
 * @package Themify PTB
 */
?>

<?php
if (!empty($meta_data)) {
	$meta_data = $meta_data[$args['key']];
	if ( is_string( $meta_data ) ) {
		$meta_data = array( $meta_data, $meta_data );
	}
	if (!isset($meta_data[1]) || !trim($meta_data[1])) {
		return;
	}
	$class = $style = array();
	$none = true;
	if (!empty($data['custom_color'])) {
		$style[] = 'background-color:' . $data['custom_color'] . ' !important;';
	} elseif (isset($data['color'])) {
		$class[] = $data['color'];
	}
	if (isset($data['link_link']) && $data['link_link'] === 'lightbox') {
		$class[] = 'ptb_lightbox';
	}
	if (!empty($data['size'])) {
		$class[] = $data['size'];
	}
	if (!empty($data['styles'])) {
		if (!is_array($data['styles'])) {
			$data['styles'] = array($data['styles']);
		}
		$none = !in_array('none', $data['styles'],true);
		$class[] = $none ? implode(' ', $data['styles']) : 'none';
	}
	if (!empty($data['text_color']) ) {
		$style[] = 'color:' . $data['text_color'] . ' !important;';
	}
	if (!$meta_data[0] && isset($data['default_link'])) {
		$meta_data[0] = PTB_Utils::get_label($data['default_link']);
	}
	?>
	<div class="ptb_link">
		<a
			<?php if ($none && !empty($style)): ?>style="<?php echo implode(' ', $style) ?>"<?php endif; ?> 
			class="ptb_link_button <?php if ($none && !empty($class)): ?>shortcode <?php esc_attr_e(implode(' ', $class)) ?><?php endif; ?>" 
			<?php if (isset($data['link_link']) && $data['link_link'] === 'new_window'): ?> target="_blank"<?php endif; ?> 
			<?php if (!empty($data['nofollow'])): ?> rel="nofollow"<?php endif; ?> 
			href="<?php echo esc_url($meta_data[1]) ?>" 
			area-label="<?php echo esc_attr( $meta_data[0] ); ?>"
		>
			<?php if ( ! empty( $data['icon'] ) ) : ?>
				<?php echo PTB_CMB_Base::get_icon( $data['icon'], 'inline' ); ?>
			<?php endif; ?>
			<?php echo $meta_data[0] ?>
		</a>
	</div>
	<?php
}