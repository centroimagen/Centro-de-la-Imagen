<?php
/**
 * Handle the display and saving of term meta fields
 *
 * @package PTB
 */

class PTB_Term_Meta {

	public static function init() {
		$taxonomies = get_taxonomies( array( 'public' => true ) );
		foreach ( $taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( __CLASS__, 'add_form_fields' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( __CLASS__, 'edit_form_fields' ), 10, 2 );
		}
		add_action( 'created_term', array( __CLASS__, 'save_fields' ), 10, 3 );
		add_action( 'edited_term', array( __CLASS__, 'save_fields' ), 10, 3 );
	}

	public static function add_form_fields( $taxonomy ) {
		$cmb_options = PTB::get_option()->get_ctx_cmb_options( $taxonomy );
        if ( empty( $cmb_options ) ) {
            return;
        }
        wp_nonce_field( 'ptb_meta_box', 'ptb_meta_box_nonce' );

		echo '<div class="ptb_post_cmb_wrapper">';

		foreach ( $cmb_options as $meta_box_id => $args ) {
			?>
			<div class="ptb_post_cmb_item_wrapper ptb_post_cmb_item_<?php echo $args['type'] ?>" data-ptb-cmb-type="<?php echo $args['type'] ?>"
				 id="<?php echo $meta_box_id ?>"
			 >
				<div scope="row" valign="top" class="ptb_post_cmb_title_wrapper">
					<h4 class="ptb_post_cmb_name"><?php echo PTB_Utils::get_label( $args['name'] ); ?></h4>
				</div>
				<div class="ptb_post_cmb_body_wrapper">
					<?php
					do_action( 'ptb_cmb_render', null, $meta_box_id, $args);
					do_action( 'ptb_cmb_render_' . $args['type'], null, $meta_box_id, $args );
					?>
					<p class="ptb_post_cmb_description"><?php echo PTB_Utils::get_label( $args['description'] ); ?></p>
				</div>
			</div>

			<?php
		}

		echo '</div>';
	}

	public static function edit_form_fields( $tag, $taxonomy ) {
		$cmb_options = PTB::get_option()->get_ctx_cmb_options( $taxonomy );
        if ( empty( $cmb_options ) ) {
            return;
        }
        wp_nonce_field( 'ptb_meta_box', 'ptb_meta_box_nonce' );

		foreach ( $cmb_options as $meta_box_id => $args ) {
			?>
			<tr class="ptb_post_cmb_item_wrapper ptb_post_cmb_item_<?php echo $args['type'] ?>" data-ptb-cmb-type="<?php echo $args['type'] ?>"
				 id="<?php echo $meta_box_id ?>"
			 >
				<th scope="row" valign="top" class="ptb_post_cmb_title_wrapper">
					<h4 class="ptb_post_cmb_name"><?php echo PTB_Utils::get_label( $args['name'] ); ?></h4>
				</th>
				<td class="ptb_post_cmb_body_wrapper">
					<?php
					do_action( 'ptb_cmb_render', $tag, $meta_box_id, $args);
					do_action( 'ptb_cmb_render_' . $args['type'], $tag, $meta_box_id, $args );
					?>
					<p class="ptb_post_cmb_description"><?php echo PTB_Utils::get_label( $args['description'] ); ?></p>
				</td>
			</tr>

			<?php
		}
	}

	/**
	 * Save custom fields when a term is edited
	 */
	public static function save_fields( $term_id, $taxonomy_term_id, $taxonomy ) {
		if ( ! isset( $_POST['ptb_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ptb_meta_box_nonce'], 'ptb_meta_box' ) ) {
			return;
		}

		$cmb_options = PTB::get_option()->get_ctx_cmb_options( $taxonomy );
        if ( empty( $cmb_options ) ) {
            return;
        }
		$name = PTB::get_plugin_name();
		foreach ( $cmb_options as $meta_key => $args ) {
			if ( isset( $_POST[ $meta_key ] ) ) {
				/* PTB fields are saved with a prefix in database */
				$wp_meta_key = sprintf( '%s_%s', $name, $meta_key );
				if ( empty( $_POST[ $meta_key ] ) && $_POST[ $meta_key ] != '0' ) {
					delete_term_meta( $taxonomy_term_id, $wp_meta_key );
				} else {
					if ( is_string( $_POST[ $meta_key ] ) ) {
						$_POST[ $meta_key ] = wp_encode_emoji( $_POST[ $meta_key ] );
					}
					// Update the meta field in the database.
					update_term_meta( $taxonomy_term_id, $wp_meta_key, $_POST[ $meta_key ] );
				}
			}
		}
	}
}