<?php

namespace ACFWPObjects\Compat\ACF\Fields;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

use ACFWPObjects\Asset;
use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF\ACF as CompatACF;
use ACFWPObjects\Compat\ACF\Helper;

class Includer extends \acf_field {

	private $counter = 0;
	/*+
	 *  @inheritdoc
	 */
	public function initialize() {

		$this->name = 'includer-field';
		$this->label = __('Include Fields', 'acf-wp-objects');
		$this->category = 'relational';
		$this->defaults = array(
			'field_group_key' => '',
			'field_group_key_custom' => '',
			'prefix_label' => '',
			'suffix_label' => '',
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		);

		add_action( 'acf/field_group/admin_enqueue_scripts',	[ $this, 'field_group_admin_enqueue_scripts' ] );

		add_filter( 'acf/load_fields', [ $this, 'resolve_fields' ], 5, 2 );

	}


	/**
	 *  @inheritdoc
	 */
	public function render_field_settings( $field ) {

		$current_field_group_key = isset( $_REQUEST['post'] ) ? intval($_REQUEST['post']) : null;

		$local_enabled = acf_is_local_enabled();
		if ( ! $local_enabled ) {
			acf_enable_local();
		}

		foreach ( acf_get_field_groups() as $field_group ) {
			if ( $current_field_group_key === $field_group['ID'] ) {
				continue;
			}
			$field_group_choices[ $field_group['key'] ] = $field_group['title'];
		}

		if ( ! $local_enabled ) {
			acf_disable_local();
		}

		acf_render_field_setting( $field, array(
			'name'			=> 'field_group_key',
			'label'			=> __('Field Group','acf-wp-objects'),
			'instructions'	=> __('Include all fields from selected Field Group','acf-wp-objects'),
			'type'			=> 'select',
			'allow_null'	=> '1',
			'choices'		=> $field_group_choices,
		));

		acf_render_field_setting( $field, array(
			'name'			=> 'field_group_key_custom',
			'label'			=> __('Custom Field Group Key','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'text',
			'conditions'	=> [
				'field'		=> 'field_group_key',
				'operator'	=> '==',
				'value'		=> ''
			]
		));

		acf_render_field_setting( $field, array(
			'name'			=> 'prefix_label',
			'label'			=> __('Prepend to field labels','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'text',
			'allow_null'	=> '1',
		));
		acf_render_field_setting( $field, array(
			'name'			=> 'suffix_label',
			'label'			=> __('Append to field labels','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'text',
			'allow_null'	=> '1',
		));

	}

	/**
	 *	Enqueue field-specific style
	 *
	 *	@action acf/field_group/admin_enqueue_scripts
	 */
	public function field_group_admin_enqueue_scripts() {

		Asset\Asset::get( 'css/admin/acf-field-group.css' )
			->deps( 'acf-field-group' )
			->enqueue();

	}

	/**
	 *	Replace includer field with fields from field group
	 *
	 *	@filter acf/load_fields
	 */
	public function resolve_fields( $fields, $parent ) {

		if ( ! $this->should_resolve() ) {
			return $fields;
		}

		$return_fields = [];
		foreach ( $fields as $field ) {


			if ( $this->name === $field['type'] ) {

				$resolved_fields = $this->resolve_field( $field );


				if ( isset( $parent['type'] ) && 'repeater' === $parent['type'] && $parent['collapsed'] === $field['key'] ) {
					// add collapsed-target class
					$resolved_fields = array_map( function( $field ) {
						$field['wrapper']['class'] .= ' -collapsed-target';
						return $field;
					}, $resolved_fields );

				}

				$return_fields = array_merge( $return_fields, $resolved_fields );

			} else {
				$return_fields[] = $field;
			}
		}

		return $return_fields;
	}

	/**
	 *	Resolve Includer Field: turn field into many
	 *
	 *	@param array $field Includer Field to resolve
	 *	@return array Fields
	 */
	private function resolve_field( $field ) {

		$helper = Helper\Conditional::instance();
		$key_suffix = str_replace( 'field_', '', $field['key'] );
		$ret = [];


		$field_group_key = $field['field_group_key'];
		if ( empty( $field_group_key ) ) {
			$field_group_key = $field['field_group_key_custom'];
		}
		$parent = acf_get_field_group( $field_group_key );

		$replace_field_keys = [];

		if ( isset( $field['parent_layout'] ) ) {
			$parent['parent_layout'] = $field['parent_layout'];
		}
		/* @var  */
		$include_fields = acf_get_fields( $field_group_key );

		$conditional = $field['conditional_logic'];

		foreach ( $include_fields as $include_field ) {

			if ( $field['prefix_label'] ) {
				$include_field['label'] = $field['prefix_label'] . $include_field['label'];
			}

			if ( $field['suffix_label'] ) {
				$include_field['label'] .= $field['suffix_label'];
			}

			$include_field['wrapper'] = wp_parse_args( $include_field['wrapper'], [
				'width' => '',
				'class' => '',
				'id' => '',
			] );
			// inherit wrapper attributes
			if ( $field['wrapper']['width'] ) {
				$include_field['wrapper']['width'] = $field['wrapper']['width'];
			}
			if ( $field['wrapper']['id'] ) {
				$include_field['wrapper']['id'] = $field['wrapper']['id'];
			}
			if ( $field['wrapper']['class'] ) {
				$include_field['wrapper']['class'] .= ' ' . $field['wrapper']['class'];
				$include_field['wrapper']['class'] = trim( $include_field['wrapper']['class'] );
			}

			$include_field['parent'] = $field['parent'];

			// support flexible content field
			if ( isset( $field['parent_layout'] ) ) {

				$include_field['parent_layout'] = $field['parent_layout'];

			}

			// make sure fieldkey is unique
			$new_field_key = $include_field['key'] . '_' . $key_suffix;
			$replace_field_keys[ $include_field['key'] ] = $new_field_key;
			$include_field['key'] = $new_field_key;
			$include_field['conditional_logic'] = $helper->combine( $include_field['conditional_logic'], $field['conditional_logic'] );

			$ret[] = $include_field;
		}

		$acf = CompatACF::instance();

		foreach ( $replace_field_keys as $search => $replace ) {
			$ret = $acf->replace_field_key( $ret, $search, $replace );
		}

		// cache fields
		array_map( function($field) {
			acf_get_store( 'fields' )->set( $field['key'], $field )->alias( $field['key'], $field['name'] );
		}, $ret );

		return $ret;
	}

	/**
	 *	Whether includer fields should resolve to fields
	 *
	 *	@param array $field_group
	 *	@return boolean
	 */
	private function should_resolve() {

		if ( CompatACF::instance()->is_fieldgroup_admin() ) {
			return false;
		}

		// is trash action
		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], [ 'trash',  'acf/field_group/move_field' ] ) ) {
			return false;
		}

		return true;
	}




	/**
	 *  @inheritdoc
	 */
	function load_field( $field ) {

		// stolen from tab field

		// remove name to avoid caching issue
		$field['name'] = '';

		// remove instructions
		$field['instructions'] = '';

		// remove required to avoid JS issues
		$field['required'] = 0;

		// set value other than 'null' to avoid ACF loading / caching issue
		$field['value'] = false;

		// return
		return $field;

	}

}
