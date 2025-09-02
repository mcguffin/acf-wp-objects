<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;

class PrimaryTerm extends Core\Singleton {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_filter( 'acf/load_field/type=taxonomy', [ $this, 'load_field' ] );
		add_action( 'acf/field_group/render_field_settings_tab/general/type=taxonomy', [ $this, 'field_settings_general' ] );
		add_filter( 'acf/fields/taxonomy/wp_list_categories', [ $this, 'wp_list_categories_args' ], 10, 2 );
		add_filter( 'acf/update_value/type=taxonomy', [ $this, 'update_value' ], 10, 3 );
		add_filter( 'acf/load_value/type=taxonomy', [ $this, 'load_value' ], 20, 3 );

	}

	/**
	 *	@filter acf/prepare_field/type=taxonomy
	 */
	public function load_field( $field ) {

		return wp_parse_args( $field, [
			'primary_term'        => 0,
			'primary_term_prefix' => 'primary',
		] );
	}

	/**
	 *	@filter acf/field_group/render_field_settings_tab/general/type=taxonomy
	 */
	public function field_settings_general( $field ) {
		acf_render_field_setting( $field, array(
			'label'        => __( 'Select Primary Term','acf-wp-objects'),
			// 'instructions' => __( '....', 'acf-wp-objects' ),
			'name'         => 'primary_term',
			'type'         => 'true_false',
			'ui'           => 1,
			'conditions'   => [
				[
					'field'    => 'save_terms',
					'operator' => '==',
					'value'    => 1,
				],
				[
					'field'    => 'load_terms',
					'operator' => '==',
					'value'    => 1,
				],
				[
					'field'    => 'field_type',
					'operator' => '==',
					'value'    => 'checkbox',
				],
			],
		));

		// TODO: post meta prefix.
		acf_render_field_setting( $field, array(
			'label'        => __( 'Post-Meta-Prefix','acf-wp-objects'),
			// 'instructions' => __( '....', 'acf-wp-objects' ),
			'name'         => 'primary_term_prefix',
			'type'         => 'text',
			'ui'           => 1,
			'default'      => 'primary',
			'conditions'   => [
				[
					'field'    => 'primary_term',
					'operator' => '==',
					'value'    => 1,
				],
				// [
				// 	'field'    => 'load_terms',
				// 	'operator' => '==',
				// 	'value'    => 1,
				// ],
				// [
				// 	'field'    => 'field_type',
				// 	'operator' => '==',
				// 	'value'    => 'checkbox',
				// ],
			],
		));

	}

	/**
	 *	@filter acf/fields/taxonomy/wp_list_categories
	 */
	public function wp_list_categories_args( $args, $field ) {
		if ( ! array_diff_assoc( [
			'field_type' => 'checkbox',
			'save_terms' => 1,
			'load_terms' => 1,
			'primary_term' => 1,
		], $field ) ) {
			$args['walker'] = new class($field) extends \ACF_Taxonomy_Field_Walker {
				public $primaryTerm;

				public function end_el( &$output, $term, $depth = 0, $args = array() ) {
					$key     =  $this->primaryTerm->get_meta_key($this->field);
					$output .= sprintf(
						'<label class="primary-term acf-js-tooltip" title="%s"><input type="radio" name="%s" value="%d" %s /></label>',
						sprintf(
							esc_attr__('Primary %s','acf-wp-objects'),
							get_taxonomy_labels(get_taxonomy($this->field['taxonomy']))->singular_name
						),
						str_replace( '[]', "[{$key}]", $this->field['name'] ),
						$term->term_id,
						checked( $term->term_id, $this->field['value'][$key]??0, false )
					);
					parent::end_el( $output, $term, $depth, $args );
				}

			};
			$args['walker']->primaryTerm = $this;
		}
		return $args;
	}

	/**
	 *	@filter acf/load_value/type=taxonomy
	 */
	public function load_value( $value, $post_id, $field ) {
		if ( is_numeric( $post_id ) && is_array($value) && ! array_diff_assoc( [
			'field_type' => 'checkbox',
			'save_terms' => 1,
			'load_terms' => 1,
			'primary_term' => 1,
		], $field ) ) {
			$key     =  $this->get_meta_key($field);
			$term_id = (int) get_post_meta($post_id, $key, true );
			if ( in_array( $term_id, $value ) ) {
				$value[$key] = $term_id;
			}
		}

		return $value;
	}

	/**
	 *	@filter acf/update_value/type=taxonomy
	 */
	public function update_value( $value, $post_id, $field ) {
		if ( is_numeric( $post_id ) && is_array($value) && ! array_diff_assoc( [
			'field_type' => 'checkbox',
			'save_terms' => 1,
			'load_terms' => 1,
			'primary_term' => 1,
		], $field ) ) {
			$key =  $this->get_meta_key($field);
			if ( isset( $value[$key] ) ) {
				update_post_meta($post_id, $key, $value[$key] );
			}
		}

		return $value;
	}

	public function get_meta_key( $field ) {
		return "{$field['primary_term_prefix']}_{$field['taxonomy']}";
	}

}
