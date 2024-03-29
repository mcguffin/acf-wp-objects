<?php
/**
 *	@package ACFWPObjects\Compat\Fields
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Fields;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF;

class SelectImageSize extends \acf_field_select {

	private $all_sizes = [];

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'image_size_select';
		$this->label = __("Select Image Size",'acf-wp-objects');
		$this->category = __('WordPress', 'acf-wp-objects' );
		$this->defaults = [
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'return_format'	=> 'array',

			'image_sizes'	=> [],
			'pick'			=> 0,
			'named'			=> '',
			'crop'			=> '',
			'_builtin'		=> '',
		];


		// ajax
		add_action('wp_ajax_acf/fields/image_size_select/query',		[ $this, 'ajax_query' ] );
		add_action('wp_ajax_nopriv_acf/fields/image_size_select/query',	[ $this, 'ajax_query' ] );

	}



	/**
	 *	@inheritdoc
	 */
	function load_field( $field ) {

		$wp = Core\WP::instance();

		$args_keys = [
			'_builtin',
			'named',
			'crop',
		];

		if ( $field['pick'] ) {
			if ( empty( $field['image_sizes'] ) ) {
				$choices = $wp->get_image_sizes( [], 'label' );
			} else {
				$choices = $wp->get_image_sizes( [ 'names' => $field['image_sizes'] ], 'label' );
			}
		} else {

			$args = [];

			foreach ( $args_keys as $key ) {

				if ( $field[$key] !== '' ) {
					$args[ $key ] = boolval( intval( $field[$key] ) );
				}
			}

			$choices = $wp->get_image_sizes( $args, 'label' );
		}

 		if ( ! ACF\ACF::instance()->is_fieldgroup_admin() ) {
			$field['choices'] = $choices;
		}

		// return
		return $field;

	}



	/**
	 *	@inheritdoc
	 */
	function render_field_settings( $field ) {

		$wp = Core\WP::instance();

		// allow_null
		acf_render_field_setting( $field, [
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		]);


		// multiple
		acf_render_field_setting( $field, [
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
		]);

		// return_format
		acf_render_field_setting( $field, [
			'label'			=> __('Return Value','acf-wp-objects'),
			'instructions'	=> __('Specify the returned value on front end','acf-wp-objects'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> [
				'array'			=> __("Size Array",'acf-wp-objects'),
				'slug'			=> __("Slug",'acf-wp-objects'),
			],
		]);




		// return_format
		acf_render_field_setting( $field, [
			'label'			=> __('Pick from List','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'pick',
			'ui'			=> 1,
		]);

		// default_value
		acf_render_field_setting( $field, [
			'label'			=> __('Select Image Sizes','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'image_sizes',
			'choices'		=> $wp->get_image_sizes( [], 'label' ),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All Image Sizes",'acf'),
			'conditions'	=> [
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 1
			]
		]);


		acf_render_field_setting( $field, [
			'label'			=> __('Named ','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> [
				''		=> __( 'Any', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			],
			'name'			=> 'named',
			'ui'			=> 1,
			'conditions'	=> [
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			]
		]);

		acf_render_field_setting( $field, [
			'label'			=> __('Cropped ','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> [
				''		=> __( 'Any', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			],
			'name'			=> 'crop',
			'ui'			=> 1,
			'conditions'	=> [
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			]
		]);

		acf_render_field_setting( $field, [
			'label'			=> __('Builtin','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> [
				''		=> __( 'Any', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			],
			'name'			=> '_builtin',
			'ui'			=> 1,
			'conditions'	=> [
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			]
		]);


		// ajax
		acf_hidden_input([
			'name'			=> 'ajax',
			'value'			=> 0,
		]);


	}

	/**
	 *	@inheritdoc
	 */
	function format_value_single( $value, $post_id, $field ) {

		// bail ealry if is empty
		if( acf_is_empty( $value ) ) {
			return $value;
		}


		// value
		if( $field['return_format'] == 'array' ) {
			$wp = Core\WP::instance();
			// do nothing
			$sizes = $wp->get_all_image_sizes();
			if ( isset( $sizes[ $value ] ) ) {
				$value = $sizes[ $value ];
			}

			// label
		} elseif( $field['return_format'] == 'slug' ) {

		}
		// return
		return $value;

	}

}
