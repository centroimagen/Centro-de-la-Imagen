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
class PTB_Search_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private static $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private static $version;
    private static $resp = '';
    private static $slug = false;
    private static $current_id = false;
    private static $cache_enabled = false;
	private static $is_search_container = false; /* flag, only set when inside [ptb-search-results] shortcode */

	/* name and associated post type of the currently submitted PTB Search form */
    private static $active_form = null;
	/* search data in $_REQUEST */
    private static $data = null;
	/* search results, cached as an array of post IDs */
    private static $search_result = null;

	/* prefix used for form fields */
	public static $prefix = 'ptb_';

	public static $url;

    /**
     * Creates or returns an instance of this class.
     *
     * @return	A single instance of this class.
     */
    public static function get_instance() {
        static $instance = null;
        if ( $instance === null ) {
            $instance = new self;
        }
        return $instance;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    private function __construct() {
		self::$plugin_name = PTB_Search::$plugin_name;
		self::$version = PTB_Search::$version;
		self::$url = plugin_dir_url( __FILE__ );
		self::$cache_enabled = apply_filters( 'ptb_search_enable_cache', self::$cache_enabled );

        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            add_action( 'template_redirect', array( $this, 'set_active_form' ) );
            add_action('wp_enqueue_scripts', array($this, 'public_enqueue_scripts'));
        } else {
            add_action('wp_ajax_nopriv_ptb_search_set_values', array($this, 'set_values'));
            add_action('wp_ajax_ptb_search_set_values', array($this, 'set_values'));
            add_action('wp_ajax_nopriv_ptb_search_autocomplete', array($this, 'get_terms'));
            add_action('wp_ajax_ptb_search_autocomplete', array($this, 'get_terms'));
            add_action('wp_ajax_nopriv_ptb_ajax_search', array($this, 'get_post'));
            add_action('wp_ajax_ptb_ajax_search', array($this, 'get_post'));
        }
        add_shortcode('ptb_search', array($this, 'ptb_search'));
        add_shortcode('ptb-search-results', array($this, 'ptb_search_results_container'));
		add_action( 'widgets_init', 'PTB_Search_Widget::register' );
    }

    public function public_enqueue_scripts() {

        global $wp_scripts;
        $translation_ = array(
            'url' => self::$url,
            'ver' => self::$version,
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'i18n' => [
				'currentText' => _x( 'Now', 'PTB date picker currentText', 'ptb-search' ),
				'closeText' => _x( 'Done', 'PTB date picker closeText', 'ptb-search' ),
				'timeText' => _x( 'Time', 'PTB date picker timeText', 'ptb-search' ),
			],
        );
        wp_register_script(self::$plugin_name . '-date', self::$url . 'js/jquery-ui-timepicker.min.js', array('jquery-ui-datepicker', self::$plugin_name), self::$version, false);
        wp_register_style(self::$plugin_name, PTB_Utils::enque_min(self::$url . 'css/ptb-search.css'), array(), self::$version, 'all');
        wp_register_script(self::$plugin_name,PTB_Utils::enque_min(self::$url . 'js/ptb-search.js'), array('ptb'), self::$version, true);
        wp_register_style(self::$plugin_name . '-select', PTB_Utils::enque_min(self::$url . 'css/select2.css'), array(), self::$version, 'all');
        wp_register_script(self::$plugin_name . '-select', self::$url . 'js/select2.min.js', array(), self::$version, true);

        wp_localize_script(self::$plugin_name, 'ptb_search', $translation_);
        $ui = $wp_scripts->query('jquery-ui-core');
        if (is_page()) {
            self::$current_id = get_the_ID();
        }
    }

    public function ptb_search($atts) {
        if (!isset($atts['form'])) {
            return;
        }
        $ptb_options = PTB::get_option();
        if (!isset($ptb_options->option_post_type_templates[$atts['form']]['search'])) {
            return;
        }
        $template = $ptb_options->option_post_type_templates[$atts['form']];
        if (empty($template['search']['layout'])) {
            return;
        }
        $languages = PTB_Utils::get_all_languages();
        $lang = PTB_Utils::get_current_language_code();
        $count = count($template['search']['layout']) - 1;
        $post_type = $template['post_type'];
        $cmb_options = $post_support = $post_taxonomies = array();
        $ptb_options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
        $cmb_options['has'] = array('type' => 'has');
        $cmb_options['button'] = array('type' => 'button');
        $cmb_options['content'] = array('type' => 'content');
        $post_support[] = 'has';
        $post_support[] = 'button';
        $post_support[] = 'content';
        $cmb_options = apply_filters( 'ptb_search_render', $cmb_options, 'search', $post_type );
        $form_keys = array();

		$same_page = empty( $template['search'][ 'ptb_ptt_result_type' ] ) || $template['search'][ 'ptb_ptt_result_type' ] === 'same_page';
		$disable_ajax = $same_page && isset( $template['search']['ptb_ptt_disable_ajax'] ) && $template['search']['ptb_ptt_disable_ajax'] === '1';
		if ( ! $same_page ) {
			$disable_ajax = true;
			if ( $template['search'][ 'ptb_ptt_result_type' ] === 'archive' ) {
				$action = get_post_type_archive_link( $post_type );
			} else {
				if ( ! empty( $template['search'][ 'ptb_ptt_page' ] ) ) {
					$action = get_the_permalink( $template['search'][ 'ptb_ptt_page' ] );
				}
			}
		} else {
			$action = self::current_page_url();
			if ( empty( $action ) ) {
				$action = get_post_type_archive_link( $post_type );
			}
		}
		$scrollto = ! isset( $template['search']['ptb_ptt_scrollto'] ) || $template['search']['ptb_ptt_scrollto'] === '1';
        ob_start();
		?>
        <form
			method="get"
			class="ptb-search-form ptb-search-<?php echo $atts['form'] ?><?php if ( ! $scrollto ) : ?> ptb-search-no-scroll<?php endif; ?><?php if ( $disable_ajax ) : ?> ptb-search-no-ajax<?php endif; ?>"
			action="<?php echo esc_url( $action ); ?>"
			data-archive="<?php echo esc_url( get_post_type_archive_link( $post_type ) ); ?>"
		>
            <input type="hidden" class="ptb-search-post-type" value="<?php echo $post_type ?>" />
            <input type="hidden" name="f" value="<?php echo esc_attr( $atts['form'] ); ?>" />
            <input type="hidden" name="ptb-search" value="1" />

			<?php
			if ( PTB_Public::show_edit_links() ) {
				echo '<a class="ptb_edit_template_link" href="' . admin_url( 'admin.php?page=ptb-search#' ) . $atts['form'] . '">' . __( 'Edit <span>Search Form</span>', 'ptb' ) . '</a>';
			}

            foreach ($template['search']['layout'] as $k => $row) {

                $class = '';
                if ($k === 0) {
                    $class .= 'first';
                } elseif ($k === $count) {
                    $class .= 'last';
                }
				
				if ( !empty( $row['row_classes'] ) ) {
					$class .= $row['row_classes'];
					unset($row['row_classes']);
				}
                ?>
                <div class="<?php if ($class): ?>ptb_<?php echo $class ?>_row<?php endif; ?> ptb_row ptb_<?php echo $post_type ?>_row">
                    <?php if (!empty($row)): ?>
                        <?php
                        $colums_count = count($row) - 1;
                        $i = 0;
                        ?>
                        <?php foreach ($row as $col_key => $col): ?>
                            <?php
                            $tmp_key = explode('-', $col_key);
                            $key = isset($tmp_key[1]) ? $tmp_key[0] . '-' . $tmp_key[1] : $tmp_key[0];
                            ?>
                            <div class="ptb_col ptb_col<?php echo $key ?><?php if ($i === 0): ?> ptb_col_first<?php elseif ($i === $colums_count): ?> ptb_col_last<?php endif; ?>">
                                <?php if (!empty($col)): ?>
                                    <?php foreach ($col as $module): ?>
                                        <?php
										if (!is_array($module) || !isset($module['key'])) {
											continue;
										}
										$type = $module['type'];
										$label = ! empty( $module['label'] ) ? PTB_Utils::get_label( $module['label'] ) : false;
										$meta_key = $module['key'];
										$id = 'ptb_' . $atts['form'] . '_';
										$args = $cmb_options[$meta_key];
										$args['key'] = $meta_key;
										if ( has_action( 'ptb_search_' . $type ) ) {
											$value = isset(self::$data[$post_type][$meta_key]) && self::$data[$post_type][$meta_key] ? self::$data[$post_type][$meta_key] : false;
											$id .= $meta_key;
											do_action( 'ptb_search_' . $type, $post_type, $id, $args, $module, $value, $label, $lang, $languages );
											continue;
										}

                                        if ( isset( $cmb_options[ $meta_key ] ) && ( $module['type'] !== 'has' || (isset( $module['has_field'] ) && $module['type'] === 'has' && in_array($module['has_field'], $post_support ) ) ) ) :
                                            $field = in_array($type, $post_support);
                                            $m = $module;
                                            unset($m['label']);
                                            if ($type !== 'taxonomies') {
                                                $form_keys[$atts['form']][$meta_key] = $m;
                                                $id.=$meta_key;
                                                if ($type === 'has') {
                                                    $id.='_' . $module['has_field'];
                                                }
                                            } else {
                                                $id.= $meta_key . '_' . $module['taxonomy'];
                                            }
                                            if (!$label && $module['type'] !== 'button') {
                                                if ($type !== 'taxonomies') {

                                                    $multy = isset($module['show_as']) ? PTB_Search_Options::is_multy($module['show_as']) : false;
                                                    $label = !$field ? PTB_Utils::get_label($args['name']) : PTB_Search_Options::get_name($type, $multy);
                                                    if ($type === 'has') {
                                                        $label = $label . ' ' . PTB_Search_Options::get_name($module['has_field']);
                                                    }
                                                } else {
                                                    $tax_ = $ptb_options->get_custom_taxonomy($module['taxonomy']);

                                                    if (!$tax_) {
                                                        continue;
                                                    }
                                                    $label = PTB_Search_Options::is_multy($module['show_as']) ? PTB_Utils::get_label($tax_->plural_label) : PTB_Utils::get_label($tax_->singular_label);
                                                }
                                            }
                                            ?>
                                            <div  class="ptb_search_module ptb_search_<?php echo $type ?><?php if (!$field): ?> ptb_search_<?php echo $meta_key ?><?php endif; ?> tf_clearfix">

                                                <?php if ($label): ?>
                                                    <div class="ptb_search_label">
                                                        <label for="<?php echo $id ?>"><?php echo $label; ?></label>
                                                    </div>
                                                <?php endif; ?>

												<?php $this->render($type, $post_type, $id, $args, $module, $label, $lang, $languages, $field); ?>

                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php $i ++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php }
			?>
            <input type="hidden" value="<?php echo base64_encode(json_encode( apply_filters( 'ptb_search_keys', $form_keys ) ) ); ?>" class="ptb_search_keys" autocomplete="off" />
        </form>
        <?php
        $result = ob_get_contents();
        ob_end_clean();
        if (!wp_script_is(self::$plugin_name)) {
            wp_enqueue_script(self::$plugin_name);
        }
        if (!wp_style_is(self::$plugin_name)) {
            wp_enqueue_style(self::$plugin_name);
        }
        return $result;
    }

	/*
	* Shortcode to display search result in a specific location
	*
	* Sets the self::$is_search_container flag
	*/
	public function ptb_search_results_container( $atts, $content = null ) {
		self::$is_search_container = true;
		if ( strpos( $content, '[ptb' ) === false && current_user_can( 'manage_options' ) ) {
			/* make sure [ptb] shortcode exists inside the search-container */
			$content = $this->missing_shortcode_notice_message() . $content;
		}
		$content = '<div class="ptb-search-container">' . do_shortcode( $content ) . '</div>';
		self::$is_search_container = false;
		return $content;
	}

    public function render($type, $post_type, $id, array $args, array $module, $label, $lang, array $languages) {
        $name = isset($args['name']) ? PTB_Utils::get_label($args['name']) : PTB_Search_Options::get_name($type);
		$meta_key = $args['key'];
        $name = $name ? sanitize_title($name) : $meta_key;
        $value = isset( self::$data[$meta_key] ) && self::$data[$meta_key] ? self::$data[$meta_key] : false;

		switch ($type) {
            case 'button':
				PTB_Utils::enqueue_module_css( 'link_button' );
                if (isset($module['aligmnet'])) {
                    echo '<div class="ptb-search-align-' . $module['aligmnet'] . '">';
                }

				$keys = [ '' ];
				if ( ! empty( $module['reset'] ) ) {
					$keys = 'before' === $module['reset'] ? [ 'reset_', '' ] : [ '', 'reset_' ];
				}

				foreach ( $keys as $key ) {
					$style = false;
					$class = array();
					if ( ! empty( $module["custom_{$key}color"] ) ) {
						$style = 'background-color:' . $module["custom_{$key}color"] . ' !important;';
					} elseif ( isset( $module["{$key}color"] ) ) {
						$class[] = $module["{$key}color"];
					}
					$label = PTB_Utils::get_label( $module["{$key}text"] );
					if ( ! $label ) {
						$label = $key === '' ? __( 'Search', 'ptb-search' ) : __( 'Reset', 'ptb-search' );
					}
					?>
					<input <?php if ($style): ?>style="<?php echo $style ?>"<?php endif; ?> class="shortcode ptb_link_button <?php echo implode(' ', $class) ?>" type="<?php echo $key === '' ? 'submit' : 'reset'; ?>" value="<?php echo $label ?>" />
					<?php
				}

                if (isset($module['aligmnet'])) {
                    echo '</div>';
                }

                break;
            case 'date':
            case 'author':
                self::show_as($type, $post_type, $id, $name, $value, $meta_key, $label);
                break;
            case 'text':
			case 'textarea':
            case 'content':
            case 'title':
            case 'radio_button':
            case 'checkbox':
            case 'select':
                $options = array();
                if($type === 'radio_button' || $type === 'checkbox' || $type === 'select'){
                        if (empty($args['options'])) {
                            return;
                        }
                        if ($type === 'radio_button') {
                                $type = 'radio';
                        }
                        foreach ($args['options'] as $opt) {
                                $options[$opt['id']] = PTB_Utils::get_label($opt);
                        }
                }
                if ($type === 'content' || $type === 'textarea') {
                    $type = 'text';
                }
				if ($type === 'select' && $module['multiselect']) {
                    $type = 'multiselect';
                }
                self::show_as($type, $post_type, $id, $name, $value, $meta_key, $label, $options);
                break;
            case 'taxonomies':
            case 'category':
                $slug = $type === 'taxonomies' ? $module['taxonomy'] : $type;
                $v = '';
                if ($value) {
                    if (isset($value[$slug])) {
                        $v = $value[$slug];
                    } elseif ($type !== 'taxonomies') {
                        $v = $value;
                    }
                }
                if ( $module['show_as'] === 'select' || $module['show_as'] === 'multiselect' ) {
					self::enqueue_select2();
					if ( ! class_exists( 'Walker_PTB_CategoryDropdown' ) ) {
						include PTB::$dir . 'includes/class-walker-ptb-category-dropdown.php';
					}
					$categories = wp_dropdown_categories( [
						'orderby' => ! empty( $module['orderby'] ) ? $module['orderby'] : 'name',
						'order' => ! empty( $module['order'] ) ? $module['order'] : 'ASC',
						'show_count' => !empty( $module['count'] ),
						'hide_empty' => true,
						'taxonomy' => $slug,
						'name' => self::$prefix . $slug . ( $module['show_as'] === 'multiselect' ? '[]' : '' ),
						'hierarchical' => is_taxonomy_hierarchical( $slug ),
						'show_option_all' => $module['show_as'] === 'multiselect' ? false : __( 'Show All', 'ptb-search' ),
						'id' => $id,
						'value_field' => 'slug',
						'echo' => false,
						'selected' => $v,
						'walker' => new Walker_PTB_CategoryDropdown,
						'exclude' => ! empty( $module['term_exclude'] ) ? $module['term_exclude'] : null,
						'include' => ! empty( $module['term_include'] ) ? $module['term_include'] : null,
					] );
					if ( $module['show_as'] === 'multiselect' ) {
						$categories = str_replace( '<select ', '<select multiple="multiple" ', $categories );
					}
					echo '<div class="ptb_search_select_wrap">' . $categories . '</div>';
				} elseif ( $module['show_as'] !== 'autocomplete' ) {
                    $orderby = !empty($module['orderby']) ? $module['orderby'] : 'name';
                    $order = !empty($module['order']) ? $module['order'] : 'ASC';
                    $terms = get_terms($slug, array(
						'hide_empty' => true,
						'hierarchical' => false,
						'orderby' => $orderby,
						'order' => $order,
						'exclude' => ! empty( $module['term_exclude'] ) ? $module['term_exclude'] : null,
						'include' => ! empty( $module['term_include'] ) ? $module['term_include'] : null,
					) );
					$options = array();
                    if (!empty($terms)) {
                        $show_count = !empty($module['count']);
                        foreach ($terms as $t) {
                            $options[$t->slug] = $t->name;
                            if($show_count){
                                $options[$t->slug] .= sprintf( ' <span class="ptb_search_term_count">(%s)</span>', $t->count );
                            }
                        }
                    }
					self::show_as($module['show_as'], $post_type, $id, $slug, $v, $meta_key, $label, $options, $slug);
                } else {
                    if ($v) {
                        if (is_array($v)) {
                            $v = reset($v);
                        }
                        $get_term = get_term_by('slug', $v, $slug);
                        $v = $get_term ? array('slug' => $v, 'name' => $get_term->name) : '';
                    }
					self::show_as($module['show_as'], $post_type, $id, $slug, $v, $meta_key, $label, [], $slug);
                }
				if ( in_array( $module['show_as'], [ 'multiselect', 'checkbox' ], true ) && isset( $module['operator'] ) && $module['operator'] === 'AND' ) {
					echo '<input type="hidden" name="' . self::$prefix . $slug . '_operator' . '" value="AND" />';
				}
                break;
            case 'post_tag':
                $options = array();
                if (empty($module['show_as'])) {
                    $module['show_as'] = 'autocomplete';
                } elseif ($module['show_as'] !== 'autocomplete') {
                    $args = array('post_type' => $post_type, 'orderby' => 'ID', 'order' => 'ASC', 'numberposts' => 1, 'tag' => '');
                    $terms = get_tags(array(
                        'get' => 'all',
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));
                    foreach ($terms as $t) {
                        $args['tag'] = $t->slug;
                        $is_empty = get_posts($args);
                        if (!empty($is_empty)) {
                            $options[$t->slug] = $t->name;
                        }
                    }

                    wp_reset_postdata();
                }
                self::show_as($module['show_as'], $post_type, $id, $type, $value, $meta_key, $label, $options, $type);
				if ( in_array( $module['show_as'], [ 'multiselect', 'checkbox' ], true ) && isset( $module['operator'] ) && $module['operator'] === 'AND' ) {
					echo '<input type="hidden" name="' . self::$prefix . $type . '_operator' . '" value="AND" />';
				}
                break;
            case 'custom_image':
                ?>
                <?php if (!empty($module['image'])): ?>
                    <?php
                    $url = PTB_CMB_Base::ptb_resize($module['image'], $module['width'], $module['height']);
                    ?>
                    <figure class="ptb_search_post_image tf_clearfix">
                        <?php
                        if (isset($module['link']) && $module['link']):
                            echo '<a href="' . $module['link'] . '">';
                        endif;
                        ?>
                        <img src="<?php echo $url ?>" />
                        <?php
                        if (isset($module['link']) && $module['link']):
                            echo '</a>';
                        endif;
                        ?>
                    </figure>
                <?php endif; ?>
                <?php
                break;
            case 'custom_text':
            case 'plain_text':
                if ($module['text'][$lang]) {
                    echo!has_shortcode($module['text'][$lang], self::$plugin_name) ? do_shortcode($module['text'][$lang]) : $module['text'][$lang];
                }
                break;
            case 'has':
                $options = array(
                    'yes' => __('Yes', 'ptb-search'),
                    'no' => __('No', 'ptb-search')
                );
                if ($module['show_as'] === 'checkbox') {
                    $options = array(1 => '');
                }
                $name = 'has[' . $module['has_field'] . ']';
                if ($value && isset($value[$module['has_field']]) ) {
                    $value = $value[$module['has_field']];
                } else {
                    $value = '';
                }
                self::show_as($module['show_as'], $post_type, $id, $name, $value, $meta_key, $label, $options, false, __('---', 'ptb-search'));
                break;
            case 'number':
                $from = $to = '';
                if ($value) {
                    if (isset($value['from']) && is_numeric($value['from'])) {
                        $from = floatval($value['from']);
                    }
                    if (isset($value['to']) && is_numeric($value['to'])) {
                        $to = floatval($value['to']);
                    }
                }
                $slider = $module['show_as'] === 'slider';
                ?>
                <div class="ptb_search_wrap_number ptb_search_wrap_number_<?php echo $module['show_as'] ?>">
                    <?php if (!$slider): ?>
                        <div class="ptb_search_wrap_min">
                        <?php endif; ?>
                        <input placeholder="<?php _e('From', 'ptb-search') ?>" class="ptb_search_number_min" type="<?php echo $slider ? 'hidden' : 'number' ?>" id="<?php echo $id ?>_min" value="<?php echo $from ?>" name="<?php echo self::$prefix . $name ?>-from" autocomplete="off" />
                        <?php if (!$slider): ?>
                        </div>
                        <div class="ptb_search_wrap_max">
                        <?php endif; ?>
                        <input placeholder="<?php _e('To', 'ptb-search') ?>" class="ptb_search_number_max" type="<?php echo $slider ? 'hidden' : 'number' ?>" id="<?php echo $id ?>_max" value="<?php echo $to ?>" name="<?php echo self::$prefix . $name ?>-to" autocomplete="off" />
                        <?php if ($slider): ?>

                            <div class="ptb-search-slider"></div>
                            <?php
                            if (!wp_script_is('jquery-ui-slider')) {
                                wp_enqueue_style(self::$plugin_name . 'ui-css');
                                wp_enqueue_script('jquery-ui-slider');
                            }
                            ?>
                        <?php else: ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                break;
        }
    }

	public static function enqueue_select2() {
		if ( ! wp_script_is( self::$plugin_name . '-select' ) ) {
			wp_enqueue_style( self::$plugin_name . '-select' );
			wp_enqueue_script( self::$plugin_name . '-select' );
		}
	}

    public static function show_as($type, $post_type, $id, $name, $value, $key, $label, $data = array(), $slug = false, $placeholder = false) {

		$name = self::$prefix . $name;
        switch ($type) {
            case 'date':
                if (!wp_script_is(self::$plugin_name . '-date')) {
                    wp_enqueue_script(self::$plugin_name . '-date');
                }
                ?>
                <div class="ptb_search_field_date">
                    <input type="text" class="ptb_search_date_from" data-id="<?php echo $id ?>" id="<?php echo $id ?>_start" name="<?php echo $name ?>-from" placeholder="<?php _e('From', 'ptb-search') ?>" value="<?php echo isset($value['from']) ? $value['from'] : '' ?>" />
                    <input type="text" class="ptb_search_date_to" id="<?php echo $id ?>_end" name="<?php echo $name ?>-to" placeholder="<?php _e('To', 'ptb-search') ?>" value="<?php echo isset($value['to']) ? $value['to'] : '' ?>" />
                </div>
                <?php
                break;
            case 'select':
            case 'multiselect':
                self::enqueue_select2();
                if ($value && !is_array($value)) {
                    $value = array($value);
                }
                ?>

                <div class="ptb_search_<?php echo $type ?>_wrap">
                    <select data-placeholder="<?php echo $placeholder ? $placeholder : __('Show All', 'ptb-search') ?>" <?php if ($type == 'multiselect'): ?>multiple="multiple" <?php endif; ?>name="<?php echo $name ?><?php echo $type == 'multiselect' ? '[]' : '' ?>" id="<?php echo $id ?>">
                        <?php if ($type !== 'multiselect'): ?>
                            <option value=""><?php echo $placeholder ? $placeholder : __('Show All', 'ptb-search') ?></option>
                        <?php endif; ?>
                        <?php foreach ($data as $k => $v): ?>
                            <option <?php if ($value && in_array($k, $value)): ?>selected="selected"<?php endif; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <?php
                break;
            case 'autocomplete':
            case 'text':
            case 'title':
            case 'author':
                if (!wp_script_is('jquery-ui-autocomplete')) {
                    wp_enqueue_style(self::$plugin_name . 'ui-css');
                    wp_enqueue_script('jquery-ui-autocomplete');
                }
                $v1 = $value;
                $v2 = '';
                if ($key === 'taxonomies' && !empty($value['name'])) {
                    $v1 = $value['name'];
                    $v2 = $value['slug'];
                }
                ?>
                <input type="text" <?php if ($key !== 'taxonomies'): ?>name="<?php echo $name ?>"<?php endif; ?> data-post_type="<?php echo $post_type ?>" data-key="<?php echo base64_encode(json_encode(array('post_type' => $post_type, 'key' => $key, 'slug' => $slug))) ?>" class="ptb-search-autocomplete" value="<?php esc_attr_e($v1) ?>"  id="<?php echo $id ?>" />
                <input type="hidden" value="<?php esc_attr_e($v2) ?>" <?php if ($key === 'taxonomies'): ?>name="<?php echo $name ?>"<?php endif; ?> />
                <?php
                break;
            case 'radio':
            case 'checkbox':
                ?>
                <div class="ptb_search_<?php echo $type ?>_wrap ptb_search_option_wrap">
                    <?php
                    $is_one = count($data) === 1;
                    if ($type == 'checkbox') {
                        $name.= '[]';
                    } else {
                        if ($value) {
                            $value = array($value);
                        }
                        ?>

                        <label>
                            <input <?php if (!$value): ?> checked="checked"<?php endif; ?> type="<?php echo $type ?>" name="<?php echo $name ?>" value="" />
                            <?php _e('Any', 'ptb-search') ?>
                        </label>

                    <?php } ?>

                    <?php foreach ($data as $k => $v): ?>
                        <?php if (!$is_one): ?>
                            <label>
                                <input <?php if ($value && in_array($k, $value)): ?> checked="checked"<?php endif; ?>id="<?php echo $id ?>_<?php echo $k ?>" type="<?php echo $type ?>" name="<?php echo $name ?>" value="<?php echo $k ?>" />
                                <?php echo $v ?>
                            </label>
                        <?php else: ?>
                            <label>
                                <input <?php if ($value && in_array($k, $value)): ?> checked="checked"<?php endif; ?>id="<?php echo $id ?>" type="<?php echo $type ?>" name="<?php echo $name ?>" value="<?php echo $k ?>" />
                                <?php echo $v ?>
                            </label>
                        <?php endif; ?>
                    <?php endforeach ?>
                </div>
                <?php
                break;
        }
    }

    public function set_values() {
        if (!isset($_POST['data']) || !$_POST['data']) {
            wp_die();
        }
        $data = sanitize_text_field($_POST['data']);
        $data = json_decode(base64_decode($data), true);
        if (!$data) {
            wp_die();
        }
        $slug = key($data);
        $ptb_options = PTB::get_option();
        if (!isset($ptb_options->option_post_type_templates[$slug]['search']['layout'])) {
            wp_die();
        }
        $post_type = $ptb_options->option_post_type_templates[$slug]['post_type'];
        $cache = PTB_Search_Options::get_cache();
        if (isset($cache['default'][$post_type][$slug])) {
            echo wp_json_encode($cache['default'][$post_type][$slug]);
            wp_die();
        }
        $cmb_options = $ptb_options->get_cpt_cmb_options($post_type);
        $result = array();
        global $wpdb;
        PTB_Public::$shortcode = true;
        foreach ($data[$slug] as $meta_key => $m) {
			
            if (!isset($cmb_options[$meta_key])) {
                continue;
            }
            $args = $cmb_options[$meta_key];
            $meta_key = sanitize_key($meta_key);
            $id = 'ptb_' . $slug . '_' . $meta_key;
            $meta_key = 'ptb_' . $meta_key;
            switch ($m['type']) {
                case 'number':
                    $get_values = $wpdb->get_results("SELECT `post_id`,`meta_value` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '$meta_key' AND `meta_value`!=''");
                    $max = $min = array();
                    if (!empty($get_values)) {
                        $ids = array();
                        foreach ($get_values as $val) {
                            $ids[] = $val->post_id;
                        }
                        $ids = implode(',', $ids);
                        $posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                        if (!empty($posts)) {
                            $ids = array();
                            foreach ($posts as $p) {
                                $ids[$p->ID] = 1;
                            }
                            foreach ($get_values as $val) {
                                if (isset($ids[$val->post_id])) {
                                    $v = maybe_unserialize($val->meta_value);
                                    $m = isset($v['to']) ? $v['to'] : $v;
                                    $n = isset($v['from']) ? $v['from'] : $v;
                                    if (is_numeric($m)) {
                                        $min[$val->post_id] = $n;
                                    }
                                    if (is_numeric($n)) {
                                        $max[$val->post_id] = $m;
                                    }
                                }
                            }
                            $result[$id] = array('min' => !empty($min) ? min($min) : 0, 'max' => !empty($max) ? max($max) : 0);
                        } else {
                            $result[$id] = 1;
                        }
                    } else {
                        $result[$id] = 1;
                    }
                    break;
                case 'radio_button':
                case 'checkbox':
                case 'select':

                    foreach ($args['options'] as $opt) {
                        $condition = $m['type'] !== 'radio_button' ? "LOCATE('{$opt['id']}',`meta_value`,15)>0" : "meta_value='{$opt['id']}'";
                        $get_values = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '$meta_key' AND $condition");
                        if (empty($get_values)) {
                            $result[$id][] = $opt['id'];
                        } else {
                            $ids = array();
                            foreach ($get_values as $val) {
                                $ids[] = $val->post_id;
                            }
                            $ids = implode(',', $ids);
                            $posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish' LIMIT 1");
                            if (empty($posts)) {
                                $result[$id][] = $opt['id'];
                            }
                        }
                    }
                    break;
                case 'has':
                    if ($m['has_field'] === 'comments') {
                        $posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` = '$post_type' AND `post_status`='publish' AND comment_count>0 LIMIT 1");
                        if (empty($posts)) {
                            $result[$id] = 1;
                        }
                    } elseif ($m['has_field'] === 'thumbnail') {
                        $args = array(
                            'post_type' => $post_type,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'posts_per_page' => 1,
                            'meta_query' => array(
                                array(
                                    'key' => '_thumbnail_id',
                                    'compare' => 'EXISTS'
                                ),
                            )
                        );
                        $query = new WP_Query($args);
                        if (!$query->have_posts()) {
                            $result[$id] = 1;
                        }
                    }
                    break;
            }
        }
		$result = apply_filters( 'ptb_search_set_values', $result, $data[$slug], $slug );
        $cache['default'][$post_type][$slug] = $result;
        PTB_Search_Options::set_cache($cache);
        echo wp_json_encode($result);
        PTB_Public::$shortcode = false;
        wp_die();
    }

    public function get_terms() {
        if (isset($_GET['key']) && isset($_GET['term'])) {
            $data = sanitize_text_field($_GET['key']);
            $data = json_decode(base64_decode($data), true);
            if (!$data) {
                wp_die();
            }
            $post_type = sanitize_key($data['post_type']);
            $ptb_options = PTB::get_option();
            if ( ! post_type_exists( $post_type ) ) {
                wp_die();
            }
            $key = esc_sql($data['key']);
            $term = esc_sql($_GET['term']);
            $options = array();
            PTB_Public::$shortcode = true;
            if ($key === 'title' || $key === 'taxonomies' || $key === 'author' || $key === 'post_tag') {
                if ($key === 'author') {
                    global $wpdb;
                    $get_values = $wpdb->get_results("SELECT display_name,ID FROM (((SELECT post_author FROM `{$wpdb->posts}`  WHERE post_status='publish' AND post_type='$post_type') AS A ) JOIN (SELECT display_name,ID FROM `{$wpdb->users}` WHERE LOCATE('{$term}',`display_name`)>0) AS B) WHERE B.ID=post_author GROUP BY display_name ORDER BY NULL  LIMIT 15");
                    foreach ($get_values as $author) {
                        $options[$author->ID] = array( 'id' => $author->ID, 'value' => $author->ID, 'label' => $author->display_name );
                    }
                } elseif ($key === 'taxonomies') {
                    $slug = sanitize_key($data['slug']);
                    $terms = get_terms($slug, array('name__like' => $term, 'hide_empty' => true, 'hierarchical' => false, 'orderby' => 'name', 'order' => 'ASC', 'number' => 15));
                    foreach ($terms as $t) {
                        $options[$t->term_id] = array( 'id' => $t->slug, 'value' => $t->slug, 'label' => $t->name );
                    }
                } elseif ($key === 'post_tag') {
                    $terms = get_tags(array(
                        'name__like' => $term,
                        'get' => 'all',
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'number' => 15
                    ));
                    foreach ($terms as $t) {
                        $options[$t->term_id] = array( 'id' => $t->slug, 'value' => $t->slug, 'label' => $t->name );
                    }
                } else {
                    add_filter('posts_search', array(__CLASS__, 'search_by_title'), 100, 2);
                    $the_query = new WP_Query( array(
                        'post_type' => $post_type,
                        'orderby' => 'title',
                        'order' => 'ASC',
                        's' => $term,
                        'posts_per_page' => 15
                    ) );
                    if ( $the_query->have_posts() ){
                        while ( $the_query->have_posts() ){
                            $the_query->the_post();
                            $t = get_the_title();
							// IMPORTANT: use the unfiltered post title as the search query; so it matches the value in DB
                            $options[get_the_ID()] = array( 'id' => $t, 'value' => $GLOBALS['post']->post_title, 'label' => $t );
                        }
                        wp_reset_postdata();
                    }
                }
            } else {
                global $wpdb;
                $get_values = $wpdb->get_results("SELECT `post_id`,meta_value FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$key' AND LOCATE('{$term}',`meta_value`)>0");
                if (!empty($get_values)) {
                    $values = array();
                    foreach ($get_values as $val) {
                        $values[$val->post_id] = $val->meta_value;
                    }
                    $ids = implode(',', array_keys($values));
                    $posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                    if (!empty($posts)) {
                        $p = array();
                        foreach ($posts as $post) {
                            $p[$post->ID] = 1;
                        }
                        foreach ($values as $k => $v) {
                            if (isset($p[$k])) {
                                $v = maybe_unserialize($v);
                                if (is_array($v)) {
                                    foreach ($v as $v1) {
                                        if (stripos($v1, $term) !== false) {
                                            $options[$v1] = array( 'id' => $v1, 'value' => $v1, 'label' => $v1 );
                                        }
                                    }
                                } else {
                                    $v = wp_strip_all_tags($v);
                                    $options[$v] = array( 'id' => $v, 'value' => $v, 'label' => $v );
                                }
                            }
                        }
                    }
                }
            }
            echo wp_json_encode($options);
        }
        PTB_Public::$shortcode = false;
        wp_die();
    }

    public static function search_by_title($search, $wp_query) {
        global $wpdb;
        if (empty($search))
            return $search; // skip processing - no search term in query
        $q = $wp_query->query_vars;
        $n = !empty($q['exact']) ? '' : '%';
        $search = $searchand = '';
        foreach ((array) $q['search_terms'] as $term) {
            $term = esc_sql($wpdb->esc_like($term));
            $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
            $searchand = ' AND ';
        }
        if (!empty($search)) {
            $search = " AND ({$search}) ";
            if (!is_user_logged_in())
                $search .= " AND ($wpdb->posts.post_password = '') ";
        }
        return $search;
    }

    public static function search_by_content($search, $wp_query) {
        global $wpdb;
        if (empty($search))
            return $search; // skip processing - no search term in query
        $q = $wp_query->query_vars;
        $n = !empty($q['exact']) ? '' : '%';
        $search = $searchand = '';
        foreach ((array) $q['search_terms'] as $term) {
            $term = esc_sql($wpdb->esc_like($term));
            $search .= "{$searchand}($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
            $searchand = ' AND ';
        }
        if (!empty($search)) {
            $search = " AND ({$search}) ";
            if (!is_user_logged_in())
                $search .= " AND ($wpdb->posts.post_password = '') ";
        }
        return $search;
    }

    private static function search( $post_type, $slug, array $post = array(), $post_id = false, array $cmb_options = array(), array $post_support = array(), array $post_taxonomies = array() ) {
        PTB_Public::$shortcode = true;
		if ($post_id === false) {
            $cache = PTB_Search_Options::get_cache();
            $post_id = array();
            global $wpdb;
            $post = apply_filters('ptb_search_post', $post, $cmb_options, $post_type, $slug, $post_taxonomies);
			foreach ($post as $meta_key => $value) {
                if (!isset($cmb_options[$meta_key]) || !$value) {
                    continue;
                }
                $args = $cmb_options[$meta_key];
                $type = $args['type'];

                switch ($type) {
                    case 'date':

                        if (!empty($value['from'])|| !empty($value['to'])) {
                            $query_args = array(
                                'fields' => 'ids',
                                'post_type' => $post_type,
                                'orderby' => 'ID',
                                'order' => 'ASC',
                                'nopaging' => 1,
                                'include' => !empty($post_id) ? implode(',', array_keys($post_id)) : '',
                                'date_query' => array(
                                    array(
                                        'after' => isset($value['from']) && $value['from'] ? $value['from'] : '',
                                        'before' => isset($value['to']) && $value['to'] ? $value['to'] : '',
                                        'inclusive' => true,
                                    ),
                                )
                            );
                            $posts_array = get_posts($query_args);
                            if (empty($posts_array)) {
                                return;
                            } else {
                                $post_id = array();
                                foreach ($posts_array as $p) {
                                    $post_id[$p] = 1;
                                }
                            }
                        }
                        break;
                    case 'checkbox':
                    case 'select':
                    case 'radio_button':
                    case 'text':
                    case 'textarea':
                    case 'content':
                    case 'title':
                    case 'author':

                        if ($type === 'author') {
                            $value = esc_sql($value);
                            $condition = !empty($post_id) ? ' AND ID IN(' . implode(',', array_keys($post_id)) . ')' : '';
                            $get_posts = $wpdb->get_results("SELECT P.ID FROM (((SELECT post_author,ID FROM `{$wpdb->posts}`  WHERE post_status='publish' AND post_type='$post_type' $condition) AS P ) JOIN (SELECT ID FROM `{$wpdb->users}` WHERE LOCATE('{$value}',`display_name`)>0) AS U) WHERE U.ID=P.post_author");
                            if (empty($get_posts)) {
                                return;
                            } else {
                                $post_id = array();
                                foreach ($get_posts as $p) {
                                    $post_id[$p->ID] = 1;
                                }
                            }
                        }
                        elseif ($type === 'title' || $type === 'content') {
                            if ($type === 'content') {
                                add_filter('posts_search', array(__CLASS__, 'search_by_content'), 100, 2);
                            } else {
                                add_filter('posts_search', array(__CLASS__, 'search_by_title'), 100, 2);
                            }
                            $the_query = new WP_Query( array(
                                'fields' => 'ids',
                                'post_type' => $post_type,
                                'orderby' => 'ID',
                                'order' => 'ASC',
                                'include' => !empty($post_id) ? implode(',', array_keys($post_id)) : '',
                                's' => sanitize_text_field($value),
                                'nopaging' => 1
                            ));
                            if ( $the_query->have_posts() ){
                                $post_id = array();
                                while ( $the_query->have_posts() ){
                                    $the_query->the_post();
                                    $post_id[get_the_ID()] = 1;
                                }
                                wp_reset_postdata();
                            }else{
                                return;
                            }
                        }
                        else {
                            if ($type !== 'text' && $type !== 'textarea') {
                                if (!is_array($value)) {
                                    $value = array($value);
                                }
                                $options = array();
                                foreach ($args['options'] as $opt) {
                                    $options[$opt['id']] = 1;
                                }
                                foreach ($value as $k => &$ch_m) {
                                    if (!isset($options[$ch_m])) {
                                        unset($value[$k]);
                                    }
                                }
                                if (empty($value)) {
                                    return;
                                }
                            }
                            if ($type === 'text' || $type === 'textarea') {
                                $value = array($value);
                            }
							if ($type === 'checkbox' || $type === 'select') {
                                foreach ($value as $k => &$ch_m) {
                                    $value[$k] = '"'. $ch_m .'"'; // to fix #7153.
                                }
                            }
							$condition2 = $type === 'radio_button' ? "`meta_value` = '%s'" : "LOCATE('%s',`meta_value`)>0";
							$condition = !empty($post_id) ? ' AND post_id IN(' . implode(',', array_keys($post_id)) . ')' : '';
							foreach ($value as $ch) {
                                $ch = esc_sql( stripslashes_deep( $ch ) );
                                $get_values = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$meta_key' AND ". sprintf($condition2, $ch) ." $condition");
								if (!empty($get_values)) {
                                    $ids = array();
                                    foreach ($get_values as $val) {
                                        $ids[] = $val->post_id;
                                    }
                                    $ids = implode(',', $ids);

                                    $get_posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                                    if (!empty($get_posts)) {
										/* reset $post_id when $post loop iterates, meaning condition between different form fields is AND but different selections in the same field is OR */
										if ( isset( $meta_key_cache ) && $meta_key_cache !== $meta_key ) {
											$post_id = array();
										}
										$meta_key_cache = $meta_key;

                                        foreach ($get_posts as $p) {
                                            $post_id[$p->ID] = 1;
                                        }
                                    } else {
                                        return;
                                    }
                                } else {
									/* for Select fields continue the search, we're looking for *any* match with the selections */
									if ( $type !== 'select' ) {
										return;
									}
                                }
                            }
                        }
                        break;
                    case 'taxonomies':
                    case 'category':
						$query_args = array(
                            'fields' => 'ids',
                            'post_type' => $post_type,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'nopaging' => 1
                        );
                        if ($type !== 'taxonomies') {
                            $value = array($type => $value);
                        }
                        $tax_post_id = $post_id;
                        foreach ($value as $tax => $v) {

							/* fix Show All option in multiselect fields */
							if ( is_array( $v ) ) {
								$v = array_filter( $v );
							}

                            if (!empty($v) && in_array($tax, $post_taxonomies)) {
								$query_args['tax_query'] = array(
									array(
										'taxonomy' => $tax,
										'field' => 'slug',
										'terms' => $v,
										'operator' => isset( $post['tax_operator'][ $tax ] ) && 'AND' === $post['tax_operator'][ $tax ] ? 'AND' : 'IN',
									)
								);
                                $query_args['include'] = !empty($tax_post_id) ? implode(',', array_keys($tax_post_id)) : '';
								$posts_array = get_posts($query_args);
                                if (empty($posts_array)) {
                                    return;
                                }
                                $tax_post_id = array();
                                foreach ($posts_array as $p) {
                                    $tax_post_id[$p] = 1;
                                }
                            }
                        }
                        $post_id = $tax_post_id;
                        break;

                    case 'post_tag':
                        $query_args = array(
                            'fields' => 'ids',
                            'post_type' => $post_type,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'nopaging' => 1,
                            'include' => !empty($post_id) ? implode(',', array_keys($post_id)) : ''
                        );
						if ( isset( $post['tax_operator']['post_tag'] ) && 'AND' === $post['tax_operator']['post_tag'] ) {
							$query_args['tag_slug__and'] = $value;
						} else {
							$query_args['tag'] = $value;
						}
                        $posts_array = get_posts($query_args);
                        if (empty($posts_array)) {
                            return;
                        }

                        $post_id = array();
                        foreach ($posts_array as $p) {
                            $post_id[$p] = 1;
                        }
                        break;
                    case 'has':
                        if (!is_array($value)) {
                            $value = array($value);
                        }
                        $field_post_id = $post_id;

                        $query_args = array(
                            'fields' => 'ids',
                            'post_type' => $post_type,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'nopaging' => 1,
                            'meta_query' => array(
                                array(
                                    'key' => '_thumbnail_id',
                                    'compare' => 'EXISTS'
                                ),
                            )
                        );
                        foreach ($value as $field => $v) {
                            if ($v) {
                                $posts_array = array();
                                if ($field === 'comments') {
                                    $condition = $v === 'no' ? 'comment_count=0' : 'comment_count>0';
                                    $include = !empty($field_post_id) ? ' AND ID IN(' . implode(',', array_keys($field_post_id)) . ')' : '';
                                    $posts_array = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` = '$post_type' AND `post_status`='publish' AND $condition $include");
                                } elseif ($field === 'thumbnail') {
                                    $query_args['meta_query'][0]['compare'] = $v === 'no' ? 'NOT EXISTS' : 'EXISTS';
                                    $query_args['include'] = !empty($field_post_id) ? implode(',', array_keys($field_post_id)) : '';
                                    $posts_array = get_posts($query_args);
                                }
                                if (empty($posts_array)) {
                                    return;
                                }
                                $field_post_id = array();
                                foreach ($posts_array as $p) {
                                    if ($field === 'comments') {
                                        $field_post_id[$p->ID] = 1;
                                    } else {
                                        $field_post_id[$p] = 1;
                                    }
                                }
                            }
                        }
                        $post_id = $field_post_id;
                        break;
                    case 'number':
                        if (isset($cache['default']) && isset($cache['default'][$post_type][$slug]) && isset($value['from']) && isset($value['to'])) {
                            $id = 'ptb_' . $slug . '_' . $meta_key;
                            if (isset($cache['default'][$post_type][$slug][$id])) {
                                $min = floor($cache['default'][$post_type][$slug][$id]['min']);
                                $max = floor($cache['default'][$post_type][$slug][$id]['max']);
                                $from = floatval($value['from']);
                                $to = floatval($value['to']);
                                if ($from >= $min && $from <= $max && $to >= $min && $to <= $max) {
                                    if ($from == $min && $to == $max) {
                                        unset($post[$meta_key]);
                                        continue 2;
                                    }
                                    $condition = !empty($post_id) ? ' AND post_id IN(' . implode(',', array_keys($post_id)) . ')' : '';
                                    $get_values = $wpdb->get_results("SELECT `post_id`,`meta_value` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$meta_key' $condition");
                                    if (!empty($get_values)) {
                                        $ids = array();
                                        foreach ($get_values as $val) {
                                            $ids[] = $val->post_id;
                                        }
                                        $ids = implode(',', $ids);
                                        $get_posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                                        if (!empty($get_posts)) {
                                            $ids = $range_ids = array();
                                            foreach ($get_posts as $p) {
                                                $ids[$p->ID] = 1;
                                            }
                                            foreach ($get_values as $val) {
                                                if (isset($ids[$val->post_id])) {
                                                    $v = maybe_unserialize($val->meta_value);
                                                    $max = isset($v['to']) ? $v['to'] : $v;
                                                    $min = isset($v['from']) ? $v['from'] : $v;
                                                    $max = floor($max);
                                                    $min = floor($min);
                                                    if ((!$min || $from <= $min) && (!$max || $max <= $to)) {
                                                        $range_ids[$val->post_id] = 1;
                                                    }
                                                }
                                            }
                                            if (empty($range_ids)) {
                                                return;
                                            } else {
                                                $post_id = $range_ids;
                                            }
                                        } else {
                                            return;
                                        }
                                    } elseif ($condition) {
                                        return;
                                    }
                                } else {
                                    return;
                                }
                            } else {
                                return;
                            }
                        }
                        break;
                    default :
						$post_id = apply_filters('ptb_search_by_' . $type, $post_id, $post_type, $value, $args, $meta_key, $post_taxonomies);
                        if (empty($post_id)) {
                            return;
                        }
                        break;
                }
            }
            $post_id = array_keys($post_id);
        }
        $post_id = apply_filters('ptb_search_result', $post_id, $post, $cmb_options, $post_type, $slug, $post_taxonomies);
        wp_reset_postdata();

		return $post_id;
    }

	function set_active_form() {
        if ( ! isset( $_REQUEST['ptb-search'], $_REQUEST['f'] ) ) {
			return;
		}

		$slug = sanitize_key( $_REQUEST['f'] );
		$ptb_options = PTB::get_option();
		if ( ! isset( $ptb_options->option_post_type_templates[ $slug ]['search']['layout'] ) ) {
			return;
		}
		$post_type = $ptb_options->option_post_type_templates[ $slug ]['post_type'];

		$post_id = self::$cache_enabled ? PTB_Search_Options::get_query_cache($post_type, $_REQUEST) : false;
		$cmb_options = $post_support = $post_taxonomies = array();
		$ptb_options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
		$post_taxonomies[] = 'category';
		$post_taxonomies[] = 'post_tag';
		$cmb_options['has'] = array('type' => 'has');
		$cmb_options['content'] = array('type' => 'content');
		$post_support[] = 'has';
		$post_support[] = 'content';
		$cmb_options = apply_filters( 'ptb_search_render', $cmb_options, 'search', $post_type );
		$options = array();
		foreach ( $cmb_options as $key => $cmb ) {
			$name = isset($cmb['name']) ? PTB_Utils::get_label($cmb['name']) : ($key === 'has' ? $key : PTB_Search_Options::get_name($key));
			$name = $name ? sanitize_title( $name ) : $key;
			if ( $cmb['type'] === 'number' || $cmb['type'] === 'event_date' ) {
				$options[ $name . '-to' ] = $options[ $name . '-from' ] = array( 'label' => $name, 'key' => $key, 'type' => $cmb['type'] );
			} else {
				$options[ $name ] = array( 'key' => $key, 'type' => $cmb['type'] );
			}
		}

		$data = [];
		foreach ($_REQUEST as $k => $v) {
			$k = preg_replace( '/^' . self::$prefix . '/', '', $k );
			if ($v) {
				if (in_array($k, $post_taxonomies)) {
					if ($k !== 'category' && $k !== 'post_tag') {
						$data['taxonomies'][$k] = $v;
					} else {
						$data[$k] = $v;
					}
					$operator_key = self::$prefix . $k . '_operator';
					if ( isset( $_REQUEST[ $operator_key ] ) ) {
						$data['tax_operator'][ $k ] = $_REQUEST[ $operator_key ];
					}
				} else {
					if ( ! isset( $options[ $k ] ) ) {
						$k = preg_replace( '/(-from|-to)$/', '', $k );
					}

					if ( isset( $options[ $k ] ) ) {
						if ( $options[ $k ]['type'] !== 'number' && $options[ $k ]['type'] !== 'event_date' ) {
							$data[ $options[ $k ]['key'] ] = $v;
						} else {
							if ( isset( $_REQUEST[ self::$prefix . $options[ $k ]['label'] . '-from' ] ) ) {
								$data[ $options[ $k ]['key'] ]['from'] = $_REQUEST[ self::$prefix . $options[ $k ]['label'] . '-from' ];
							}
							if ( isset( $_REQUEST[ self::$prefix . $options[ $k ]['label'] . '-to' ] ) ) {
								$data[ $options[ $k ]['key'] ]['to'] = $_REQUEST[ self::$prefix . $options[ $k ]['label'] . '-to' ];
							}
						}
					}
				}
			}
		}

		self::$data = apply_filters( 'ptb_search_filter_by_slug', $data, $post_id, $options, $cmb_options, $post_support, $post_taxonomies );
		self::$active_form = compact( 'post_type', 'slug' );

		/* get search results, this returns an array of post IDs */
		self::$search_result = self::search( $post_type, $slug, self::$data, $post_id, $cmb_options, $post_support, $post_taxonomies );

		/* enable filtering [ptb] shortcode */
		add_filter( 'ptb_shortcode_args', array( $this, 'ptb_shortcode_args' ) );
		if ( is_singular() ) {
			add_filter( 'the_content', [ $this, 'missing_shortcode_notice' ], 1000 );
		}
	}

    public function ptb_shortcode_args( $shortcode_args ) {
		/* apply the search filter if: */
		if ( ! (
			self::$is_search_container === true /* we're inside [ptb-search-results] shortcode */
			|| ( is_archive() && isset( $shortcode_args['ptb_main_query'] ) && $shortcode_args['ptb_main_query'] === true ) /* main query on post type archive or taxonomy archive pages */
			|| ( $shortcode_args['post_type'] === self::$active_form['post_type'] && empty( $shortcode_args['ptb_main_query'] ) ) /* last resort: the [ptb] shortcode is displaying the same post type as the search form */
		) ) {
			return $shortcode_args;
		}

		/* get search form settings */
		$slug = self::$active_form['slug'];
		$options = PTB::get_option()->option_post_type_templates[ $slug ]['search'];
		$options = wp_parse_args( $options, [
			'ptb_ptt_order' => 'desc',
			'ptb_ptt_orderby' => 'date',
		] );

		/**
		 * create a custom query from the search results
		 * if search result returned empty, disable the [ptb] shortcode entirely
		 */
		$query_args = [
			'post_type' => self::$active_form['post_type'],
			'post__in' => self::$search_result === null ? [ 0 ] : self::$search_result,
			'paged' => get_query_var( 'paged' ),
			'order' => $options['ptb_ptt_order'],
			'orderby' => $options['ptb_ptt_orderby'],
		];
		if ( ! empty( $shortcode_args['posts_per_page'] ) ) {
			$query_args['posts_per_page'] = $shortcode_args['posts_per_page'];
		}

		/* sort results */
		if ( ! isset( PTB_Form_PTT_Archive::$sortfields[ $options['ptb_ptt_orderby'] ] ) ) {
			$query_args['meta_key'] = 'ptb_' . $options['ptb_ptt_orderby'];
			$field = ptb_get_field_definition( $options['ptb_ptt_orderby'], $query_args['post_type'] );
			/* when sorting by a Rating field type, use "{field}_rating" custom field instead which stores the overall rating of the post */
			if ( isset( $field['type'] ) && $field['type'] === 'rating' ) {
				$query_args['meta_key'] .= '_rating';
			}
			$query_args['orderby'] = isset( $field['type'] ) && $field['type'] === 'number' && empty( $field['type']['range'] ) ? 'meta_value_num' : 'meta_value';
		}

		$query = new WP_Query( $query_args );

		/* make [ptb] shortcode use this $query, this overrides all other query-related args passed to the shortcode */
		$shortcode_args['query'] = $query;

		if ( self::$search_result === null ) {
			$shortcode_args['not_found'] = isset( $options['ptb_ptt_no_result']['no_result'] ) ? PTB_Utils::get_label( $options['ptb_ptt_no_result']['no_result'] ) : __( 'No results found.', 'ptb-search' );
		}

		if ( is_singular() ) {
			/* disable the notice regarding missing [ptb] shortcode */
			remove_filter( 'the_content', [ $this, 'missing_shortcode_notice' ], 1000 );
		}

		return $shortcode_args;
    }

    public function pre_search($search, &$wp_query) {
        global $wpdb;
        if (empty($search)) {
            return $search; // skip processing - no search term in query
        }
        $q = $wp_query->query_vars;
        $n = !empty($q['exact']) ? '' : '%';
        $search = $searchand = '';
        foreach ((array) $q['search_terms'] as $term) {
            $term = esc_sql($wpdb->esc_like($term));
            $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
            $searchand = ' AND ';
        }
        if (!empty($search)) {
            $search = " AND ({$search}) ";
            if (!is_user_logged_in())
                $search .= " AND ($wpdb->posts.post_password = '') ";
        }
        return $search;
	}

	public static function get_options_by_slug( $slug = '' ) {
		if( $slug === '' && ! empty( $_REQUEST['f'] ) ) {
			$slug = sanitize_key( $_REQUEST['f'] );
		}

		if( empty ( $slug ) ) return;

		$ptb_options = PTB::get_option();
		if( ! empty( $ptb_options->option_post_type_templates[$slug] ) ) {
			return $ptb_options->option_post_type_templates[$slug];
		}
	}

	/**
	 * Return current page URL, without extra URL parameters
	 *
	 * @return string
	 */
	public static function current_page_url() {
		$url = false;
		$id = get_queried_object_id();
		if ( is_singular() ) {
			$url = get_permalink( $id );
		} else if ( is_category() || is_tag() || is_tax() ) {
			$url = get_term_link( $id );
		} else if ( is_post_type_archive() ) {
			$url = get_post_type_archive_link( get_queried_object()->name );
		}

		return $url;
	}

	/**
	 * Generate the warning message about missing [ptb] shortcode
	 *
	 * @return string
	 */
	public function missing_shortcode_notice_message() {
		$message = sprintf( __( 'Please put a [ptb] shortcode inside the [ptb-search-results] shortcode to show the search results. <a href="%s">More Info</a>', 'ptb-search' ), 'https://themify.me/docs/ptb-search#search-result-location' );
		$message = str_replace( [ '[', ']' ], [ '&#91;', '&#93;' ], $message ); /* prevent interpreting [] as shortcodes */
		return '<div class="ptb_notice">' . $message . '</div>';
	}

	/**
	 * Hooked to the_content(), displayed only if no [ptb] shortcode is found
	 *
	 * @return string
	 */
	public function missing_shortcode_notice( $content ) {
		if ( is_main_query() ) {
			if ( current_user_can( 'manage_options' ) ) {
				$content .= $this->missing_shortcode_notice_message();
			}
			remove_filter( 'the_content', [ $this, 'missing_shortcode_notice' ], 1000 );
		}

		return $content;
	}

	public static function get_active_form() {
		return self::$active_form;
	}
}