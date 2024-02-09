<?php

class Builder_Timeline_Text_Source {

	public function get_id() {
		return 'text';
	}

	public function get_name() {
		return __( 'Text', 'builder-timeline' );
	}

	public function get_items( $args ) {
		$items = array();
                if(!empty($args['text_source_timeline'])){
                    foreach( $args['text_source_timeline'] as $key => $item ) {
                            $item = wp_parse_args( $item, array(
                                'image_timeline' => '',
                                'title_timeline' => '',
                                'icon_timeline' => '',
                                'iconcolor_timeline' => '',
                                'icontextcolor_timeline' => '',
                                'date_timeline' => '',
                                'content_timeline' => '',
                                'link_timeline' => ''
                            ) );
                            $items[] = array(
                                    'id' => $key,
                                    'title' => $item['title_timeline'],
                                    'icon' => $item['icon_timeline'],
                                    'icon_color' => $item['iconcolor_timeline'],
                                    'icon_text_color' => $item['icontextcolor_timeline'],
                                    'link' => '' !== $item['link_timeline'] ? $item['link_timeline'] : null,
                                    'date' => $item['date_timeline'],
                                    'date_formatted' => $item['date_timeline'],
                                    'hide_featured_image' => $item['image_timeline'] === 'yes',
                                    'image' => $item['image_timeline'],
                                    'hide_content' =>$item['content_timeline'] === 'none',
                                    'content' => apply_filters( 'themify_builder_module_content', $item['content_timeline'] ),
                            );
                    }
                }
		return apply_filters( 'builder_timeline_source_text_items', $items );
	}

	public function get_options() {
		return array(
			array(
				'id' => 'text_source_timeline',
				'type' => 'builder',
				'options' => array(
					array(
						'id' => 'title_timeline',
						'type' => 'text',
						'label' => __('Title', 'builder-timeline'),
						'class' => 'large',
						'control' => array(
							'selector'=>'.module-timeline-title'
						)
					),
					array(
						'id' => 'link_timeline',
						'type' => 'text',
						'label' => __('Link', 'builder-timeline'),
						'class' => 'large'
					),
					array(
						'id' => 'icon_timeline',
						'type' => 'icon',
						'label' => __('Icon', 'builder-timeline'),
						'wrap_class' => 'tb_group_element_list'
					),
					array(
						'id' => 'iconcolor_timeline',
						'type' => 'color',
						'label' => __('Icon Background Color', 'builder-timeline'),
						'wrap_class' => 'tb_group_element_list'
					),
					array(
						'id' => 'icontextcolor_timeline',
						'type' => 'color',
						'label' => __('Icon Color', 'builder-timeline'),
						'wrap_class' => 'tb_group_element_list'
					),
					array(
						'id' => 'date_timeline',
						'type' => 'text',
						'label' => __('Date', 'builder-timeline'),
						'help' => __( '(eg. Sep 2014)', 'builder-timeline' ),
						'control' => array(
							'selector'=>'.module-timeline-date'
						)
					),
					array(
						'id' => 'image_timeline',
						'type' => 'image',
						'label' => __('Image', 'builder-timeline'),
					),
					array(
						'id' => 'content_timeline',
						'type' => 'wp_editor',
					)
				),
				'wrap_class' => 'tb_group_element_text'
			)
		);
	}

}
