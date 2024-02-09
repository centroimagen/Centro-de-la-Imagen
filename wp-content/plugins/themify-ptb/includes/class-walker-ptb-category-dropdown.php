<?php
/**
 * Walker_CategoryDropdown with support for multiple selected items
 * Same as Walker_CategoryDropdown however adds support for $args['selected'] as array
 *
 * @package PTB
 */

class Walker_PTB_CategoryDropdown extends Walker_CategoryDropdown {

	/** documented in wp-includes/class-ptb-walker-category-dropdown.php */
	public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		// Restores the more descriptive, specific name for use within this method.
		$category = $data_object;
		$pad      = str_repeat( '&nbsp;', $depth * 3 );

		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = apply_filters( 'list_cats', $category->name, $category );

		if ( isset( $args['value_field'] ) && isset( $category->{$args['value_field']} ) ) {
			$value_field = $args['value_field'];
		} else {
			$value_field = 'term_id';
		}

		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $category->{$value_field} ) . '"';

		if ( in_array( (string) $category->{$value_field}, (array) $args['selected'] ) ) {
			$output .= ' selected="selected"';
		}
		$output .= '>';
		$output .= $pad . $cat_name;
		if ( $args['show_count'] ) {
			$output .= '&nbsp;&nbsp;(' . number_format_i18n( $category->count ) . ')';
		}
		$output .= "</option>\n";
	}
}