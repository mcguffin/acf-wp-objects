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

class SelectNavMenuLocation extends \acf_field_select {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'nav_menu_location_select';
		$this->label = __("Select Nav Menu Location",'acf-wp-objects');
		$this->category = __('WordPress', 'acf-wp-objects' );
		$this->defaults = [
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'return_format'	=> 'object',
		];

		// ajax
		add_action('wp_ajax_acf/fields/nav_menu_location_select/query',			[ $this, 'ajax_query' ] );
		add_action('wp_ajax_nopriv_acf/fields/nav_menu_location_select/query',	[ $this, 'ajax_query' ] );

		add_filter('acf/update_field/type=' . $this->name, [ $this, 'update_field' ] );

	}


	/**
	 *	@inheritdoc
	 */
	function load_field( $field ) {

		$choices = get_registered_nav_menus();

		$field['choices'] = $choices;

		return $field;

	}

	/**
	 *	@inheritdoc
	 */
	function render_field( $field ) {
		return parent::render_field( $this->load_field( $field ) );
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
				'id'			=> __("ID",'acf-wp-objects'),
				'name'			=> __("Slug",'acf-wp-objects'),
				'label'			=> __("Label",'acf-wp-objects'),
				'object'		=> __("Object",'acf-wp-objects'),
			],
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
		if( acf_is_empty($value) ) return $value;


		// vars
		$label = acf_maybe_get($field['choices'], $value, $value);


		// value
		if( $field['return_format'] == 'label' ) {

			// label
			$value = get_taxonomy( $value )->label;

		} elseif( $field['return_format'] == 'name' ) {

			// do nothing

		} elseif( $field['return_format'] == 'object' ) {
			$value = get_taxonomy( $value );

		}
		// return
		return $value;

	}


	/**
	 *	@filter acf/update_field/type=nav_menu_select
	 */
	public function update_field( $field ) {
		$field['choices'] = [];
		return $field;
	}

}
