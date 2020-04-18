<?php

namespace ACFWPObjects\Compat\ACF\Fields;

use ACFWPObjects\Asset;
use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF;

class TemplateFileSelect extends \acf_field_select {

	private static $_resolved = array();

	function initialize() {

		// vars
		$this->name = 'template_file_select';
		$this->label = __( 'Select Template File', 'acf-wp-objects');
		$this->category = __('Generic', 'acf-wp-objects' );
		$this->defaults = array(
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',

			'choices'		=> [],
			'return_format'	=> 'value',
			'template_type'	=> 'Template Name'
		);

		add_filter( 'acf/load_fields', [ $this, 'resolve_fields' ], 3 );

	}



	/**
	 *	@inheritdoc
	 */
	function load_field( $field ) {

		if ( $this->should_resolve() ) {

			$field['choices'] = $this->get_template_choices( $field );

			$this->get_template_settings_group( $field );

		}

		return $field;

	}

	/**
	 *	@inheritdoc
	 */
	function render_field_settings( $field ) {

		// placeholder
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Template Type', 'acf-wp-objects' ),
			'instructions'	=> '', //__( 'Filter Template files having this specific header key.', 'acf-wp-objects' ),
			'type'			=> 'select',
			'choices'		=> $this->get_template_type_choices(),
			'name'			=> 'template_type',
		));

	}

	/**
	 *	Replace includer field with fields from field group
	 *
	 *	@filter --acf/load_fields--
	 */
	public function resolve_fields( $fields ) {

		if ( ! $this->should_resolve() ) {

			return $fields;
		}

		$core = Core\Core::instance();
		$return_fields = [];
		foreach ( $fields as $field ) {

			if ( $this->name === $field['type'] ) {

				$field['type'] = 'select'; // 1. Because location rules work, 2. Because it won't resolve twice

				$return_fields[] = $field;

				if ( in_array( $field['key'], self::$_resolved ) ) {
					continue;
				}
				self::$_resolved[] = $field['key'];

				$group_field = $this->get_template_settings_group( $field );

				$return_fields[] = $group_field;

			} else {
				$return_fields[] = $field;
			}
		}
		return $return_fields;
	}

	/**
	 *	Add an ACF loca field with template settings
	 *
	 *	@param array $template_select_field
	 */
	private function get_template_settings_group( $template_select_field ) {

		$core_template = Core\Template::instance();

		$template_type = $core_template->get_template_type( $template_select_field['template_type'] );

		$header_key = $template_type['header_key']; // Template file header key

		$templates = $core_template->get_templates( $header_key );

		$group_field = $this->create_group_field( $template_select_field );
		$sub_fields = [];

		/**
		 *	TODO: Filter Doc
		 */
		foreach ( $templates as $template ) {

			$field_groups = acf_get_field_groups( [ 'template_file_settings' => $template['name'] ] );

			// condition for this template
			$add_condition = [
				'field'		=> $template_select_field['key'],
				'operator'	=> '==',
				'value'		=> $template['name']
			];

			foreach ( $field_groups as $group ) {

				$field_group_fields = acf_get_fields( $group['key'] );

				foreach ( $field_group_fields as $field_group_field ) {

					if ( ! isset( $sub_fields[ $field_group_field['key'] ] ) ) {
						// add condition to field
						$field_group_field['parent'] = $group_field['key'];
						$sub_fields[ $field_group_field['key'] ] = $field_group_field;
					}

					$sub_fields[ $field_group_field['key'] ]['__tmp_sort_key'] = $group['menu_order'];

					if ( ! $sub_fields[ $field_group_field['key'] ]['conditional_logic'] ) {
						$sub_fields[ $field_group_field['key'] ]['conditional_logic'] = [ [ $add_condition ] ];
					} else {
						//*
						$sub_fields[ $field_group_field['key'] ]['conditional_logic'][] = [ $add_condition ];
						/*/
						// modify existing conditions!
						foreach ( $sub_fields[ $field_group_field['key'] ]['conditional_logic'] as &$condition ) {
							$condition[] = $add_condition;
						}
						//*/
					}
				}
			}
		}
		// if ( false !== $template['settings'] ) {
		// 	add_filter(
		// 		sprintf( 'acf/format_value/name=%s', $group_field['name'] ),
		// 		function( $value, $post_id, $field ) use ($templates) {
		// 			return $template['settings'] + $value;
		// 		},
		// 		10, 3
		// 	);
		// }
		if ( count( $sub_fields ) ) {
			$sub_fields = array_values( $sub_fields );
			usort( $sub_fields, function($a,$b) {
				return $b['__tmp_sort_key'] === $a['__tmp_sort_key']
					? 0
					: ( $b['__tmp_sort_key'] > $a['__tmp_sort_key'] ? -1 : 1 );
			});
			array_map( function( &$el ) {
				unset( $el['__tmp_sort_key'] );
			}, $sub_fields );
			$group_field['sub_fields'] = $sub_fields;
			acf_add_local_field( $group_field, true );
		}

		return $group_field;
	}


	/**
	 *	@param array $parent_field ACF Field
	 */
	private function create_group_field( $parent_field ) {

		$slug = $parent_field['name'];
		$key = sprintf( 'field_%s_settings', $slug );

//

		return [
			'key'			=> $key,
			'label'			=> __( 'Template Settings', 'acf-wp-objects' ),
			'name'			=> sprintf( '%s_settings', $slug ),
			'type'			=> 'group',
			'prefix'		=> 'acf',
			'instructions'	=> '',
			'required'		=> 0,
			'parent'		=> $parent_field['parent'],
			'conditional_logic'	=> [],
			'wrapper'		=> [
				'width'			=> '',
				'class'			=> '',
				'id'			=> ''
			],
			'layout'		=> 'block',
			'sub_fields'	=> [],
			'value'			=> null,
		];

	}


	/**
	 *	Whether includer fields should resolve to fields
	 *
	 *	@param array $field_group
	 *	@return boolean
	 */
	private function should_resolve() {

		return ! ACF\ACF::instance()->is_fieldgroup_admin();

	}



	/**
	 *	@return array
	 */
	private function get_template_type_choices( ) {

		$template = Core\Template::instance();

		return array_map(
			function($e) {
				return $e['header_key'];
			},
			$template->get_template_types()
		);
	}

	/**
	 *	@return array
	 */
	private function get_template_choices( $field ) {

		$template_type = Core\Template::instance()->get_template_type( $field['template_type'] );

		$header_key = $template_type['header_key']; // Template file header key

		$template = Core\Template::instance();

		return array_map(
			function($e) {
				return $e['label'];
			},
			$template->get_templates( $header_key )

		);
	}


}