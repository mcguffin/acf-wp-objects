<?php

namespace ACFWPObjects\Compat\ACF;

use ACFWPObjects\Core;

class ImageSweetSpot extends Core\Singleton {

	private $field_name = 'acf_wpo_sweet_spot';

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_filter( 'wp_get_attachment_image_attributes', [ $this, 'add_position_style' ], 10, 2 );

		add_action( 'acf/init', [ $this, 'init' ], 20 );
	}

	/**
	 *	@action acf/init
	 */
	public function init() {
		acf_add_local_field_group([
			'key' => 'group_acf_wp_objects__image',
		    'title' => 'Image',
		    'fields' => [
		        [
		            'key' => 'field_acf_wp_objects__image_sweet_spot',
		            'label' => __( 'Sweet Spot', 'acf-wp-objects' ),
		            'name' => $this->field_name,
		            'type' => 'image_sweet_spot',
		            'instructions' => '',
		            'required' => 0,
		            'conditional_logic' => 0,
		            'wrapper' => [
		                'width' => '',
		                'class' => '',
		                'id' => ''
		            ],
		            'multiple' => 0,
		            'allow_null' => 0,
		            'default_value' => [
		                'x' => 50,
		                'y' => 50
		            ],
		            'ui' => 0,
		            'ajax' => 0,
		            'placeholder' => '',
		            'return_format' => 'object'
		        ]
		    ],
		    'location' => [
		        [
		            [
		                'param' => 'attachment',
		                'operator' => '==',
		                'value' => 'image'
		            ]
		        ]
		    ],
		    'menu_order' => 0,
		    'position' => 'normal',
		    'style' => 'default',
		    'label_placement' => 'top',
		    'instruction_placement' => 'label',
		]);
	}

	/**
	 *	@filter wp_get_attachment_image_attributes
	 */
	public function add_position_style( $attributes, $attachment ) {
		if ( ! $sweet_spot = get_field( $this->field_name, $attachment->ID ) ) {
			return $attributes;
		}

		$x = $y = 50;
		if ( isset( $sweet_spot['x'] ) ) {
			$x = $sweet_spot['x'];
		}
		if ( isset( $sweet_spot['y'] ) ) {
			$y = $sweet_spot['y'];
		}

		if ( ! isset( $attributes['style'] ) ) {
			$attributes['style'] = '';
		}
		$attributes['style'] = sprintf('object-position:%d%% %d%%;', $x, $y ) . $attributes['style'];

		return $attributes;
	}
}
