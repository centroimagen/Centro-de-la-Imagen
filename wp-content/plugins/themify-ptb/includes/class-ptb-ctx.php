<?php

/**
 * Custom Taxonomy class.
 *
 * This class helps to create custom taxonomy arguments
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom Taxonomy class.
 *
 * This class helps to create custom taxonomy arguments
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Custom_Taxonomy {

    // Constants
    const ID = 'id';
    const SLUG = 'slug';
    const SINGULAR_LABEL = 'singular_label';
    const PLURAL_LABEL = 'plural_label';
    const ATTACH_TO = 'attach_to';
    const IS_HIERARCHICAL = 'is_hierarchical';
    // Build in post types to attach
    const POST_TYPE_PAGE = 'page';
    const POST_TYPE_POST = 'post';
    // Custom Labels
    const CL_SEARCH_ITEMS = 'search_items';
    const CL_POPULAR_ITEMS = 'popular_items';
    const CL_ALL_ITEMS = 'all_items';
    const CL_PARENT_ITEM = 'parent_item';
    const CL_PARENT_ITEM_COLON = 'parent_item_colon';
    const CL_EDIT_ITEM = 'edit_item';
    const CL_UPDATE_ITEM = 'update_item';
    const CL_ADD_NEW_ITEM = 'add_new_item';
    const CL_NEW_ITEM_NAME = 'new_item_name';
    const CL_SEPARATE_ITEMS_WITH_COMMAS = 'separate_items_with_commas';
    const CL_ADD_OR_REMOVE_ITEMS = 'add_or_remove_items';
    const CL_CHOOSE_FROM_MOST_USED = 'choose_from_most_used';
    const CL_MENU_NAME = 'menu_name';
    // Advanced options
    const AD_PUBLICLY_QUERYABLE = 'publicly_queryable';
    const AD_SHOW_UI = 'show_ui';
    const AD_SHOW_TAG_CLOUD = 'show_tag_cloud';
    const AD_SHOW_ADMIN_COLUMN = 'show_admin_column';
    const AD_UNREGISTE = 'unregister';
    const AD_SHOW_IN_REST = 'show_in_rest';
    const AD_REWRITE_SLUG = 'rewrite_slug';
    const AD_REWRITE_FRONT = 'with_front';

    // Private properties
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
    // Public properties
    public $id;
    public $slug;
    public $singular_label;
    public $plural_label;
    public $attach_to;
    public $is_hierarchical;
    public $search_items;
    public $popular_items;
    public $all_items;
    public $parent_item;
    public $parent_item_colon;
    public $edit_item;
    public $update_item;
    public $add_new_item;
    public $new_item_name;
    public $separate_items_with_commas;
    public $add_or_remove_items;
    public $choose_from_most_used;
    public $menu_name;
    public $ad_publicly_queryable;
    public $ad_show_ui;
    public $ad_show_tag_cloud;
    public $ad_show_admin_column;
    public $unregister = false;
    public $ad_show_in_rest;
    public $rewrite_slug;
    public $with_front = true;

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

        $this->set_defaults();
    }

    /**
     * Set default values of the properties
     *
     * @since 1.0.0
     */
    private function set_defaults() {

        $this->id = '';
        $this->slug = '';

        $this->singular_label = array();
        $this->plural_label = array();
        $this->attach_to = array();
        $this->is_hierarchical = true;

        // Set custom labels default values
        $this->search_items = array();
        $this->popular_items = array();
        $this->all_items = array();
        $this->parent_item = array();
        $this->parent_item_colon = array();
        $this->edit_item = array();
        $this->update_item = array();
        $this->add_new_item = array();
        $this->new_item_name = array();
        $this->separate_items_with_commas = array();
        $this->add_or_remove_items = array();
        $this->choose_from_most_used = array();
        $this->menu_name = array();

        // Set advanced options default values;
        $this->ad_publicly_queryable = true;
        $this->ad_show_ui = true;
        $this->ad_show_tag_cloud = true;
        $this->ad_show_admin_column = true;
        $this->unregister = false;
        $this->ad_show_in_rest = true;
        $this->with_front = true;
    }

    /**
     * Returns the args array of custom taxonomy for registration
     *
     * @since   1.0.0
     *
     * @return array The array of arguments for registration
     */
    public function get_args() {

        $singular_label = PTB_Utils::get_label($this->singular_label);
        $plural_label = PTB_Utils::get_label($this->plural_label);
        $search_items = PTB_Utils::get_label($this->search_items);
        $popular_items = PTB_Utils::get_label($this->popular_items);
        $all_items = PTB_Utils::get_label($this->all_items);
        $parent_item = PTB_Utils::get_label($this->parent_item);
        $parent_item_colon = PTB_Utils::get_label($this->parent_item_colon);
        $edit_item = PTB_Utils::get_label($this->edit_item);
        $update_item = PTB_Utils::get_label($this->update_item);
        $add_new_item = PTB_Utils::get_label($this->add_new_item);
        $new_item_name = PTB_Utils::get_label($this->new_item_name);
        $separate_ = PTB_Utils::get_label($this->separate_items_with_commas);
        $add_or_remove_items = PTB_Utils::get_label($this->add_or_remove_items);
        $most_used = PTB_Utils::get_label($this->choose_from_most_used);
        $menu_name = PTB_Utils::get_label($this->menu_name);
        $low_plural_label = strtolower($plural_label);

        $labels = array(
            'name' => $plural_label,
            'singular_name' => $singular_label,
            // Custom labels
            'search_items' => PTB_Utils::safe_sprintf($search_items, $plural_label),
            'popular_items' => PTB_Utils::safe_sprintf($popular_items, $plural_label),
            'all_items' => PTB_Utils::safe_sprintf($all_items, $plural_label),
            'parent_item' => PTB_Utils::safe_sprintf($parent_item, $singular_label),
            'parent_item_colon' => PTB_Utils::safe_sprintf($parent_item_colon, $singular_label),
            'edit_item' => PTB_Utils::safe_sprintf($edit_item, $singular_label),
            'update_item' => PTB_Utils::safe_sprintf($update_item, $singular_label),
            'add_new_item' => PTB_Utils::safe_sprintf($add_new_item, $singular_label),
            'new_item_name' => PTB_Utils::safe_sprintf($new_item_name, $singular_label),
            'separate_items_with_commas' => PTB_Utils::safe_sprintf($separate_, $low_plural_label),
            'add_or_remove_items' => PTB_Utils::safe_sprintf($add_or_remove_items, $low_plural_label),
            'choose_from_most_used' => PTB_Utils::safe_sprintf($most_used, $low_plural_label),
            'menu_name' => PTB_Utils::safe_sprintf($menu_name, $singular_label),
        );

        $args = array(
            'labels' => $labels,
            'public' => $this->ad_publicly_queryable,
            'hierarchical' => $this->is_hierarchical,
            'show_ui' => $this->ad_show_ui,
            'show_tagcloud' => $this->ad_show_tag_cloud,
            'show_admin_column' => $this->ad_show_admin_column,
            'unregister'=>$this->unregister,
			'show_in_rest' => $this->ad_show_in_rest,
			'rewrite' => [
				'with_front' => $this->with_front,
			],
        );

		if ( ! empty( $this->rewrite_slug ) ) {
			$args['rewrite']['slug'] = $this->rewrite_slug;
		}

        return $args;
    }

    /**
     * Serialization of custom taxonomy class for storing in WP options.
     * This function mainly used by PTB_Options class.
     *
     * @since   1.0.0
     *
     * @return array Serialized array of custom taxonomy
     */
    public function serialize() {

        $args = array(
            self::ID => $this->id,
            self::SLUG => $this->slug,
            self::SINGULAR_LABEL => $this->singular_label,
            self::PLURAL_LABEL => $this->plural_label,
            self::ATTACH_TO => array_values($this->attach_to),
            self::IS_HIERARCHICAL => $this->is_hierarchical,
            // Custom Labels
            self::CL_SEARCH_ITEMS => $this->search_items,
            self::CL_POPULAR_ITEMS => $this->popular_items,
            self::CL_ALL_ITEMS => $this->all_items,
            self::CL_PARENT_ITEM => $this->parent_item,
            self::CL_PARENT_ITEM_COLON => $this->parent_item_colon,
            self::CL_EDIT_ITEM => $this->edit_item,
            self::CL_UPDATE_ITEM => $this->update_item,
            self::CL_ADD_NEW_ITEM => $this->add_new_item,
            self::CL_NEW_ITEM_NAME => $this->new_item_name,
            self::CL_SEPARATE_ITEMS_WITH_COMMAS => $this->separate_items_with_commas,
            self::CL_ADD_OR_REMOVE_ITEMS => $this->add_or_remove_items,
            self::CL_CHOOSE_FROM_MOST_USED => $this->choose_from_most_used,
            self::CL_MENU_NAME => $this->menu_name,
            // Advanced options
            self::AD_PUBLICLY_QUERYABLE => $this->ad_publicly_queryable,
            self::AD_SHOW_UI => $this->ad_show_ui,
            self::AD_SHOW_TAG_CLOUD => $this->ad_show_tag_cloud,
            self::AD_SHOW_ADMIN_COLUMN => $this->ad_show_admin_column,
            self::AD_UNREGISTE=>$this->unregister,
            self::AD_SHOW_IN_REST=>$this->ad_show_in_rest,
            self::AD_REWRITE_SLUG => $this->rewrite_slug,
            self::AD_REWRITE_FRONT => $this->with_front,
        );

        return $args;
    }

    /**
     * De-serialization of custom taxonomy class from options.
     * This function mainly used by PTB_Options class and
     * should be called right after constructor.
     *
     * @since   1.0.0
     *
     * @param array $source Serialized options of custom taxonomy
     *
     */
    public function deserialize($source) {

        if (isset($source[self::ID])) {
            $this->id = $source[self::ID];
        }

        if (isset($source[self::SLUG])) {
            $this->slug = $source[self::SLUG];
        }

        if (isset($source[self::SINGULAR_LABEL])) {
            $this->singular_label = $source[self::SINGULAR_LABEL];
        }

        if (isset($source[self::PLURAL_LABEL])) {
            $this->plural_label = $source[self::PLURAL_LABEL];
        }

        if (isset($source[self::ATTACH_TO])) {
            $this->attach_to = $source[self::ATTACH_TO];
        }

        if (isset($source[self::IS_HIERARCHICAL])) {
            $this->is_hierarchical = $source[self::IS_HIERARCHICAL];
        }

        // Custom Labels

        if (isset($source[self::CL_SEARCH_ITEMS])) {
            $this->search_items = $source[self::CL_SEARCH_ITEMS];
        }

        if (isset($source[self::CL_POPULAR_ITEMS])) {
            $this->popular_items = $source[self::CL_POPULAR_ITEMS];
        }

        if (isset($source[self::CL_ALL_ITEMS])) {
            $this->all_items = $source[self::CL_ALL_ITEMS];
        }

        if (isset($source[self::CL_PARENT_ITEM])) {
            $this->parent_item = $source[self::CL_PARENT_ITEM];
        }

        if (isset($source[self::CL_PARENT_ITEM_COLON])) {
            $this->parent_item_colon = $source[self::CL_PARENT_ITEM_COLON];
        }

        if (isset($source[self::CL_EDIT_ITEM])) {
            $this->edit_item = $source[self::CL_EDIT_ITEM];
        }

        if (isset($source[self::CL_UPDATE_ITEM])) {
            $this->update_item = $source[self::CL_UPDATE_ITEM];
        }

        if (isset($source[self::CL_ADD_NEW_ITEM])) {
            $this->add_new_item = $source[self::CL_ADD_NEW_ITEM];
        }

        if (isset($source[self::CL_NEW_ITEM_NAME])) {
            $this->new_item_name = $source[self::CL_NEW_ITEM_NAME];
        }

        if (isset($source[self::CL_SEPARATE_ITEMS_WITH_COMMAS])) {
            $this->separate_items_with_commas = $source[self::CL_SEPARATE_ITEMS_WITH_COMMAS];
        }

        if (isset($source[self::CL_ADD_OR_REMOVE_ITEMS])) {
            $this->add_or_remove_items = $source[self::CL_ADD_OR_REMOVE_ITEMS];
        }

        if (isset($source[self::CL_CHOOSE_FROM_MOST_USED])) {
            $this->choose_from_most_used = $source[self::CL_CHOOSE_FROM_MOST_USED];
        }

        if (isset($source[self::CL_MENU_NAME])) {
            $this->menu_name = $source[self::CL_MENU_NAME];
        }

        // Advanced options

        if (isset($source[self::AD_PUBLICLY_QUERYABLE])) {
            $this->ad_publicly_queryable = $source[self::AD_PUBLICLY_QUERYABLE];
        }

        if (isset($source[self::AD_SHOW_UI])) {
            $this->ad_show_ui = $source[self::AD_SHOW_UI];
        }

        if (isset($source[self::AD_SHOW_TAG_CLOUD])) {
            $this->ad_show_tag_cloud = $source[self::AD_SHOW_TAG_CLOUD];
        }

        if (isset($source[self::AD_SHOW_ADMIN_COLUMN])) {
            $this->ad_show_admin_column = $source[self::AD_SHOW_ADMIN_COLUMN];
        }
        if (isset($source[self::AD_UNREGISTE])) {
            $this->unregister = $source[self::AD_UNREGISTE];
        }
		if (isset($source[self::AD_SHOW_IN_REST])) {
            $this->ad_show_in_rest = $source[self::AD_SHOW_IN_REST];
        }
		if (isset($source[self::AD_REWRITE_SLUG])) {
            $this->rewrite_slug = $source[self::AD_REWRITE_SLUG];
        }
		if (isset($source[self::AD_REWRITE_FRONT])) {
            $this->with_front = $source[self::AD_REWRITE_FRONT];
        }
    }

    /**
     * Check whether taxonomy attached to post type
     *
     * @since 1.0.0
     *
     * @param string $post_type
     *
     * @return bool
     */
    public function is_attached_to_post_type($post_type) {

        return in_array($post_type, $this->attach_to,true);
    }

    /**
     * Attach to or detach from post type based on $state
     *
     * @since 1.0.0
     *
     * @param string $post_type
     * @param bool $state
     */
    public function attach_to_post_type($post_type, $state) {

        if (true === $state) {

            PTB_Utils::add_to_array($post_type, $this->attach_to);
        } else {

            PTB_Utils::remove_from_array($post_type, $this->attach_to);
        }
    }

	/**
	 * Get admin URL to taxonomy's edit screen
	 *
	 * @return string
	 */
	public function get_edit_url() {
		return admin_url( 'admin.php?page=ptb-ctx&action=edit&slug=' . $this->slug );
	}
}
