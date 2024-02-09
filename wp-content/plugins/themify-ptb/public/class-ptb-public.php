<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    PTB
 * @subpackage PTB/public
 * @author     Themify <ptb@themify.me>
 */
class PTB_Public {

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
	 * Copy of the original $wp_query
	 *
	 * @type WP_Query
	 */
	private $query;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    private static $options = false;
    private static $template = false;
	/* true only on single post pages of PTB's post types */
    public static $is_single = false;
    private static $output = '';
    public static $shortcode = false;
    private static $is_disabled = false;
    private static $post_ids = array();
    private $post;
    private $url;

	/**
	 * Flag used for sorting by event_date field type
	 */
    protected static $date_sort_field = false;

    /**
     * Creates or returns an instance of this class.
     *
     * @return	A single instance of this class.
     */
    public static function get_instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self;
        }
        return $instance;
    }

    private function __construct() {
        $this->plugin_name = PTB::$plugin_name;
        $this->version = PTB::get_version();
        self::$options = PTB::get_option();
    }

	/**
	 * Setup the hooks
	 */
	public function init() {
        add_action( 'wp_head', array( $this, 'ptb_filter_wp_head' ) );
        add_action( 'body_class', array( $this, 'ptb_filter_body_class' ) );
        add_filter( 'pre_get_posts', array( $this, 'ptb_filter_cpt_category_archives' ), 99, 1 );
		add_action( 'pre_get_posts', array( $this,'custom_post_author_archive' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99);

		add_filter( 'template_include', array( $this, 'template_include' ) );

        add_shortcode( $this->plugin_name, array( $this, 'ptb_shortcode' ) );
		add_shortcode( $this->plugin_name .'_taxonomy', array( $this, 'display_taxonomy_hierarchy' ) ) ;
		add_shortcode( $this->plugin_name .'_field', array( $this, 'ptb_field_shortcode' ) ) ;
        add_filter( 'widget_text', array( $this, 'widget_text'), 10, 2 );
        add_filter( 'widget_text_content', array( $this, 'get_ptb_shortcode' ), 12 );
        add_filter( 'widget_posts_args', array( $this, 'disable_ptb'), 10, 1 );
		add_filter( 'tb_creative_works_items', array( $this, 'add_to_creative_works'), 10, 1 );
		add_filter( 'posts_orderby', array( __CLASS__, 'order_by_date' ), 10, 2 );

        // #6316 issue Fix
        add_filter( 'redirect_canonical', array( $this, 'pif_disable_redirect_canonical' ) );
	}

	public function template_include( $template ) {
		global $wp_query;

        $types = array_keys( self::$options->get_custom_post_types() );
		if ( empty( $types ) || ! isset( $wp_query->post ) ) {
			return $template;
		}

		$post_type = get_post_type();
		/* PTB template missing, use default template from the theme */
		if ( ! self::$options->get_post_type_template_by_type( $post_type ) ) {
			return $template;
		}

		self::$is_single = is_singular( $types );

		if ( self::is_lightbox() ) {
			add_filter( 'body_class', [ $this, 'ptb_lightbox_body_class' ] );
			/* disable Builder Pro templates in PTB lightbox */
			if ( class_exists( 'Tbp_Public' ) ) {
				remove_action( 'template_include', array( 'Tbp_Public', 'template_include' ), 15 );
			}
			return $this->locate_template( 'lightbox.php', 'lightbox' );
		}

        $tax = array_keys( self::$options->get_custom_taxonomies() );
		if (
			( ! empty( $tax ) && is_tax( $tax ) )
			|| is_post_type_archive( $types )
			|| self::$is_single
		) {

			if ( class_exists( 'Tbp_Public' ) ) {
				$location = self::$is_single ? 'single' : 'archive';
				/* a Builder Pro template is active, don't use PTB template */
				if ( ! empty( Tbp_Public::get_location( $location ) ) ) {
					return $template;
				}
			}

			/* set the template file from the active theme, with fallback */
			foreach ( [
				'get_page_template',
				'get_singular_template',
				'get_index_template'
			] as $template_getter ) {
				$template = call_user_func( $template_getter );
				if ( $template ) {
					break;
				}
			}

			$title = self::$is_single ? '' : get_the_archive_title();

			$this->query = clone $wp_query;
			$this->post = clone $wp_query->post;

			do_action( 'ptb_template_before_render', $this );
			$output = self::$is_single ? $this->get_single_template() : $this->get_archive_template();

			/* create dummy post and change the global query */
			$wp_query->posts_per_page       = 1;
			$wp_query->nopaging             = true;
			$wp_query->post_count           = 1;
			$wp_query->is_page              = false;
			$wp_query->is_archive           = ! self::$is_single;
			$wp_query->is_category          = false;
			$wp_query->is_tax               = $this->query->is_tax( $tax );
			$wp_query->is_single            = self::$is_single;
			$wp_query->is_singular          = self::$is_single;
			$wp_query->post                 = new WP_Post( new stdClass() );
			$wp_query->post->ID             = 0;
			$wp_query->post->filter         = 'raw';
			$wp_query->post->post_title     = $title;
			$wp_query->post->post_content   = $output;
			$wp_query->post->comment_status = 'closed';
			$wp_query->post->ping_status    = 'closed';
			$wp_query->post->post_type      = $post_type;
			$wp_query->posts                = array( $wp_query->post );

			$this->disable_wpautop();

			/* add Edit links to admin bar */
			add_action( 'wp_before_admin_bar_render', array( $this, 'wp_before_admin_bar_render' ) );

			if ( self::$is_single ) {
				add_filter( 'get_edit_post_link', [ $this, 'get_edit_post_link' ], 10, 3 );
				/* disable post title display outside of PTB loop */
				add_filter( 'the_title', array( $this, 'disable_title' ), 100, 2 );
				add_filter( 'comments_template', array( $this, 'disable_comments' ), 100 );
			}
		}

		return $template;
	}

	/**
	 * Check whether a PTB lightbox is currently showing
	 *
	 * @return bool
	 */
	public static function is_lightbox() {
		return isset( $_GET['ptb_lightbox'] ) && $_GET['ptb_lightbox'] === '1';
	}

	/**
	 * Renders the page output for archive pages
	 *
	 * @return string
	 */
	function get_archive_template() {
		$output = '';
		$post_type = get_post_type();
		$template = self::$options->get_post_type_template_by_type( $post_type );
		if ( $template && $template->has_archive() ) {
			$template = $template->get_archive();
			$style = $template[ self::$options->prefix_ptt_id . 'layout_post' ];
			$masonry = ! empty( $template[ self::$options->prefix_ptt_id . 'masonry' ] );
			$paginate = ! empty( $template[ self::$options->prefix_ptt_id . 'pagination_post' ] );
			$archive_description = get_the_archive_description();
			if ( $archive_description ) {
				$output .= '<div class="ptb_archive_description">' . $archive_description . '</div>';
			}
			$output .= $this->ptb_shortcode( [
				'pagination' => $paginate,
				'style' => $style,
				'masonry' => $masonry,
				'query' => $this->query,
				'ptb_main_query' => true, /* flag, used by PTB addons */
			] );
		}

		return $output;
	}

	/**
	 * Renders the template for single post type pages
	 *
	 * @return string
	 */
	function get_single_template() {
		$output = $this->ptb_shortcode( [
			'query' => $this->query,
			'ptb_main_query' => true,
		] );

		return $output;
	}

	/**
	 * Add a unique body class when PTB post is displayed in lightbox
	 *
	 * @return array
	 */
	function ptb_lightbox_body_class( $classes ) {
		$classes[] = 'ptb_in_lightbox';

		return $classes;
	}

	/**
     * @param $atts {
     *
     *  @type string $type               Custom post type
     *  @type string $tax                taxonomy assigned to custom post type
     *  @type int $children_level        how many children terms level is allowed.
     *  @type int $starting_parent       starting terms level. for terms with multiple children levels
     *  @type string $tag                wrapping html tag. <ul> or <ol>
     *  @type bool $hyperlinks           hyperlink of term. true or false
     *  @type string $order ASC|DESC     order of terms.
     *  @type bool $assigned_taxonomy    show only terms assigned to a post.
     *
     * }
     * @return string
     */
    public function display_taxonomy_hierarchy( $atts ) {
        $atts = shortcode_atts( array(
            'tax'              => '',
            'children_depth'   => '2',
            'starting_parent'  => '1',
            'tag'              => 'ul',
            'hyperlinks'       => '1',
            'order'            => 'ASC',
            'assigned_taxonomy'=> '0',
			'image'            => '0',
			'grid'             => 'list',
			'image_w'          => 0,
			'image_h'          => 0,
        ), $atts, $this->plugin_name. '_taxonomy' );

        $taxonomy         = $atts['tax'];
        $children_level   = (int) $atts['children_depth'];
        $starting_parent  = (int) $atts['starting_parent'] !== 0 ? (int) $atts['starting_parent'] : 1;
        $order            = $atts['order'];
        $assigned_taxonomy= intval( $atts['assigned_taxonomy'] );

        if ( empty($taxonomy) ) return '';

        $options = PTB::get_option();

		if ( ! class_exists( 'PTB_Walker_Category' ) ) {
			include dirname( plugin_dir_path( __FILE__ ) ) . '/includes/class-ptb-walker-category.php';
		}

        $args = array(
            'depth'               => intval( $children_level ),
            'echo'                => 0,
            'hide_empty'          => $assigned_taxonomy,
            'hide_title_if_empty' => true,
            'order'               => $order,
            'show_option_none'    => '',
            'style'               => 'list',
            'taxonomy'            => $taxonomy,
            'title_li'            => '',
			'walker'              => new PTB_Walker_Category(),
			'image'               => (bool) $atts['image'],
			'image_w'             => (int) $atts['image_w'],
			'image_h'             => (int) $atts['image_h'],
			'hyper_links'         => (bool) $atts['hyperlinks'],
        );

        $list = '';
        if ( $starting_parent > 1 ) {
            $terms = self::terms_hierarchy( $taxonomy, 0 , array(
					'hide_empty' => $assigned_taxonomy,
					'childless' => ! $children_level,
					'order' => $order
				)
			);
            $new_terms = array();
            $this->terms_depth($terms, 1, $starting_parent-1,$new_terms);
            unset($terms); // release memory. there maybe a lot of terms in it. 100's or even 1000's

            foreach ( $new_terms as $term ) {
                $args['child_of'] = $term->term_id;
                $list  .= wp_list_categories( $args );
            }

        } else {
            $list  = wp_list_categories( $args );
        }

        if ( ! empty( $list ) ) {
			$grid = $atts['grid'] === '' ? '' : ' ptb_loops_wrapper ptb_' . $atts['grid'];
			$list = '<' . $atts['tag'] . ' class="ptb_taxonomy_shortcode' . $grid . '">' . $list . '</' . $atts['tag'] . '>';
		}

        return $list;
    }

	/**
	 * [ptb_field] shortcode handler
	 *
	 * @param array $atts except for $atts['name'] this is passed to the PTB field template file as $data array
	 * @return string
	 */
	function ptb_field_shortcode( $atts, $content = '' ) {
		if ( $atts['name'] !== '' ) {
			$field_name = $atts['name'];
			unset( $atts['name'] );
			if ( ptb_get_field_definition( $field_name ) ) {
				return ptb_get_field( $field_name, $atts );
			} else {
				/**
				 * The custom field name is not recognized by PTB, return raw meta value
				 * so [ptb_field] can display non-PTB custom fields.
				 */
				return get_post_meta( get_the_ID(), $field_name, true );
			}
		}
	}

    /**
     * Recursively get taxonomy and its children. only for taxonomy shortcode.
     *
     * @param $taxonomy
     * @param int $parent
     * @param array $args
     * @return array
     */
    private static function terms_hierarchy ($taxonomy, $parent = 0 , $args = array() ) {

        $default = array( 'parent' => $parent );
        $temp = array();
        if ( !empty ($args) ) {
            $default = array_merge( $default, $args);
        }

        if ( isset ($default['order']) ) $temp['order'] = $default['order'];

        if ( isset ($default['hide_empty']) ) $temp['hide_empty'] = $default['hide_empty'];

        $terms = get_terms( $taxonomy, $default );
        $children = array();

        foreach ( $terms as $term ){
            // recurse to get the direct descendants of "this" term
            $term->children = self::terms_hierarchy( $taxonomy, $term->term_id, $temp );
            $children[ $term->term_id ] = $term;
        }
        return $children;
    }

    /**
     * Recursive function. only for taxonomy shortcode.
     *
     * @param $terms
     * @param $start
     * @param $depth
     * @param $new_terms
     */
    private function terms_depth($terms, $start, $depth, &$new_terms ) {
        if ($start === $depth) {
            foreach (  $terms as $term){
                if ( $term->children ){
                    $new_terms[] = $term;
                }
            }
            return;
        }

        foreach ($terms as $term) {
            if ( !empty( $term->children ) && $start < $depth) {
                $this->terms_depth($term->children, $start+1, $depth, $new_terms);
            }
        }
    }

    public function pif_disable_redirect_canonical($redirect_url) {
        if ( is_singular() ) {
			if ( get_query_var( 'paged' ) ) {
				$redirect_url = false;
			}
        }

        return $redirect_url;
    }

    /**
     * Shortcut to PTB_Template_Loader::locate_template()
     *
     * @return string|false
     */
    public function locate_template( $names, $type ) {
        return PTB_Template_Loader::locate_template( $names, $type );
    }

    /**
     * Register the Javascript/Stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        $this->url = $plugin_url = plugin_dir_url(__FILE__);
        $min_files = array(
            'css' => array(
                'lightbox' => PTB_Utils::enque_min( $this->url . 'css/lightbox.css',true )
            ),
            'js' => array()
        );
        $min_files = apply_filters( 'ptb_min_files', $min_files );
		if(empty( $min_files['js'])){
			unset( $min_files['js']);
		}
        $translation_ = array(
            'url' => $plugin_url,
            'ver' => $this->version,
            'min' => $min_files,
            'include' => includes_url('js/'),
			'is_themify_theme' => PTB_Utils::is_themify_theme() ? true : false,
			'jqmeter' => $plugin_url . 'js/jqmeter.min.js',
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'lng' => PTB_Utils::get_current_language_code(),
			'map_key' => self::$options->get_google_map_key(),
			'modules' => [
				'gallery' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/gallery.js' ),
					'selector' => '.ptb_extra_showcase, .ptb_extra_gallery_masonry .ptb_extra_gallery'
				],
				'map' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/map.js' ),
					'selector' => '.ptb_extra_map'
				],
				'progress_bar' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/progress_bar.js' ),
					'selector' => '.ptb_extra_progress_bar'
				],
				'rating' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/rating.js' ),
					'selector' => '.ptb_extra_rating'
				],
				'video' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/video.js' ),
					'selector' => '.ptb_extra_show_video'
				],
				'accordion' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/accordion.js' ),
					'selector' => '.ptb_extra_accordion'
				],
				'slider' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/slider.js' ),
					'selector' => '.ptb_slider'
				],
				'lightbox' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/lightbox.js' ),
					'selector' => '.ptb_lightbox, .ptb_extra_lightbox, .ptb_extra_video_lightbox'
				],
				'masonry' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/masonry.js' ),
					'selector' => '.ptb_masonry'
				],
				'post_filter' => [
					'js' => PTB_Utils::enque_min( $this->url . 'js/modules/post_filter.js' ),
					'selector' => '.ptb-post-filter'
				],
			]
        );

        wp_register_script($this->plugin_name, PTB_Utils::enque_min($plugin_url . 'js/ptb-public.js'), array( 'jquery' ), $this->version, false);
        wp_localize_script($this->plugin_name, 'ptb', $translation_);

        wp_register_style( $this->plugin_name . '-edit-template-link', PTB_Utils::enque_min( $plugin_url . 'css/ptb-edit-template-link.css' ), array(), $this->version, 'all' );
        wp_enqueue_style($this->plugin_name, PTB_Utils::enque_min($plugin_url . 'css/ptb-public.css'), array(), $this->version, 'all');
        wp_enqueue_script($this->plugin_name);
        add_filter('script_loader_tag', array($this, 'defer_js'), 11, 3);

		do_action( 'ptb_public_enqueue_scripts' );
    }

    public function ptb_filter_wp_head() {
        $option = PTB::get_option();
        $custom_css = $option->get_custom_css();
        if ($custom_css) {
            echo '<!-- PTB CUSTOM CSS --><style type="text/css">' , $custom_css , '</style><!--/PTB CUSTOM CSS -->';
        }
    }

    public function ptb_filter_body_class($classes) {
        $post_type = get_post_type();
        $templateObject = self::$options->get_post_type_template_by_type($post_type);
        if (is_null($templateObject)) {
            return $classes;
        }
        $type = self::$template ? 'archive' : (self::$is_single && $templateObject->has_single() && is_singular($post_type) ? 'single' : false);
        if ($type !== false) {
            $classes[] = $this->plugin_name . '_' . $type;
            $classes[] = $this->plugin_name . '_' . $type . '_' . $post_type;
        }
        return $classes;
    }

    /**
     * Enable shortcodes in Text widget
     *
     * @return string
     */
    function widget_text($text, $instance = array()) {
        global $wp_widget_factory;

        /* check for WP 4.8.1+ widget */
        if( isset( $wp_widget_factory->widgets['WP_Widget_Text'] ) && method_exists( $wp_widget_factory->widgets['WP_Widget_Text'], 'is_legacy_instance' ) && ! $wp_widget_factory->widgets['WP_Widget_Text']->is_legacy_instance( $instance ) ) {
            return $text;
        }

        /*
         * if $instance['filter'] is set to "content", this is a WP 4.8 widget,
         * leave it as is, since it's processed in the widget_text_content filter
         */
        if( isset( $instance['filter'] ) && 'content' === $instance['filter'] ) {
            return $text;
        }

        return $this->get_ptb_shortcode($text);
    }

    public function get_ptb_shortcode($text) {
        if ($text && has_shortcode($text, $this->plugin_name)) {
            $text = PTB_CMB_Base::format_text($text);
            $text = shortcode_unautop(do_shortcode($text));
        }
        return $text;
    }

    /**
	 * Fix 'posts_per_page' and 'order' query parameters where PTB template is applicable.
	 *
     * @param WP_Query $query
     *
     * @return WP_Query
     */
    public function ptb_filter_cpt_category_archives(&$query) {
        if (self::$shortcode || !empty($query->query['ptb_disable']) || self::$is_disabled || is_admin()) {
            return $query;
        }

        // apply the template only if it's archive of CPTs or Taxonomies registered by PTB
        $types = array_keys( self::$options->get_custom_post_types() );
        $tax = array_keys( self::$options->get_custom_taxonomies() );

        if (
			$query->is_main_query()
			&& ( is_post_type_archive() || is_category() || is_tag() || is_tax() )
			&& ( ! isset($query->query_vars['suppress_filters']) || $query->query_vars['suppress_filters'] )
		) {
            $post_type = false;
            if (isset($query->query['post_type']) && $query->is_post_type_archive()) {
                $args = array();
                $t = self::$options->get_post_type_template_by_type($query->query['post_type']);
                if ($t && $t->has_archive()) {
                    self::$template = $t;
                    $post_type = $query->query['post_type'];
                    $args[] = $post_type;
                }
            } elseif (!empty($query->tax_query->queries)) {
                $tax = $query->tax_query->queries;
                ksort( $tax );
                $tax = current($tax);
                $taxonomy = ! empty( $tax['taxonomy'] ) ? get_taxonomy( $tax['taxonomy'] ) : false;
                if ($taxonomy && !empty($tax['terms'])) {
                    $args = $taxonomy->object_type;
                    if ($args) {
                        self::$is_disabled = true;
                        $tmp_args = array(
                            'post_status' => 'publish',
                            'posts_per_page' => 1,
                            'no_found_rows' => true,
                            'orderby' => 'none',
                            'tax_query' => array($tax)
                        );
                        unset($tax);
                        foreach ($args as $type) {
                            $t = self::$options->get_post_type_template_by_type($type);
                            if ($t && $t->has_archive()) {
                                $tmp_args['post_type'] = $type;

                                $tmp_query = get_posts($tmp_args);
                                if (!empty($tmp_query)) {
                                    self::$template = $t;
                                    $post_type = $type;
                                    unset($tmp_query);
                                    break;
                                }
                            }
                        }
                        if ($post_type) {
                            wp_reset_postdata();
                        }
                        self::$is_disabled = false;
                    }
                }
            }
            if ($post_type) {
                $archive = self::$template->get_archive();

                if ($archive['ptb_ptt_pagination_post'] > 0) {
                    if ($archive['ptb_ptt_offset_post'] > 0) {
                        $query->set('posts_per_page', intval($archive['ptb_ptt_offset_post']));
                    }
                } else {
                    $query->set('nopaging', 1);
                    $query->set('no_found_rows', 1);
                }
                if (isset(PTB_Form_PTT_Archive::$sortfields[$archive['ptb_ptt_orderby_post']])) {
                    $query->set('orderby', $archive['ptb_ptt_orderby_post']);
                } else {
                    $cmb_options = self::$options->get_cpt_cmb_options($post_type);
                    if (isset($cmb_options[$archive['ptb_ptt_orderby_post']])) {
                        $sort = $cmb_options[$archive['ptb_ptt_orderby_post']]['type'] === 'number' && empty($cmb_options[$archive['ptb_ptt_orderby_post']]['range']) ? 'meta_value_num' : 'meta_value';
                        $query->set('orderby', $sort);
                        $query->set('meta_key', $this->plugin_name . '_' . $archive['ptb_ptt_orderby_post']);
                    }
                }
                $query->set('order', $archive['ptb_ptt_order_post']);
                $query->set('post_type', $args);
                if ($query->is_main_query()) {
                    $query->set('suppress_filters', false); // #7713 wpml
                }

                if ( ! isset(PTB_Form_PTT_Archive::$sortfields[$archive['ptb_ptt_orderby_post']]) && isset( $query->query['post_type'] ) ) {
					$options = PTB::get_option();
					$options = $options->get_cpt_cmb_options( $query->query['post_type'] );
					$order_by_key = $archive['ptb_ptt_orderby_post'];

					if(! isset( $options[$order_by_key] ) && ! $query->get( 'meta_key' ) && preg_match( '/_(?:start|end)$/', $order_by_key, $match ) && ! empty( $match[0] ) ) {
						$order_by_key = preg_replace( '/' . $match[0] . '$/', '', $order_by_key );
						self::$date_sort_field = strpos($match[0], '_start') !== FALSE ? 'start' : 'end';

						if( isset( $options[$order_by_key] ) && $options[$order_by_key]['type'] === 'event_date' ) {
							$query->set( 'orderby', 'meta_value' );
							$query->set( 'meta_key', PTB::$plugin_name . '_' . $order_by_key );
							$query->set( 'order_by_date', true );
							$query->set( 'suppress_filters', false );
						}
					} else if( $query->get( 'meta_key' ) && $options[$order_by_key]['type'] === 'event_date' ) {
						self::$date_sort_field = 'start';
						$query->set( 'order_by_date', true );
						$query->set( 'suppress_filters', false );
					}
                }

            }
        }

        return apply_filters( 'ptb_filter_cpt_category_archives', $query, self::$template );
    }

    /**
     * @since 1.0.0
     *
     * @param $atts
     *
     * @return string|void
     */
    public function ptb_shortcode( $atts ) {
        $post_types = isset( $atts['query'] ) ? (array) $atts['query']->query_vars['post_type'] : explode( ',', esc_attr( $atts['type'] ) );
        $type = current( $post_types );
        $template = self::$options->get_post_type_template_by_type($type);

        if ( null == $template ) {
            return $this->warning( sprintf( __( 'Missing template for %s post type. Please <a href="%s" target="blank">add a template</a>.', 'ptb' ), $type, admin_url( 'admin.php?page=ptb-ptt' ) ) );
        }

        unset($atts['type']);
        // WP_Query arguments
        $args = array(
			'query' => null, /* use custom WP_Query object */
            'orderby' => 'date',
            'order' => 'DESC',
            'post_type' => $type,
            'post_status' => 'publish',
            'nopaging' => false,
            'style' => 'list-post',
            'post__in' => isset($atts['ids']) && $atts['ids'] ? explode(',', $atts['ids']) : '',
            'posts_per_page' => isset($atts['posts_per_page']) && intval($atts['posts_per_page']) > 0 ? $atts['posts_per_page'] : get_option('posts_per_page'),
            'paged' => isset($atts['paged']) && $atts['paged'] > 0 ? intval($atts['paged']) : (is_front_page() ? get_query_var('page', 1) : get_query_var('paged', 1)),
            'logic' => 'AND',
            'not_found' => '',
			'template' => '',
        );

        if (isset($atts['offset']) && intval($atts['offset']) > 0) {
            $args['offset'] =(int)$atts['offset'];
        }
        if (isset($atts['ptb_widget'])) {
            unset($atts['ptb_widget']);
            $ptb_widget = true;
			if ( isset( $atts['paged'] ) && $atts['paged'] > 0 ) {
				$args['paged'] = intval( $atts['paged'] );
			} else if ( isset( $_GET['ptb_paged'] ) ) {
				$args['paged'] = intval( $_GET['ptb_paged'] );
			} else {
				$args['paged'] = 1;
			}
        }
        $args = apply_filters( 'ptb_shortcode_args', wp_parse_args( $atts, $args ) );
        $return = isset( $atts['return'] ) ? $atts['return'] : 'html';
        unset($atts);
		if ( $args['query'] === null ) {
			unset( $args['query'] );
			if (!$args['paged'] || !is_numeric($args['paged'])) {
				$args['paged'] = 1;
			}
			if (empty($args['pagination'])) {
				$args['no_found_rows'] = 1;
			}
			if (isset($args['post_id']) && is_numeric($args['post_id'])) {
				$args['p'] = $args['post_id'];
				$args['style'] = '';
			} else {
				$taxes = $conditions = $meta = array();
				$post_taxonomies = $cmb_options = $post_support = array();
				self::$options->get_post_type_data($type, $cmb_options, $post_support, $post_taxonomies);
				if (isset($post_support['category'])) {
					$post_taxonomies[] = 'category';
				}
				if (isset($post_support['post_tag'])) {
					$post_taxonomies[] = 'post_tag';
				}
				foreach ($args as $key => $value) {
					if (!is_array($value)) {
						$value = trim($value);
					}
					if ($value || $value == '0') {
						if (strpos($key, 'ptb_tax_') === 0) {
							$origk = str_replace('ptb_tax_', '', $key);
							if (in_array($origk, $post_taxonomies)) {
								$taxes[] = array(
									'taxonomy' => sanitize_key($origk),
									'field' => 'slug',
									'terms' => explode(',', $value),
									'operator' => !empty($args[$origk . '_operator']) ? $args[$origk . '_operator'] : 'IN',
									'include_children' => !empty($args[$origk . '_children']) ? false : true,
								);
							}
							unset($args[$key], $args[$origk . '_operator'], $args[$origk . '_children']);
						} elseif (strpos($key, 'ptb_meta_') === 0) {
							$origk = sanitize_key(str_replace('ptb_meta_', '', $key));
							if (!isset($cmb_options[$origk]) && strpos($origk, '_exist') !== false) {
								$origk = str_replace('_exist', '', $origk);
							} else {
								$origk = $origk.'*';
								$origk = str_replace('_to*', '', $origk);
								$origk = str_replace('_from*', '', $origk);
								$origk = str_replace('*', '', $origk);
							}
							if (isset($cmb_options[$origk]) || isset($args['ptb_meta_' . $origk . '_from']) || isset($args['ptb_meta_' . $origk . '_to'])) {
								if (!empty($args['ptb_meta_' . $origk . '_exist'])) {
									$meta[$origk] = array(
										'key' => 'ptb_' . $origk,
										'compare' => '!=',
										'value'=>''
									);
									$meta[$origk] = apply_filters('ptb_meta_' . $cmb_options[$origk]['type'] . '_exist', $meta[$origk], $origk,$cmb_options[$origk]);
								} else {
									$cmb = $cmb_options[$origk];
									$mtype = isset($args['ptb_meta_' . $origk . '_from']) || isset($args['ptb_meta_' . $origk . '_to']) ? 'number' : $cmb['type'];
									switch ($mtype) {
										case 'checkbox':
										case 'select':
										case 'radio_button':
											if (empty($cmb['options'])) {
												continue 2;
											}
											if ($mtype === 'select' || $mtype === 'checkbox') {
												$value = explode(',', $value);
												$value = array_map(function($val) { return '"'.$val.'"'; }, $value); // Similar to #7153
												$args['post__in'] = self::parse_multi_query($value, $type, $origk, $args['post__in']);

												if (!$args['post__in']) {
													return '';
												}
											} else {
												$temp_found = false;
												foreach($cmb['options'] as $temp_opt){
													if(in_array($value, $temp_opt)){
														$temp_found = true;
														break;
													}
												}
												if (!$temp_found) {
													return '';
												}
												$meta[$origk] = array(
													'key' => 'ptb_' . $origk,
													'compare' => '=',
													'value' => $value
												);
											}

											break;
										case 'text':
											$slike = !empty($args[$origk . '_slike']);
											$elike = !empty($args[$origk . '_elike']);

											if (!$cmb['repeatable']) {
												$meta[$origk] = array(
													'key' => 'ptb_' . $origk,
													'compare' => '=',
													'value' => $value
												);
												if ($slike && $elike) {
													$meta[$origk]['compare'] = 'LIKE';
												} elseif ($slike) {
													$meta[$origk]['compare'] = 'REGEXP';
													$meta[$origk]['value'] = '^' . $meta[$origk]['value'];
												} elseif ($elike) {
													$meta[$origk]['compare'] = 'REGEXP';
													$meta[$origk]['value'] = $meta[$origk]['value'] . '$';
												}
											} else {
												$post_id = self::parse_multi_query(explode(',', $value), $type, $origk, $args['post__in'], true);
												if (empty($post_id)) {
													return '';
												}
												foreach ($post_id as $i => $p) {
													$m = get_post_meta($p, 'ptb_' . $origk, true);
													if (empty($m)) {
														unset($post_id[$i]);
													} else {
														if (!is_array($m)) {
															$m = array($m);
														}
														if (!$slike && !$elike && !in_array($value, $m)) {// compare =
															unset($post_id[$i]);
														} else {//compare like %s%,%s or s%
															$find = false;
															$reg = $slike ? '/^' . $value . '/iu' : '/' . $value . '$/iu';
															foreach ($m as $m1) {
																if ($slike && $elike) {
																	if (strpos($m1, $value) !== false) {//compare  %s%
																		$find = true;
																		break;
																	}
																} else {
																	if (preg_match($reg, $m1)) {
																		$find = true;
																		break;
																	}
																}
															}
															if (!$find) {
																unset($post_id[$i]);
															}
														}
													}
												}
												if (empty($post_id)) {
													return '';
												}
												$args['post__in'] = $post_id;
											}
											unset($args[$origk . '_elike'], $args[$origk . '_slike']);
											break;
										case 'number':
											if (empty($cmb['range']) && !isset($meta[$origk])) {
												$from_val = isset($args['ptb_meta_' . $origk . '_from']) && is_numeric($args['ptb_meta_' . $origk . '_from']) ? $args['ptb_meta_' . $origk . '_from'] : false;
												$to_val = isset($args['ptb_meta_' . $origk . '_to']) && is_numeric($args['ptb_meta_' . $origk . '_to']) ? $args['ptb_meta_' . $origk . '_to'] : false;
												$from_sign = $from_val && !empty($args['ptb_meta_' . $origk . '_from_sign']) ? html_entity_decode($args['ptb_meta_' . $origk . '_from_sign'], ENT_QUOTES, 'UTF-8') : false;
												$to_sign = $to_val && $from_sign !== '=' && !empty($args['ptb_meta_' . $origk . '_to_sign']) ? html_entity_decode($args['ptb_meta_' . $origk . '_to_sign'], ENT_QUOTES, 'UTF-8') : false;
												$meta[$origk] = array(
													'key' => 'ptb_' . $origk,
													'compare' => '=',
													'value' => $from_val,
													'type' => 'DECIMAL'
												);
												if ($from_sign !== '=') {
													if ($from_sign === '>=' && $to_sign === '<=') {
														$meta[$origk]['compare'] = 'BETWEEN';
														$meta[$origk]['value'] = array($from_val, $to_val);
													} elseif ($from_sign === '>' || $from_sign === '>=') {
														$meta[$origk]['compare'] = $from_sign;
													}
													if ($to_sign === '<' || $to_sign === '<=') {
														$meta[$origk . '_to'] = $meta[$origk];
														$meta[$origk . '_to']['compare'] = $to_sign;
														$meta[$origk . '_to']['value'] = $to_val;
													}
												}
											}
											unset($args['ptb_meta_' . $origk . '_to_sign'], $args['ptb_meta_' . $origk . '_from_sign'], $args['ptb_meta_' . $origk . '_from'], $args['ptb_meta_' . $origk . '_to']);
											break;
										default:
											$meta[$origk] = array(
												'key' => 'ptb_' . $origk,
												'compare' => '=',
												'value' => $value
											);
											break;
									}
								}
							}
						} elseif (strpos($key, 'ptb_field_') === 0) {
							$origk = sanitize_key(str_replace(array('ptb_field_', '_exist', '_from', '_to'), array('', '', '', ''), $key));

							if (isset($post_support[$origk]) || isset($args['ptb_field_' . $origk . '_from']) || isset($args['ptb_field_' . $origk . '_to'])) {
								$slike = !empty($args[$origk . '_slike']) ? '%' : '';
								$elike = !empty($args[$origk . '_elike']) ? '%' : '';


								switch ($origk) {
									case 'thumbnail':
										$meta['field_' . $origk] = array(
											'key' => '_thumbnail_id',
											'compare' => '!=',
											'value'=>''
										);
										break;
									case 'title':
									case 'editor':
									case 'excerpt':

										if (!empty($args['ptb_field_' . $origk . '_exist'])) {
											if ($origk === 'editor') {
												$origk = 'content';
											}
											$conditions[$origk] = " `post_$origk` !='' ";
										} else {
											if ($origk === 'editor') {
												$origk = 'content';
											}
											$conditions[$origk] = '`post_' . $origk . '` LIKE ' . "'" . $slike . esc_sql($value) . $elike . "'";
										}
										break;
									case 'author':
										$args['author__in'] = explode(',', $value);
										break;
									case 'comment_count':

										if (!empty($args['ptb_field_' . $origk . '_exist'])) {
											$conditions[$origk] = "`comment_count`>'0'";
										} elseif (!isset($conditions[$origk])) {
											$query_comment = array();
											$from_val = isset($args['ptb_field_' . $origk . '_from']) && is_numeric($args['ptb_field_' . $origk . '_from']) ? (int) $args['ptb_field_' . $origk . '_from'] : false;
											$to_val = isset($args['ptb_field_' . $origk . '_to']) && is_numeric($args['ptb_field_' . $origk . '_to']) ? (int) $args['ptb_field_' . $origk . '_to'] : false;
											$from_sign = $from_val && !empty($args[$origk . '_from_sign']) ? html_entity_decode($args[$origk . '_from_sign'], ENT_QUOTES, 'UTF-8') : false;
											$to_sign = $to_val && $from_sign !== '=' && !empty($args[$origk . '_to_sign']) ? html_entity_decode($args[$origk . '_to_sign'], ENT_QUOTES, 'UTF-8') : false;

											if ($from_sign) {
												if (in_array($from_sign, array('>', '>=', '='))) {
													$query_comment[] = '`comment_count`' . $from_sign . "'" . $from_val . "'";
												}
											}
											if ($to_sign) {
												if (in_array($from_sign, array('>', '>='))) {
													$query_comment[] = '`comment_count`' . $to_sign . "'" . $to_val . "'";
												}
											}
											if (!empty($query_comment)) {
												$conditions[$origk] = implode(' AND ', $query_comment);
											}
										}
										break;
								}
								unset($args[$origk . '_elike'], $args[$origk . '_slike']);
							}
						}
					}
				}
				if (!empty($conditions)) {
					if (!empty($args['post__in'])) {
						$conditions[] = 'ID IN(' . implode(',', $args['post__in']) . ')';
					}
					$conditions = implode(' AND ', $conditions);
					global $wpdb;
					$result_query = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE `post_status`='publish' AND post_type='" . esc_sql($type) . "' AND $conditions");
					if (empty($result_query)) {
						return '';
					}
					$args['post__in'] = array();
					foreach ($result_query as $p) {
						$args['post__in'][] = $p->ID;
					}
				}
				if (!empty($taxes)) {
					$args['tax_query'] = $taxes;
					$args['tax_query']['relation'] = $args['logic'];
					unset($args['logic']);
				}
				if (!empty($meta)) {
					$args['meta_query'] = $meta;
					$args['meta_query']['relation'] = 'AND';
				}

				if (!isset(PTB_Form_PTT_Archive::$sortfields[$args['orderby']])) {
					$args['meta_key'] = 'ptb_' . $args['orderby'];
					/* when sorting by a Rating field type, use "{field}_rating" custom field instead which stores the overall rating of the post */
					if ( isset( $cmb_options[$args['orderby']]['type'] ) && $cmb_options[$args['orderby']]['type'] === 'rating' ) {
						$args['meta_key'] .= '_rating';
					} else if ( preg_match( '/(start|end)$/', $args['orderby'] ) ) {
						$meta_key = str_replace( array( '_start', '_end' ), '', $args['orderby'] );
						if ( isset( $cmb_options[ $meta_key ]['type'] ) && $cmb_options[ $meta_key ]['type'] === 'event_date' ) {
							self::$date_sort_field = strpos( $args['orderby'], '_start' ) !== FALSE ? 'start' : 'end';
							$args['meta_key'] = 'ptb_' . $meta_key;
						}
					}

					$args['orderby'] = isset($cmb_options[$args['orderby']]['type']) && $cmb_options[$args['orderby']]['type'] === 'number' && empty($cmb_options[$args['orderby']]['range']) ? 'meta_value_num' : 'meta_value';
				}
			}
			if (empty($args['offset'])) {
				unset($args['offset']);
			}

			// if 'return' parameter with value of 'query' is sent to the function,
			//  it will return the raw $args for WP_Query.
			if ( $return === 'query' ) {
				return $args;
			}
			// The Query
			$query = new WP_Query( apply_filters( 'themify_ptb_shortcode_query', $args ) );
		} else {
			$query = $args['query'];
		}

		global $post;
		if ( is_object( $post ) ) {
			$saved_post = clone $post;
		}

		self::$shortcode = true;

        // The Loop
        $html = '';
		$css_classes = [
			'ptb_loops_shortcode',
			'tf_clearfix',
		];
		if ( ! $query->is_single() ) {
			$css_classes[] = 'ptb_loops_wrapper';
			$css_classes[] = 'ptb_' . $args['style'];
			if ( ! empty( $args['masonry'] ) && $args['style'] !== 'list-post' ) {
				$css_classes[] = 'ptb_masonry';
			}
		}
		if ( isset( $args['ptb_main_query'] ) && $args['ptb_main_query'] ) {
			$css_classes[] = 'ptb_main_query';
		}

        if ( $query->have_posts() ) {
            $themplate = new PTB_Form_PTT_Them($this->plugin_name, $this->version);

			if ( ! empty( $args['template'] ) && $template->get_custom_template( $args['template'] ) ) {
				$themplate_layout = $template->get_custom_template( $args['template'] );
			} else {
				$themplate_layout = $query->is_singular() ? $template->get_single() : $template->get_archive();
			}

			if ( empty( $themplate_layout ) ) {
				return $this->warning( sprintf( __( 'Empty template. <a href="%s" target="blank">Manage Templates</a>.', 'ptb' ), $template->get_edit_url() ) );
			}

            $cmb_options = $post_support = $post_taxonomies = array();
            self::$options->get_post_type_data($type, $cmb_options, $post_support, $post_taxonomies);

            $html = '<div ' . $this->html_attr( apply_filters( 'themify_ptb_shortcode_attr', [ 'class' => join( ' ', $css_classes ), 'data-type' => $type ], $args ) ) . '>';

            $terms = array();
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_meta = array();
                $class = array('ptb_post', 'tf_clearfix');
                $post_meta['post_url'] = get_permalink();
                $post_meta['taxonomies'] = !empty($post_taxonomies) ? wp_get_post_terms($post_id, array_values($post_taxonomies)) : array();
                if (isset($args['post_filter']) && !empty($post_meta['taxonomies'])) {
                    foreach ($post_meta['taxonomies'] as $p) {
                        $class[] = 'ptb-tax-' . $p->term_id;
                        $terms[] = $p->term_id;
                    }
                }
                $post_meta = array_merge($post_meta, get_post_custom(), get_post('', ARRAY_A));
                $html .= '<article id="post-' . $post_id . '" class="' . implode(' ', get_post_class($class)) . '">';
                $html .= $themplate->display_public_themplate($themplate_layout, $post_support, $cmb_options, $post_meta, $type, self::$is_single);
                $html .= '</article>';
            }

            $html .= '</div><!-- .ptb_loops_wrapper -->';

            if ( isset( $args['pagination'] ) && $query->max_num_pages > 1 ) {
                $paginate_args = array(
                    'total' => $query->max_num_pages,
                    'current' => $args['paged'] === 0 ? 1 : $args['paged'],
					'end_size' => 3, /* How many numbers on either the start and the end list edges */
                );
                if ( isset($ptb_widget) ) {
                    $paginate_args['base'] = @add_query_arg('ptb_paged','%#%');
                    $paginate_args['format'] = '';
                }
                $html.='<div class="ptb_pagenav">';
                $html .= paginate_links( $paginate_args );
                $html.='</div>';
            }

            if ( isset( $args['post_filter'] ) && !isset($args['post_id']) && !empty($terms)) {
                $terms = array_unique($terms);
				$post_filter_taxonomies = '1' === $args['post_filter'] ? $post_taxonomies : explode( ',', $args['post_filter'] );
                $query_terms = get_terms( $post_filter_taxonomies, array('hide_empty' => 1, 'hierarchical' => 1, 'pad_counts' => false));

                if (!empty($query_terms)) {
                    $cats = array();
                    foreach ($query_terms as $cat) {
                        if ($cat->parent == 0 || in_array($cat->term_id, $terms)) {
                            $cats[$cat->parent][$cat->term_id] = $cat->name;
                        }
                    }
                    unset($query_terms);
                    foreach ($cats[0] as $pid => &$parent) {
                        if (!isset($cats[$pid]) && !in_array($pid, $terms)) {
                            unset($cats[0][$pid]);
                        }
                    }

                    $filter = '';
                    foreach ($cats[0] as $tid => $cat) {

                        $filter.='<li data-tax="' . $tid . '"><a onclick="return false;" href="' . get_term_link(intval($tid)) . '">' . $cat . '</a>';
                        if (isset($cats[$tid])) {
                            $filter.='<ul class="ptb-post-filter-child">';
                            $filter.=$this->get_Child($cats[$tid], $cats);
                            $filter.='</ul>';
                        }
                        $filter.='</li>';
                    }
                    $html = '<ul class="ptb-post-filter">' . $filter . '</ul>' . $html;
                }
            }

            // Restore original Post Data
            if ( isset( $saved_post ) && is_object($saved_post)) {
                $post = $saved_post;
                /**
                 * WooCommerce plugin resets the global $product on the_post hook,
                 * call setup_postdata on the original $post object to prevent fatal error from WC
                 */
                setup_postdata($saved_post);
            }
        } else if ( $args['not_found'] !== '' ){
			$html = '<div class="' . join( ' ', $css_classes ) . '" data-type="' . esc_attr( $type ) . '">';
				$html .= '<div class="ptb_not_found">' . $args['not_found'] . '</div>';
			$html .= '</div>';
        }

		$edit_link = '';
		if ( self::show_edit_links() ) {
			if ( ! empty( $args['template'] ) && $template->get_custom_template( $args['template'] ) ) {
				$template_name = $args['template'];
			} else {
				$template_name = $query->is_singular() ? 'single' : 'archive';
			}
			$edit_link = '<a class="ptb_edit_template_link" href="' . esc_url( $template->get_edit_url() . '#' . $template_name ) . '">' . __( 'Edit <span>PTB Template</span>', 'ptb' ) . '</a>';
		}
		$html = '<div class="ptb_wrap">' . $edit_link . $html . '</div><!-- .ptb_wrap -->';

        self::$shortcode = false;

        return $html;
    }

	/**
	 * Check whether the PTB edit links can be displayed
	 *
	 * @return bool
	 */
	public static function show_edit_links() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		/* disabled in Builder preview */
		if ( class_exists( 'Themify_Builder_Model' ) ) {
			if ( Themify_Builder_Model::is_front_builder_activate() || ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], [ 'tb_render_element', 'tb_load_module_partial', 'tb_render_element_shortcode', 'render_element_shortcode_ajaxify' ], true ) ) ) {
				return false;
			}
		}

		wp_enqueue_style( 'ptb-edit-template-link' );
		return true;
	}

    public static function parse_multi_query(array $value, $type, $k, $post_id = array(), $like = false) {
        $like = $like ? "meta_value LIKE '%%s%'" : "LOCATE('%s',`meta_value`)>0";
        global $wpdb;
        if(!is_array($post_id)){
            $post_id = array();
        }
        foreach ($value as $v) {
            $v = sanitize_text_field(trim($v));
            $condition = str_replace('%s', $v, $like);
            if(!empty($post_id)){
                $condition.=' AND post_id IN(' . implode(',', $post_id) . ')';
            }
            $get_values = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$k' AND $condition");
            if (empty($get_values)) {
                return false;
            }

            $ids = array();
            foreach ($get_values as $val) {
                $ids[] = $val->post_id;
            }
            $ids = implode(',', $ids);
            $get_posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$type' AND `post_status`='publish'");
            if (empty($get_posts)) {
                return false;
            }

            foreach ($get_posts as $p) {
                $post_id[] = $p->ID;
            }
            $post_id = array_unique($post_id);
        }
        return $post_id;
    }

    private function get_Child($term, $cats) {
        $filter = '';
        foreach ($term as $tid => $cat) {
            $filter.='<li data-tax="' . $tid . '"><a onclick="return false;" href="' . get_term_link(intval($tid)) . '">' . $cat . '</a></li>';
            if (isset($cats[$tid])) {
                $filter.=$this->get_Child($cats[$tid], $cats);
            }
        }
        return $filter;
    }

    public function disable_ptb($args) {
        self::$is_disabled = true;
        return $args;
    }

    public function defer_js($tag, $handle, $src) {
        if (strpos($src, $this->url) !== false) {
            $tag = str_replace(' src', ' defer="defer" src', $tag);
        }
        return $tag;
    }

	public function custom_post_author_archive($query) {
		if ( $query->is_author() && $query->is_main_query() ) {
			$types = array_keys( self::$options->get_custom_post_types() );
			$types[] = 'post';
		    $query->set( 'post_type', $types );
		}
	}

	public function add_to_creative_works($types) {
		return array_merge( $types, array_keys( self::$options->get_custom_post_types() ) );
	}

	/**
	 * Renders a post inside PTB lightbox
	 *
	 * @return string
	 */
	public function get_lightbox_content() {
		global $post;

        $post_type = $post->post_type;
        $template = '';
        $templateObject = self::$options->get_post_type_template_by_type($post_type);
        if ( $templateObject ) {
            $id = get_the_ID();
            if (post_password_required()){
                return get_the_password_form($id);
            } elseif (function_exists('wpml_get_language_information') && ( function_exists('icl_get_setting') || function_exists('pll_is_translated_post_type')) ) {
				if(function_exists('icl_get_setting')){
					$lang_settings = icl_get_setting('custom_posts_sync_option');
					$condition = !isset($lang_settings[$post_type]) || 2 !== (int)$lang_settings[$post_type];
                }else if(function_exists('pll_is_translated_post_type')){
					$condition = pll_is_translated_post_type($post_type);
                }
				if ( $condition ) {
					$post_lang = wpml_get_language_information($id);
					$lang = PTB_Utils::get_current_language_code();
					if(!empty($post_lang['language_code']) && strtolower($post_lang['language_code']) !== $lang && ($lang !== 'all' || !empty($lang))){
						return $template;
					}
				}
            }
            $single = self::$is_single && $templateObject->has_single() && is_singular($post_type);
            $archive = !$single && self::$template;
            if ($single || $archive) {
                $cmb_options = $post_support = $post_taxonomies = array();
                self::$options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
                $post_meta = array_merge(array(), get_post_custom(), get_post('', ARRAY_A));
                $post_meta['post_url'] = get_permalink();
                $post_meta['taxonomies'] = !empty($post_taxonomies) ? wp_get_post_terms($id, array_values($post_taxonomies)) : array();
                $themplate = new PTB_Form_PTT_Them($this->plugin_name, $this->version);
                $themplate_layout = $single ? $templateObject->get_single() : $templateObject->get_archive();

                if (isset($themplate_layout['layout']) && ($single || !in_array($id, self::$post_ids))) {
                    self::$post_ids[] = $id;
                    $template = '<article id="post-' . $id . '" class="' . implode(' ', get_post_class(array('ptb_post', 'tf_clearfix'))) . '">';
                    $template .= $themplate->display_public_themplate($themplate_layout, $post_support, $cmb_options, $post_meta, $post_type, $single);
                    $template .= '</article>';
                }
            }
        }

		return $template;
	}

	public function wp_before_admin_bar_render() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu( array(
			'id' => 'ptb',
			'parent' => '',
			'title' => __( 'PTB', 'ptb' ),
			'href' => '#',
		) );
		if ( self::$is_single || $this->query->is_post_type_archive() ) {
			$post_type = self::$is_single ? get_post_type_object( get_queried_object()->post_type ) : get_queried_object();
			$post_type_obj = self::$options->get_custom_post_type( $post_type->name );
			$wp_admin_bar->add_menu( array(
				'id' => 'ptb-edit-cpt',
				'parent' => 'ptb',
				'title' => sprintf( __( 'Edit Post Type: %s', 'ptb' ), $post_type->labels->singular_name ),
				'href' => $post_type_obj->get_edit_url(),
			) );
		}
		if ( $this->query->is_tax() ) {
			$taxonomy_obj = self::$options->get_custom_taxonomy( get_queried_object()->taxonomy );
			$wp_admin_bar->add_menu( array(
				'id' => 'ptb-edit-ctx',
				'parent' => 'ptb',
				'title' => sprintf( __( 'Edit Taxonomy: %s', 'ptb' ), get_taxonomy( get_queried_object()->taxonomy )->labels->singular_name ),
				'href' => $taxonomy_obj->get_edit_url(),
			) );
		}
		$template = self::$is_single ? self::$options->get_post_type_template_by_type( get_queried_object()->post_type ) : self::$template;
		if ( $template ) {
			$wp_admin_bar->add_menu( array(
				'id' => 'ptb-edit-ptt',
				'parent' => 'ptb',
				'title' => sprintf( __( 'Edit Template: %s', 'ptb' ), $template->get_name() ),
				'href' => $template->get_edit_url() . '#' . ( self::$is_single ? 'single' : 'archive' ),
			) );
		}
	}

	/**
	 * Disable the display of post title outside of PTB loop
	 *
	 * @return string
	 */
	function disable_title( $return, $post_id ) {
		/* $post_id 0 is the dummy post replacing the main query */
		if ( $post_id === 0 && self::$shortcode === false ) {
			$return = '';
		}

		return $return;
	}

	/**
	 * Disable the display of post title outside of PTB loop
	 *
	 * @return string
	 */
	function disable_comments( $file ) {
		if ( self::$shortcode === false ) {
			$file = $this->locate_template( 'blank.php', 'blank' );
		}

		return $file;
	}

    public static function order_by_date($orderby, $wp_query) {
        if (self::$date_sort_field && ( PTB_Public::$shortcode || $wp_query->get( 'order_by_date' ) ) ) {
			$order = strpos($orderby, 'DESC') !== false ? 'DESC' : 'ASC';
			$orderby = self::$date_sort_field === 'start'
					? "SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value,'\"',4),'\"',-1)"
					: "SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value,'\"',-2),'\"',1)";
			$orderby .= ' ' . $order;
			self::$date_sort_field = false;

			if( $wp_query->get( 'order_by_date' ) ) unset( $wp_query->query_vars[ 'order_by_date' ] );
        }

        return $orderby;
    }

	/**
	 * Returns the original page query, whether $wp_query or
	 * the local cached copy before it gets modified by PTB
	 *
	 * @return WP_Query
	 */
	public function get_actual_query() {
		global $wp_query;

		return null === $this->query ? $wp_query : $this->query;
	}

	/**
	 * Change post edit link to get the link from proper post
	 *
	 * @return string
	 */
	function get_edit_post_link( $link, $post_id, $context ) {
		if ( $post_id === 0 ) {
			remove_filter( 'get_edit_post_link', [ $this, 'get_edit_post_link' ], 10, 3 );
			$link = get_edit_post_link( $this->get_actual_query()->queried_object_id, $context );
			add_filter( 'get_edit_post_link', [ $this, 'get_edit_post_link' ], 10, 3 );
		}

		return $link;
	}

	/**
	 * Disable wpAutoP function on the_content filter regardless of priority
	 *
	 * @return void
	 */
	public function disable_wpautop() {
		global $wp_filter;
		if ( ! empty( $wp_filter['the_content'] ) ) {
			foreach( $wp_filter['the_content'] as $priority => $hooks ) {
				if ( isset( $hooks['wpautop'] ) ) {
					remove_filter( 'the_content', 'wpautop', $priority );
				}
			}
		}
	}

	/**
	 * Generate a warning message, displayed only to admins
	 *
	 * @return string
	 */
	private function warning( $text ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<div class="ptb_warning">' . ( $text ) . '</div>';
		}

		return '';
	}

	/**
	 * Generate HTML attributes from an array
	 *
	 * @return string
	 */
	private function html_attr( $attr ) {
		$out = '';
		foreach ( $attr as $k => $v ) {
			$out .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}

		return $out;
	}
}