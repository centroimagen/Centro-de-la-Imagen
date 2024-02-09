<?php
/**
 * Template to display Rating field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-rating.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

$readonly = !empty($args['readonly']);
$wp_meta_key = sprintf('%s_%s', $this->get_plugin_name(), $args['key']);
$vote = get_post_meta($meta_data['ID'], $wp_meta_key, true);
$is_array = $vote && is_array($vote);
if (!$readonly) {
    if (!$this->uid && is_user_logged_in()) {
        $this->uid = get_current_user_id();
    }
    if ( $is_array && (
		( $this->uid && ! empty( $vote['users'] ) && in_array( $this->uid, $vote['users'] ) )
		|| ( ! $this->ip || ! empty( $vote['ip'] ) && in_array( $this->ip, $vote['ip'], true ) ) )
	) {
        $readonly = true;
    }
}

if ($is_array) {
    $value = $vote['count'] > 0 ? floatval( $vote['total'] / $vote['count']) : 0;
} elseif ($readonly && isset($meta_data[$args['key']])) {
    $value = $meta_data[$args['key']];
} else {
    $value = 0;
}
$id = str_replace('-', '_', $args['key'] . '_' . $index);
$value = round($value);
$size = isset($data['size']) ? $data['size'] : 'small';
$icon = !empty($data['icon']) ? $data['icon'] : 'fa-star';
$vcolor = !empty($data['vcolor']) ? $data['vcolor'] : false;
$hcolor = !$readonly && !empty($data['hcolor']) ? $data['hcolor'] : false;

?> 
<div <?php if( empty($args['schema']) ): ?>itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating" <?php endif; ?>
     data-key="<?php echo $args['key'] ?>"
     data-post="<?php echo $meta_data['ID'] ?>" 
     data-id="<?php echo $id ?>" 
     <?php if (!empty($data['before_confirmation_text'][$lang])): ?>
         data-before="<?php esc_attr_e($data['before_confirmation_text'][$lang]) ?>"
     <?php endif; ?>
     <?php if (!empty($data['after_confirmation_text'][$lang])): ?>
         data-after="<?php esc_attr_e($data['after_confirmation_text'][$lang]) ?>"
     <?php endif; ?>
     data-vcolor="<?php echo $vcolor ?>" 
     data-hcolor="<?php echo $hcolor ?>" 
     class="<?php if ($readonly): ?>ptb_extra_readonly_rating <?php endif; ?>ptb_extra_rating ptb_extra_rating_<?php echo $size ?>">
         <?php for ($i = $args['stars_count']; $i > 0; --$i): ?>

		<span class="<?php echo $value >= $i ? ' ptb_extra_voted' : '' ?>"><?php echo PTB_Utils::get_icon( $icon ); ?></span>

    <?php endfor; ?>
	<?php if( empty($args['schema']) ): ?>
    <meta itemprop="ratingValue" content="<?php echo $value > 0 ? ($value > 5 ? 5 : $value) : 1 ?>"/>
    <meta itemprop="ratingCount" content="<?php echo !$readonly && !empty($vote['count']) ? $vote['count'] : 1 ?>"/>
	<?php endif; ?>
</div>
<?php if (!$readonly && isset($data['show_vote'])): ?>
    <p class="ptb_extra_vote_count">( <?php echo !empty($vote['count']) ? $vote['count'] : 0 ?> )</p>
<?php endif;