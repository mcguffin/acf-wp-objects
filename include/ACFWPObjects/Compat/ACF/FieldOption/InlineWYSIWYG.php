<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;

class InlineWYSIWYG extends Core\Singleton {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'acf/field_group/render_field_settings_tab/presentation/type=wysiwyg', [ $this, 'field_settings_presentation' ] );
		add_filter( 'acf/prepare_field/type=wysiwyg', [ $this, 'prepare_field' ] );
		add_filter( 'acf/format_value/type=wysiwyg', [ $this, 'format_value_early' ], 0, 3 );
		add_filter( 'acf/format_value/type=wysiwyg', [ $this, 'format_value_late' ], 1000, 3 );

		add_action( 'acf/include_field_types', function() {
			acf_get_field_type( 'wysiwyg' )->defaults['is_inline'] = 0;
		} );

	}

	/**
	 *	@filter acf/prepare_field/type=flexible_content
	 */
	public function prepare_field( $field ) {
		return wp_parse_args( $field, [ 'is_inline' => 0 ] );
	}


	/**
	 *	@filter acf/format_value/type=wysiwyg
	 */
	function format_value_early( $value, $post_id, $field ) {
		// no paragraphs
		if ( $field['is_inline'] ) {
			remove_filter( 'acf_the_content', 'wpautop' );
			add_filter( 'acf_the_content', 'nl2br', 9 );
		}
		return $value;
	}

	/**
	 *	@filter acf/format_value/type=wysiwyg
	 */
	function format_value_late( $value, $post_id, $field ) {
		if ( $field['is_inline'] ) {
			add_filter( 'acf_the_content', 'wpautop' );
			remove_filter( 'acf_the_content', 'nl2br', 9 );
		}
		return $value;
	}

	/**
	 *	@action acf/field_group/render_field_settings_tab/presentation/type=wysiwyg
	 */
	public function field_settings_presentation( $field ) {

		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Accordion','acf-wp-objects'),
			'instructions'	=> __( 'Close other layouts when opening a Layout.', 'acf-wp-objects' ),
			'name'			=> 'is_inline',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
	}

}
