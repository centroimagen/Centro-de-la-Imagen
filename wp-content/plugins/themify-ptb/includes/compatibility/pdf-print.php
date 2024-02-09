<?php
/**
 * Provide compatibility with PDF & Print by BestWebSoft
 * @link https://wordpress.org/plugins/pdf-print/
 *
 * @since 1.5.3
 */

class PTB_PDF_Print {

    public static function get_instance() {
		static $instance = null;
		if ( $instance === null ) {
			$instance = new self;
		}
		return $instance;
    }

	private function __construct() {
		add_action( 'ptb_templates_menu', array( $this, 'ptb_templates_menu' ) );
		add_filter( 'bwsplgns_get_pdf_print_content', array( $this, 'bwsplgns_get_pdf_print_content' ) );
		add_filter( 'ptb_template_modules', array( $this, 'ptb_template_modules' ), 10, 3 );
	}

	/**
	 * Exclude 'Content' from showing up in the modules list since 'Content' is not rendered by PTB
	 *
	 * @return array
	 */
	function ptb_template_modules( $cmb_options, $type, $post_type ) {
		if ( $type === 'pdf' ) {
			unset( $cmb_options['editor'] );
		}

		return $cmb_options;
	}

	function bwsplgns_get_pdf_print_content( $content ) {
		$template = $this->get_pdf_template( get_post_type() );
		if ( ! $template ) {
			return $content;
		}

		/* render the PDF template in PTB */
		$renderer = new PTB_Form_PTT_Them( 'ptb', '1.0.0', 'pdf' );
		$post_taxonomies = $cmb_options = $post_support = array();
		PTB::get_option()->get_post_type_data( get_post_type(), $cmb_options, $post_support, $post_taxonomies );
		$post_id = get_the_id();
		$post_meta = array(
			'post_url' => get_permalink(),
			'taxonomies' => ! empty( $post_taxonomies ) ? wp_get_post_terms( $post_id, array_values( $post_taxonomies ) ) : array(),
		);
		$post_meta = array_merge( $post_meta, get_post_custom(), get_post( '', ARRAY_A ) );
		$ptb_content = $renderer->display_public_themplate( $template, $post_support, $cmb_options, $post_meta, get_post_type() );

		$ptb_content = $this->strip_tags( $ptb_content, '<h1><h2><h3><h4><h5><h6><p><strong><b><em><i><p>' );
		return $ptb_content . $content;
	}

	function ptb_templates_menu() {
		?>
		<a href="#" title="<?php _e( 'PDF Template', 'ptb' ); ?>"
		   data-template-type="<?php echo 'pdf'; ?>" id="ptb_ptt_edit_pdf" class="ptb_ptt_edit_button ptb_lightbox">
			<?php _e( 'Edit PDF Template', 'ptb' ); ?>
		</a>
		<?php
	}

	function get_pdf_template( $post_type ) {
		$templates = PTB::get_option()->get_templates_options();
		if ( ! empty( $templates ) ) {
			foreach ( $templates as $t ) {
				if ( $t['post_type'] === $post_type && isset( $t['pdf'] ) ) {
					return $t['pdf'];
				}
			}
		}

		return false;
	}

	/**
	 * Strips tags, but also removes the contents of the <script> and <style> tags
	 *
	 * @return string
	 */
	function strip_tags( $string ) {
		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
		$string = strip_tags( $string );

		return $string;
	}
}

class PTB_Form_PTT_PDF extends PTB_Form_PTT_Them {
	public function add_fields( $data = array() ) {}
}