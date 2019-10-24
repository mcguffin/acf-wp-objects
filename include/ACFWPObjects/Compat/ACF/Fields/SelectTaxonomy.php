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

class SelectTaxonomy extends \acf_field_select {

	function initialize() {

		// vars
		$this->name = 'taxonomy_select';
		$this->label = __("Select Taxonomy",'acf');
		$this->category = __('WordPress', 'acf-wp-objects' );
		$this->defaults = array(
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'return_format'	=> 'object',

			'_builtin'	=> '',
			'public'	=> '',
			'show_ui'	=> '',
			'show_in_menu'	=> '',
			'show_in_nav_menus'	=> '',
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
			'public',
			'show_ui',
			'show_in_menu',
			'show_in_nav_menus',
		);

		if ( $field['pick'] ) {
			if ( empty( $field['taxonomies'] ) ) {
				$choices = $core->get_taxonomies( array(), 'label' );
			} else {
				$choices = $core->get_taxonomies( array( 'names' => $field['taxonomies'] ), 'label' );
			}
		} else {

			$args = array();

			foreach ( $args_keys as $key ) {
				if ( $field[$key] !== '' ) {
					$args[ $key ] = boolval( intval( $field[$key] ) );
				}
			}
			$choices = $core->get_taxonomies( $args, 'label' );
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
				'label'			=> __("Label",'acf-wp-objects'),
				'name'			=> __("Slug",'acf-wp-objects'),
				'object'		=> __("Object",'acf-wp-objects')
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
			'label'			=> __('Select Taxonomies','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomies',
			'choices'		=> $core->get_taxonomies( array(), 'label' ),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All Taxonomies",'acf'),
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 1
			)
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Public','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'public',
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



		acf_render_field_setting( $field, array(
			'label'			=> __('Show UI','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'show_ui',
			'ui'			=> 1,
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			)
		));


		acf_render_field_setting( $field, array(
			'label'			=> __('Show in Menus','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'show_in_menu',
			'ui'			=> 1,
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			)
		));


		acf_render_field_setting( $field, array(
			'label'			=> __('Show in Nav Menus','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'show_in_nav_menus',
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
		if( acf_is_empty($value) ) return $value;


		// vars
		$label = acf_maybe_get($field['choices'], $value, $value);


		// value
		if( $field['return_format'] == 'label' ) {

			// do nothing

			// label
		} elseif( $field['return_format'] == 'name' ) {

			$value = get_taxonomy( $value )->label;

		} elseif( $field['return_format'] == 'object' ) {
			$value = get_taxonomy( $value );

		}
		// return
		return $value;

	}

}
