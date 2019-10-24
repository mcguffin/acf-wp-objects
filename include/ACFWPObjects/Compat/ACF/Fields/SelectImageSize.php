<?php
/**
 *	@package ACFWPObjects\Compat\Fields
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF\Fields;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;

class SelectImageSize extends \acf_field_select {

	private $all_sizes = array();

	function initialize() {

		// vars
		$this->name = 'image_size_select';
		$this->label = __("Select Image Size",'acf-wp-objects');
		$this->category = __('WordPress', 'acf-wp-objects' );
		$this->defaults = array(
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'return_format'	=> 'array',

			'image_sizes'	=> array(),
			'pick'			=> 0,
			'named'			=> '',
			'crop'			=> '',
			'_builtin'		=> '',
		);
	}


	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function render_field( $field ) {

		$core = Core\Core::instance();

		$args_keys = array(
			'_builtin',
			'named',
			'crop',
		);

		if ( $field['pick'] ) {
			if ( empty( $field['image_sizes'] ) ) {
				$choices = $core->get_image_sizes( array(), 'label' );
			} else {
				$choices = $core->get_image_sizes( array( 'names' => $field['image_sizes'] ), 'label' );
			}
		} else {

			$args = array();

			foreach ( $args_keys as $key ) {

				if ( $field[$key] !== '' ) {
					$args[ $key ] = boolval( intval( $field[$key] ) );
				}
			}

			$choices = $core->get_image_sizes( $args, 'label' );
		}

		$field['choices'] = $choices;

		parent::render_field( $field );

	}



	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/

	function render_field_settings( $field ) {

		$core = Core\Core::instance();

		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));


		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));


		// ui
		acf_render_field_setting( $field, array(
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'name'			=> 'ui',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));


		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf-wp-objects'),
			'instructions'	=> __('Specify the returned value on front end','acf-wp-objects'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'array'			=> __("Size Array",'acf-wp-objects'),
				'slug'			=> __("Slug",'acf-wp-objects'),
			),
		));




		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Pick from List','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'pick',
			'ui'			=> 1,
		));

		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Select Image Sizes','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'image_sizes',
			'choices'		=> $core->get_image_sizes( array(), 'label' ),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All Image Sizes",'acf'),
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 1
			)
		));


		acf_render_field_setting( $field, array(
			'label'			=> __('Named ','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'named',
			'ui'			=> 1,
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			)
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Cropped ','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'crop',
			'ui'			=> 1,
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			)
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Builtin','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> '_builtin',
			'ui'			=> 1,
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			)
		));


		// ajax
		acf_hidden_input(array(
			'name'			=> 'ajax',
			'value'			=> 0,
		));


	}

	function format_value_single( $value, $post_id, $field ) {

		// bail ealry if is empty
		if( acf_is_empty($value) ) {
			return $value;
		}


		// value
		if( $field['return_format'] == 'array' ) {

			// do nothing
			$sizes = $this->get_all_image_sizes();
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
