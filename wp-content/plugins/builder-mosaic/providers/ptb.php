<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Builder_Data_Provider_PTB extends Builder_Data_Provider {

	function __construct() {
		add_action( 'template_redirect', array( $this, 'post_lightbox_init' ), 100 );
	}

	function is_available() {
		return class_exists( 'PTB' );
	}

	function get_error() {
	    return sprintf( __( 'Please install <a href="%s">Post Type Builder</a> plugin.', 'builder-mosaic' ), 'https://themify.me/post-type-builder' );
	}

	function get_id() {
		return 'ptb';
	}

	function get_label() {
		return __( 'Post Type Builder', 'builder-mosaic' );
	}

	function get_options() {
		if ( ! $this->is_available() ) {
			return array(
				array(
					'type' => 'separator',
					'html' => '<p>' . $this->get_error(). '</p>'
				)
			);
		}

		$ptb = PTB::$options->get_custom_post_types();
		if ( empty( $ptb ) ) {
			return array(
				array(
					'type' => 'separator',
					'html' => '<p>' . sprintf( __( 'No post types have been added, you can <a href="%s">add new post types</a>.', 'builder-mosaic' ), admin_url( 'admin.php?page=ptb-cpt&action=add' ) ) . '</p>'
				)
			);
		}

		$fields = array();
		$lng = PTB_Utils::get_current_language_code();
		$post_types = array();
		$post_types_option = array();
		$post_types_settings = array();
		foreach ( $ptb as $post_type ) {
			if ( ! empty( $post_type->unregister ) ) {
				continue;
			}
			$templateObject = PTB::$options->get_post_type_template_by_type( $post_type->id );
			if ( ! $templateObject ) {
				continue;
			}
			$post_types_option[] = array(
				'name' => $post_type->plural_label[ $lng ],
				'value' => $post_type->id,
			);
			$post_types[ $post_type->id ] = $post_type->plural_label[ $lng ];
			$post_types_settings[ $post_type->id ] = array(
				'type' => 'group',
				'options' => array(),
				'wrap_class' => 'group_' . $post_type->id
			);
		}

		$fields[] = array(
			'id' => 'post_type_ptb',
			'type' => 'radio',
			'label' => __( 'Post Type', 'builder-mosaic' ),
			'options' => $post_types_option,
		);

		foreach ( $post_types as $post_type => $post_type_label ) {
			$pre = "__ptb_{$post_type}_";
			$data = PTB::$options->get_shortcode_data( $post_type );

			$post_types_settings[ $post_type ]['options'][] = array(
				'id' => "{$pre}orderby",
				'type' => 'select',
				'label' => __( 'Orderby', 'builder-mosaic' ),
				'options' => $this->_get_listbox( $data['data']['orderby']['values'] ),
			);
			$post_types_settings[ $post_type ]['options'][] = array(
				'id' => "{$pre}order",
				'type' => 'select',
				'label' => __( 'Order', 'builder-mosaic' ),
				'options' => $this->_get_listbox( $data['data']['order']['values'] ),
			);
			$post_types_settings[ $post_type ]['options'][] = array(
				'id' => "{$pre}posts_per_page",
				'type' => 'text',
				'label' => __( 'Posts Per Page', 'builder-mosaic' ),
				'class' => 'xsmall',
			);
			$post_types_settings[ $post_type ]['options'][] = array(
				'id' => "{$pre}offset",
				'type' => 'text',
				'label' => __( 'Offset', 'builder-mosaic' ),
				'class' => 'xsmall',
			);

			/*********************************************
			 * Taxonomies
			 ********************************************/
			if ( isset( $data['tax']['data'] ) && is_array( $data['tax']['data'] ) ) {
				$taxonomy_options = [];
				foreach ( $data['tax']['data'] as $value ) {
					if ( $value['type'] === 'multiselect' || $value['type'] === 'listbox' ) {
						$taxonomy_options[] = array(
							'id' => "{$pre}{$value['name']}",
							'type' => 'select',
							'label' => $value['label'],
							'options' => $this->_get_listbox( $value['values'] ),
							'multi' => $value['type'] === 'multiselect',
						);
					} elseif ( $value['type'] === 'radio' ) {
						$taxonomy_options[] = array(
							'id' => "{$pre}{$value['name']}",
							'type' => 'toggle_switch',
							'label' => $value['label'],
							'options' => array(
								'on' => array('name'=>1, 'value' =>'y'),
								'off' => array('name'=>'', 'value' =>'no'),
							),
						);
					}
				}
				if ( ! empty( $taxonomy_options ) ) {
					$post_types_settings[ $post_type ]['options'][] = array(
						'type' => 'separator',
						'html' => '<h3 class="mosaic_ptb_field_group"><span></span>' . __( 'Taxonomies', 'builder-mosaic' ) . '</h3>',
					);
					$post_types_settings[ $post_type ]['options'][] = [
						'type' => 'group',
						'options' => $taxonomy_options,
					];
				}
				unset( $taxonomy_options );
			}

			/*********************************************
			 * Fields
			 ********************************************/
			if ( isset( $data['field']['data'] ) && is_array( $data['field']['data'] ) ) {
				$post_types_settings[ $post_type ]['options'][] = array(
					'type' => 'separator',
					'html' => '<h3 class="mosaic_ptb_field_group"><span></span>' . __( 'Fields', 'builder-mosaic' ) . '</h3>',
				);
				$fields_settings = [];
				foreach ( $data['field']['data'] as $key => $value ) {
					$fields_settings[] = array(
						'id' => "multi_{$post_type}_field_{$key}",
						'type' => 'multi',
						'label' => $value['label'],
						'options' => $this->get_assoc_fields( $key, $value, 'field', $pre ),
					);
				}
				$post_types_settings[ $post_type ]['options'][] = [
					'type' => 'group',
					'options' => $fields_settings,
				];
				unset( $fields_settings );
			}

			/*********************************************
			 * Meta
			 ********************************************/
			if ( isset( $data['meta']['data'] ) && is_array( $data['meta']['data'] ) ) {
				$post_types_settings[ $post_type ]['options'][] = array(
					'type' => 'separator',
					'html' => '<h3 class="mosaic_ptb_field_group"><span></span>' . __( 'PTB Metaboxes', 'builder-mosaic' ) . '</h3>',
				);
				$meta_settings = [];
				foreach ( $data['meta']['data'] as $key => $value ) {
					$meta_settings[] = array(
						'id' => "multi_{$post_type}_field_{$key}",
						'type' => 'multi',
						'label' => $value['label'],
						'options' => $this->get_assoc_fields( $key, $value, 'meta', $pre ),
					);
				}
				$post_types_settings[ $post_type ]['options'][] = [
					'type' => 'group',
					'options' => $meta_settings,
				];
				unset( $meta_settings );
			}
		}

		$fields[] = array(
			'type' => 'ptb_mosaic_group',
			'options' => $post_types_settings,
		);

		$fields[] = array(
			'id' => 'ptb_lightbox_link',
			'type' => 'toggle_switch',
			'label' => __( 'Post Lightbox', 'builder-mosaic' ),
			'help' => __( 'Open post in lightbox window', 'builder-mosaic' ),
			'options' => array(
				'on' => array( 'value' =>'en', 'name' => '1' ),
				'off' => array( 'value' => 'dis', 'name' => '0' )
			),
			'binding' => array(
				'1' => array( 'show' => 'tb_ptb_lightbox_dim' ),
				'0' => array( 'hide' => 'tb_ptb_lightbox_dim' )
			),
			'control' => false
		);
		$fields[] = array(
			'type' => 'multi',
			'label' => __('Lightbox Dimension', 'builder-mosaic'),
			'wrap_class' => 'tb_ptb_lightbox_dim',
			'options' => array(
				array(
					'id' => 'ptb_lightbox_width',
					'type' => 'range',
					'label' =>'w',
					'control' =>false,
					'units' => array(
						'px' => array(
							'max' => 3500
						),
						'%' => ''
					)
				),
				array(
					'id' => 'ptb_lightbox_height',
					'type' => 'range',
					'label' => 'ht',
					'control' => false,
					'units' => array(
						'px' => array(
							'max' => 3500
						),
						'%' => ''
					)
				)
			),
		);

		/*********************************************
		 * Mapping
		 ********************************************/
		$fields[] = array(
			'id' => 'mosaic_ptb_field_group',
			'type' => 'separator',
			'meta' => array( 'html' => '<h3>' . __( 'Field Mapping', 'builder-mosaic' ) . '</h3>')
		);
		$title_fields = array( 'post_title' => __( 'Post Title', 'builder-mosaic' ) );
		$text_fields = array( 'post_excerpt' => __( 'Post Excerpt', 'builder-mosaic' ) );
		$image_fields = array( 'post_thumb' => __( 'Post Thumbnail', 'builder-mosaic' ) );
		$audio_fields = array();
		foreach ( $ptb as $post_type ) {
			if ( is_array( $post_type->meta_boxes ) ) {
				foreach ( $post_type->meta_boxes as $key => $field ) {
					$label = PTB_Utils::get_label($post_type->plural_label);
					$name = PTB_Utils::get_label($field['name']);
					if ( $field['type'] === 'text' && ! $field['repeatable'] ) {
						$title_fields[ $key ] = sprintf( '%s: %s', $label, $name );
						$text_fields[ $key ] = sprintf( '%s: %s', $label, $name );
					} elseif ( $field['type'] === 'image' ) {
						$image_fields[ $key ] = sprintf( '%s: %s', $label, $name );
					} elseif ( $field['type'] === 'textarea' ) {
						$text_fields[ $key ] = sprintf( '%s: %s', $label, $name );
					} elseif ( $field['type'] === 'audio' ) {
						$audio_fields[ $key ] = sprintf( '%s: %s', $label, $name );
					}
				}
			}
		}

		return array_merge( $fields, array(
			array(
				'id' => 'ptb_map_title',
				'type' => 'select',
				'label' => __( 'Display for Title', 'builder-mosaic' ),
				'options' => $title_fields,
			),
			array(
				'id' => 'ptb_map_text',
				'type' => 'select',
				'label' => __( 'Display for Text', 'builder-mosaic' ),
				'options' => $text_fields,
			),
			array(
				'id' => 'ptb_map_image',
				'type' => 'select',
				'label' => __( 'Display for Image', 'builder-mosaic' ),
				'options' => $image_fields,
			),
			array(
				'id' => 'ptb_map_audio',
				'type' => 'select',
				'label' => __( 'Display for Audio', 'builder-mosaic' ),
				'options' => $audio_fields,
				'help' => sprintf( __( 'Requires <a href="%s">PTB Extra Fields</a> addon.', 'builder-mosaic' ), 'https://themify.me/ptb-addons/extra-fields' ),
			),
		) );
	}

	function get_items( $settings, $limit, $paged ) {
		global $post;

		$settings = wp_parse_args( $settings, array(
			'post_type_ptb' => '',
			'ptb_map_title' => 'post_title',
			'ptb_map_text' => 'post_excerpt',
			'ptb_map_image' => 'post_thumb',
			'ptb_map_audio' => '',
			'ptb_lightbox_link' => '0',
			'ptb_lightbox_width' => '',
			'ptb_lightbox_height' => '',
			'ptb_lightbox_width_unit' => 'px',
			'ptb_lightbox_height_unit' => 'px',
		));
		if ( $settings['post_type_ptb'] === '' ) {
			return array();
		}

		/* filter out saved settings related to chosen post type */
		$len = strlen( $settings['post_type_ptb'] ) + 7; // "__ptb_{$post_type}_"
		$ptb_settings = array();
		foreach( $settings as $key => $value ) {
			if ( substr( $key, 0, $len ) === "__ptb_{$settings['post_type_ptb']}_" && ! empty( $value ) ) {
				$ptb_settings[ substr( $key, $len ) ] = $value;
			}
		}

		$ptb_settings['type'] = $settings['post_type_ptb'];
		$ptb_settings['return'] = 'query';
		$args = PTB_Public::get_instance()->ptb_shortcode( $ptb_settings );

		unset( $args['no_found_rows'] );
		$args['paged'] = $paged;
		$offset = isset( $args['offset'] ) ? $args['offset'] : 0;
		if ( $paged > 1 ) {
			$args['offset'] = ( ( $paged - 1 ) * $limit ) + $offset;
		}

		$query = new WP_Query( apply_filters( 'themify_builder_mosaic_query', $args, $settings ) );
		
		$items = array();
		if ( $query->have_posts() ) {
			global $ThemifyBuilder;
			$isLoop=$ThemifyBuilder->in_the_loop===true;
			$ThemifyBuilder->in_the_loop = true;
			if ( is_object( $post ) ){
				$saved_post = clone $post;
			}
			while( $query->have_posts() ) { 
				$query->the_post();
				// generate the data fields
				$link = get_permalink();
				$title = $settings['ptb_map_title'] !== 'post_title'
							? get_post_meta( get_the_id(), "ptb_{$settings['ptb_map_title']}", true )
							: get_the_title();

				$image = $settings['ptb_map_image'] !== 'post_thumb'
							? get_post_meta( get_the_id(), "ptb_{$settings['ptb_map_image']}", true )
							: ( has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_id(), 'full' ) : '' );

				$text = $settings['ptb_map_text'] !== 'post_excerpt'
							? get_post_meta( get_the_id(), "ptb_{$settings['ptb_map_text']}", true )
							: get_the_excerpt();

				$audio = '';
				if ( ! empty( $settings['ptb_map_audio'] ) ) {
					if ( $audio = get_post_meta( get_the_id(), "ptb_{$settings['ptb_map_audio']}", true ) ) {
						if ( ! empty( $audio['url'][0] ) ) {
							$audio = $audio['url'][0];
							$link = ''; /*@note, this is a hack: removes the overlay link in tile, making the audio player interactable */
						}
					}
				}

				$lightbox_size = '';
				if ( $settings['ptb_lightbox_link'] ) {
					$link = add_query_arg( array( 'tb-mosaic-ptb-lightbox' => 1 ), $link );
					if ( ! empty( $settings['ptb_lightbox_width'] ) || ! empty( $settings['ptb_lightbox_height'] ) ) {
						$lightbox_size = sprintf( '%s|%s', $settings['ptb_lightbox_width'] . $settings['ptb_lightbox_width_unit'], $settings['ptb_lightbox_height'] . $settings['ptb_lightbox_height_unit'] );
					}
				}

				$items[] = array(
					'title' => $title,
					'image' => $image,
					'text' => '<p>' . $text . '</p>',
					'link' => $link,
					'link_lightbox' => $settings['ptb_lightbox_link'] ? true : false,
					'lightbox_size' => $lightbox_size,
					'audio' => $audio,
					'css_classes' => str_replace( 'post ', '', join( ' ', get_post_class() ) ),
				);
			}
			$ThemifyBuilder->in_the_loop = $isLoop;
			if ( isset( $saved_post ) && is_object( $saved_post ) ) {
				$post = $saved_post;
				setup_postdata( $saved_post );
			}
		}
		wp_reset_postdata();

		return array(
			'items' => $items,
			'total_items' => $query->found_posts,
		);
	}

	function get_assoc_fields( $key, $field, $type, $pre ) {
		$fields = array();
		if ( ! ( isset( $field['hide_exist'] ) && $field['hide_exist'] ) ) {
			$fields[] = array(
				'id' => "{$pre}ptb_{$type}_{$key}_exists",
				'type' => 'toggle_switch',
				'label' => __( 'Exists', 'builder-mosaic' ),
				'options' => array(
					'on' => array( 'name' => 1, 'value' => 'y' ),
					'off' => array( 'name' => '', 'value' => 'no' )
				),
			);
		}
		if ( ! ( isset( $field['hide'] ) && $field['hide'] ) ) {
			if ( $field['type'] === 'listbox' || $field['type'] === 'multiselect' ) {
				$fields[] = array(
					'id' => "{$pre}ptb_{$type}_{$key}",
					'type' => 'select',
					'label' => __( 'Value', 'builder-mosaic' ),
					'options' => $this->_get_listbox( $field['values'] ),
					'multi' => $field['type'] === 'multiselect',
				);
			} elseif ( $field['type'] === 'number' ) {
				$fields[] = array(
					'id' => "{$pre}{$key}_from_sign",
					'type' => 'select',
					'label' => __( 'From', 'builder-mosaic' ),
					'options' => array(
						'=' => '=',
						'>=' => '>=',
						'>' => '>',
					),
				);
				$fields[] = array(
					'id' => "{$pre}ptb_{$type}_{$key}_from",
					'type' => 'text',
					'label' => __( 'From', 'builder-mosaic' ),
					'dc' => false,
				);
				$fields[] = array(
					'id' => "{$pre}{$key}_to_sign",
					'type' => 'select',
					'label' => __( 'To', 'builder-mosaic' ),
					'options' => array(
						'<=' => '<=',
						'<' => '<',
					),
				);
				$fields[] = array(
					'id' => "{$pre}ptb_{$type}_{$key}_to",
					'type' => 'text',
					'label' => __( 'To', 'builder-mosaic' ),
					'dc' => false,
				);
			} else {
				$fields[] = array(
					'id' => "{$pre}{$key}_slike",
					'type' => 'select',
					'label' => __( '%LIKE', 'builder-mosaic' ),
					'options' => array(
						'' => '',
						'%' => '%'
					),
				);
				$fields[] = array(
					'id' => "{$pre}ptb_{$type}_{$key}",
					'type' => 'text',
					'label' => __( 'Value', 'builder-mosaic' ),
					'dc' => false,
				);
				$fields[] = array(
					'id' => "{$pre}{$key}_elike",
					'type' => 'select',
					'label' => __( 'LIKE%', 'builder-mosaic' ),
					'options' => array(
						'' => '',
						'%' => '%'
					),
				);
			}
		}

		return $fields;
	}

	function _get_listbox( $arr ) {
		$output = array();
		foreach ( $arr as $r ) {
			$output[ $r['value'] ] = $r['text'];
		}
		return $output;
	}
	/**
	 * Handle display of single post lightbox
	 *
	 * @hook to template_redirect
	 */
	function post_lightbox_init() {
		if ( self::is_lightbox('tb-mosaic-ptb-lightbox') && is_singular() ) {
			show_admin_bar( false );
			add_filter( 'template_include', array( $this, 'template_include' ), 100 );
		}
	}

	/**
	 * Template to display in the lightbox window
	 *
	 * @return string
	 */
	function template_include( $file ) {
		return trailingslashit( dirname( __FILE__ ) ) . 'templates/ptb-lightbox.php';
	}
}