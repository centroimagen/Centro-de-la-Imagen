<?php

/**
 * Post Type Template class.
 *
 * This class helps to manipulate with Post Type Templates
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Post Type Template class.
 *
 * This class helps to manipulate with Post Type Templates
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Post_Type_Template {

    const ID = 'id';
    const NAME = 'name';
    const POST_TYPE = 'post_type';
    const ARCHIVE = 'archive';
    const SINGLE = 'single';

    /**
     * The last css grid classname of arhive themplate
     *
     * @since    1.0.0
     * @access   public
     * @var      string $gridclass
     */
    public static $gridclass = false;

    /**
     * The css grid counter e.g grid3,grid
     *
     * @since    1.0.0
     * @access   public
     * @var      int $gridcounter
     */
    public static $gridcounter = 0;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The id of post type template.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $id The id of post type template.
     */
    private $id;

    /**
     * The name of template.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $name The name of post type template.
     */
    private $name;

    /**
     * The post type of template.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $post_type The post type of template.
     */
    private $post_type;

    /**
     * Archive template settings
     *
     * @since    1.0.0
     * @access   private
     * @var      array $archive archive template settings.
     */
    private $archive;

    /**
     * Single template settings
     *
     * @since    1.0.0
     * @access   private
     * @var      array $single single template settings.
     */
    private $single;

    /**
     * Custom templates
     */
    private $custom;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->archive = array();
        $this->single = array();
        $this->custom = array();
    }

    /**
     * Serialization of post type template class for storing in WP options.
     * This function mainly used by PTB_Options class.
     *
     * @since   1.0.0
     *
     * @return array Serialized array of post type template
     */
    public function serialize($ptt_id = false) {

		$data = array(
            self::NAME => $this->name,
            self::POST_TYPE => $this->post_type,
            self::ARCHIVE => $this->archive,
            self::SINGLE => $this->single,
		);

		if ( ! empty( $this->custom ) ) {
			foreach ( $this->custom as $name => $layout ) {
				$data[ PTB_Form_PTT_Custom::$prefix . $name ] = $layout;
			}
		}

        return apply_filters( 'ptb_template_serialize', $data, $ptt_id );
    }

    /**
     * De-serialization of post type template class from options.
     * This function mainly used by PTB_Options class and
     * should be called right after constructor.
     *
     * @since   1.0.0
     *
     * @param array $source Serialized options of post type template
     *
     */
    public function deserialize($source) {
        $this->name = isset($source[self::NAME]) ? $source[self::NAME] : '';
        $this->post_type = isset($source[self::POST_TYPE]) ? $source[self::POST_TYPE] : '';
        $this->archive = isset($source[self::ARCHIVE]) ? $source[self::ARCHIVE] : array();
        $this->single = isset($source[self::SINGLE]) ? $source[self::SINGLE] : array();

		$custom_prefix = PTB_Form_PTT_Custom::$prefix;
		foreach ( $source as $key => $value ) {
			if ( substr( $key, 0, strlen( $custom_prefix ) ) === $custom_prefix ) {
				$name = substr( $key, strlen( $custom_prefix ) );
				$this->custom[ $name ] = $value;
			}
		}
    }

    /**
     * Getter of template id
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Setter of template id
     *
     * @since 1.0.0
     *
     * @param string $id
     */
    public function set_id($id) {
        $this->id = $id;
    }

    /**
     * Getter of template name
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Setter of template name
     *
     * @since 1.0.0
     *
     * @param string $name
     */
    public function set_name($name) {
        $this->name = $name;
    }

    /**
     * Setter of template post type
     *
     * @since 1.0.0
     * @return string
     */
    public function get_post_type() {
        return $this->post_type;
    }

    /**
     * Getter of template post type
     *
     * @since 1.0.0
     *
     * @param string $post_type
     */
    public function set_post_type($post_type) {
        $this->post_type = $post_type;
    }

    /**
     * @return array
     */
    public function get_archive() {
        return $this->archive;
    }

    /**
     * @param array $archive
     */
    public function set_archive($archive) {
        $this->archive = $archive;
    }

    /**
     * @return array
     */
    public function get_single() {
        return $this->single;
    }

    /**
     * @param array $single
     */
    public function set_single($single) {
        $this->single = $single;
    }

    /**
     * @return bool
     */
    public function has_archive() {
        return !empty($this->archive);
    }

    /**
     * @return bool
     */
    public function has_single() {
        return !empty($this->single);
    }

    /**
     * @return bool
     */
    public function has_custom_templates() {
        return !empty($this->custom);
    }

	public function set_custom_templates( $value ) {
		$this->custom = $value;
	}

	/**
	 * Returns a custom template
	 */
	public function get_custom_templates() {
		return $this->custom;
	}

	/**
	 * Returns a custom template
	 */
	public function get_custom_template( $name ) {
		if ( isset( $this->custom[ $name ] ) ) {
			return $this->custom[ $name ];
		}

		return false;
	}

	/**
	 * Default template for archive display
	 *
	 * @return string
	 */
	public static function get_default_archive_template() {
		return array(
			'layout' => array(
				0 => array(
					'1-1-0' => array(
						0 => array(
							'type' => 'title',
							'key' => 'title',
							'title_tag' => '2',
							'title_link' => '1',
							'text_before' =>
							array(
								'en' => '',
							),
							'text_after' =>
							array(
								'en' => '',
							),
							'css' => '',
						),
						1 => array(
							'type' => 'excerpt',
							'key' => 'excerpt',
							'excerpt_count' => '',
							'can_be_empty' => '1',
							'text_before' =>
							array(
								'en' => '',
							),
							'text_after' =>
							array(
								'en' => '',
							),
							'css' => '',
						),
					),
				),
			),
			'ptb_ptt_layout_post' => 'grid4',
			'ptb_ptt_offset_post' => '',
			'ptb_ptt_orderby_post' => 'date',
			'ptb_ptt_order_post' => 'desc',
			'ptb_ptt_pagination_post' => true,
		);
	}

	/**
	 * Default template for single display
	 *
	 * @return string
	 */
	public static function get_default_single_template() {
		return array(
			'layout' => array(
				0 => array(
					'1-1-0' => array(
						0 => array(
							'type' => 'title',
							'key' => 'title',
							'title_tag' => '1',
							'title_link' => '0',
							'text_before' =>
							array(
								'en' => '',
							),
							'text_after' =>
							array(
								'en' => '',
							),
							'css' => '',
						),
						1 => array(
							'type' => 'editor',
							'key' => 'editor',
							'editor' => '',
							'css' => '',
						),
					),
				),
			),
			'ptb_ptt_navigation_post' => '1',
		);
	}

	/**
	 * Get admin URL to template's edit screen
	 *
	 * @return string
	 */
	public function get_edit_url() {
		return admin_url( 'admin.php?page=ptb-ptt&action=edit&ptb-ptt=' . $this->id );
	}
}