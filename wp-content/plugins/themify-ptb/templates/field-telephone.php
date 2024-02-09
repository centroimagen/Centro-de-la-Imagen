<?php
/**
 * Template to display Telephone field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-telephone.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

if ( empty( $meta_data[ $args['key'] ] ) ) {
    return;
}
?>
<a href="tel:<?php echo esc_attr( $meta_data[ $args['key'] ] ); ?>" class="ptb_extra_telephone ptb_extra_<?php echo $args['key'] ?>"><?php echo empty($data['placement']) ? $meta_data[ $args['key'] ] : trim( $data['placement'] );?></a>

