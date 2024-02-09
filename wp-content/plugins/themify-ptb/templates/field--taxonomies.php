<?php
/**
 * Taxonomies field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--taxonomies.php
 *
 * @var string $type
 * @var array $args
 * @var array $data
 * @var array $meta_data
 * @var array $lang
 * @var boolean $is_single single page
 * @var string $index index in themplate
 *
 * @package Themify PTB
 */
?>

<?php if ( ! empty( $meta_data['taxonomies'] ) && isset( $data['taxonomies'] ) ) : ?>
    <?php $taxs = array();
    static $taxonomy = array();
    if( isset( $data['display_order'] ) && $data['display_order'] == 'by_name' && $data['order_direction'] === 'desc'){
        $meta_data['taxonomies'] = array_reverse($meta_data['taxonomies']);
    }
	$link = isset( $data['link'] ) && $data['link'] === 'no' ? false : true;
	$image = isset( $data['image'] ) && $data['image'] === 'no' ? false : true;
	$image_w = ! empty( $data['image_w'] ) ? (int) $data['image_w'] : 0;
	$image_h = ! empty( $data['image_h'] ) ? (int) $data['image_h'] : 0;
    ?>
    <?php foreach ($meta_data['taxonomies'] as $tax): ?>
        <?php if (isset($tax->taxonomy) && $data['taxonomies'] === $tax->taxonomy): ?>
            <?php
            $get_tax =!isset($taxonomy[$tax->taxonomy])? $this->options->get_custom_taxonomy($data['taxonomies']):$taxonomy[$tax->taxonomy];
            if ( $get_tax->ad_publicly_queryable && $link ){
                $term_link = get_term_link($tax, $tax->taxonomy);
                $taxs[$tax->term_id] = '<a href="' . $term_link . '">' . $tax->name . '</a>';
            }
            else{
                $taxs[$tax->term_id] =  $tax->name;
            }

			if ( $image ) {
				$term_image = PTB_Utils::get_meta_value( $tax, 'term_cover' );
				if ( is_array( $term_image ) && ! empty( $term_image[0] ) ) {
					$image = themify_ptb_get_image( $term_image[0], $image_w, $image_h );
					if ( $link ) {
						$image_link = ! empty( $term_image[2] ) ? $term_image[2] : $atts['href'];
						$image = sprintf( '<a href="%s">%s</a>', esc_url( $image_link ), $image );
					}
					$taxs[ $tax->term_id ] = '<div class="ptb_term_cover">' . $image . '</div>' . $taxs[ $tax->term_id ];
				}
			}
            ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ( ! empty( $taxs ) ) : ?>
        <?php
        if (!$data['seperator']) {
            $data['seperator'] = ', ';
        }
        if( isset( $data['display_order'] ) && $data['display_order'] == 'by_id'){
            if($data['order_direction'] === 'desc') {
                krsort($taxs, SORT_NUMERIC);
            } else {
                ksort($taxs, SORT_NUMERIC);
            }
        }

        ?>
        <div class="ptb_module_inline ptb_taxonomies_<?php echo str_replace('-', '_', $data['taxonomies']) ?>">
            <?php echo implode($data['seperator'], $taxs) ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
