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
use ACFWPObjects\Compat\ACF;

class SelectRole extends \acf_field_select {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'role_select';
		$this->label = __("Select Role",'acf');
		$this->category = __('WordPress', 'acf-wp-objects' );
		$this->defaults = [
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
		];

		// ajax
		add_action('wp_ajax_acf/fields/role_select/query',			[ $this, 'ajax_query' ] );
		add_action('wp_ajax_nopriv_acf/fields/role_select/query',	[ $this, 'ajax_query' ] );


	}


	/**
	 *	@inheritdoc
	 */
	function load_field( $field ) {

		$core_roles = Core\Core::instance()->get_roles();


		$field['choices'] = $core_roles;

		if (  ! ACF\ACF::instance()->is_fieldgroup_admin() && ! empty( $field['roles'] ) ) {
			$field['choices'] = array_intersect_key( $core_roles, array_flip( $field['roles'] ) );
		}

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


		// ui
		acf_render_field_setting( $field, [
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'name'			=> 'ui',
			'type'			=> 'true_false',
			'ui'			=> 1,
		]);

		// ajax
		acf_render_field_setting( $field, [
			'label'			=> __('Use AJAX to lazy load choices?','acf'),
			'instructions'	=> '',
			'name'			=> 'ajax',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'conditions'	=> [
				'field'		=> 'ui',
				'operator'	=> '==',
				'value'		=> 1
			]
		]);

		// return_format
		acf_render_field_setting( $field, [
			'label'			=> __('Return Value','acf-wp-objects'),
			'instructions'	=> __('Specify the returned value on front end','acf-wp-objects'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> [
				'label'			=> __("Label",'acf-wp-objects'),
				'name'			=> __("Slug",'acf-wp-objects'),
				'object'		=> __("Role Object",'acf-wp-objects')
			],
		]);

		// default_value
		acf_render_field_setting( $field, [
			'label'			=> __('Select User Role','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'roles',
			'choices'		=> $wp->get_roles(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All Roles",'acf')
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
		if ( acf_is_empty( $value ) ) {
			return $value;
		}
		// check if is role
		if ( ! wp_roles()->is_role( $value ) ) {
			return null;
		}

		// value
		if( $field['return_format'] == 'label' ) {
			$names = wp_roles()->get_names();
			$value = $names[ $value ];

		} else if ( $field['return_format'] == 'name' ) {
			// do nothing

		} else if ( $field['return_format'] == 'object' ) {

			$value = get_role( $value );

		}
		// return
		return $value;

	}

}
