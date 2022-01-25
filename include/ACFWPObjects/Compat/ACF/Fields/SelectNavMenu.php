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

class SelectNavMenu extends \acf_field_select {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'nav_menu_select';
		$this->label = __("Select Nav-Menu",'acf-wp-objects');
		$this->category = __('WordPress', 'acf-wp-objects' );
		$this->defaults = [
			'allow_null' 	=> 0,
			'multiple'		=> 0,
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'return_format'	=> 'object',
			'menu_location'	=> '',
		];

		// ajax
		add_action('wp_ajax_acf/fields/nav_menu_select/query',			[ $this, 'ajax_query' ] );
		add_action('wp_ajax_nopriv_acf/fields/nav_menu_select/query',	[ $this, 'ajax_query' ] );

		add_filter('acf/get_field_label', [ $this, 'get_field_label' ], 10, 3 );

		add_filter('acf/update_field/type=' . $this->name, [ $this, 'update_field' ] );
	}

	/**
	 *	@filter acf/get_field_label
	 */
	public function get_field_label( $label, $field, $context ) {

		if ( ACF\ACF::instance()->is_fieldgroup_admin() ) {

			return $label;

		}

		if ( $field['type'] === $this->name && $field['menu_location'] ) {
			$edit_link = add_query_arg([
					'action'	=> 'edit',
					'menu'		=> $field['value'],
				],
				admin_url( 'nav-menus.php' )
			);

			$create_link = add_query_arg([
					'action' 		=> 'edit',
					'menu'			=> 0,
					'use-location'	=> $field['menu_location'],
				],
				admin_url( 'nav-menus.php' )
			);
			$label .= sprintf(
				'<a class="edit-menu-link %s" href="%s">%s</a>',
				$field['value'] ? '' : 'acf-hidden',
				$edit_link,
				__( 'Edit','acf-wp-objects' )
			);
			$label .= sprintf( '<a class="create-menu-link %s" href="%s">%s</a>',
				! $field['value'] ? '' : 'acf-hidden',
				$create_link,
				__( 'Create Menu','acf-wp-objects' )
			);
		}
		return $label;
	}

	/**
	 *	@inheritdoc
	 */
	function load_field( $field ) {

		$nav_menus = wp_get_nav_menus();
		$menu_names = array_map( function( $nav_menu ) {
			return $nav_menu->name;
		}, $nav_menus );
		$menu_ids = array_map( function( $nav_menu ) {
			return $nav_menu->term_id;
		}, $nav_menus );

		$field['choices'] = array_combine(
			$menu_ids,
			$menu_names
		);

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

		acf_render_field_setting( $field, [
			'label'			=> __('Menu Location','acf-wp-objects'),
			'instructions'	=> __('Save selected menu in in menu location','acf-wp-objects'),
			'type'			=> 'nav_menu_location_select',
			'name'			=> 'menu_location',
			'allow_null'	=> 1,
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
	function load_value( $value, $post_id, $field ) {
		if ( $field['menu_location'] ) {
			$locations = get_nav_menu_locations();
			if ( isset( $locations[ $field['menu_location'] ] ) ) {
				return $locations[ $field['menu_location'] ];
			}
			return '';
		}
		return $value;
	}


	/**
	 *	@inheritdoc
	 */
	function update_value( $value, $post_id, $field ) {

		$ret = parent::update_value( $value, $post_id, $field );

		if ( current_user_can('edit_theme_options') && $field['menu_location'] ) {

			$locations = get_nav_menu_locations();

			if ( $value ) {
				$locations[ $field['menu_location'] ] = $value;
			} else {
				$locations[ $field['menu_location'] ] = '';
			}
			set_theme_mod( 'nav_menu_locations', $locations );
			return null;
		}

		return $ret;

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
		if( $field['return_format'] == 'id' ) {

			return $value;

		}
		$term = get_term( $value );
		if ( is_wp_error( $term ) ) {
			return 0;
		}
		if ( $field['return_format'] == 'name' ) {

			return $term->slug;

		} else if ( $field['return_format'] == 'label' ) {

			// do nothing
			return $term->name;

		} else if ( $field['return_format'] == 'object' ) {

			return $term;

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
