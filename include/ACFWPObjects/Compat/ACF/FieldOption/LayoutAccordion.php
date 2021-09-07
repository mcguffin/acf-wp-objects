<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;

class LayoutAccordion extends Core\Singleton {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'acf/render_field_settings/type=flexible_content', [ $this, 'field_settings' ] );
		add_filter( 'acf/prepare_field/type=flexible_content', [ $this, 'prepare_field' ] );

	}

	/**
	 *	@filter acf/prepare_field/type=flexible_content
	 */
	public function prepare_field( $field ) {

		$field = wp_parse_args( $field, [ 'is_accordion' => false ] );

		if ( $field['is_accordion'] ) {
			// $field['pattern'] = '[0-9a-z_-]+';
			$field['wrapper'] = wp_parse_args( $field['wrapper'], [
				'class' => '',
			] );

			$field['wrapper']['class'] .= ' acf-accordion-layout';

		}
		return $field;
	}

	/**
	 *	@action acf/render_field_settings/type=flexible_content
	 */
	public function field_settings( $field ) {

		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Accordion','acf-wp-objects'),
			'instructions'	=> __( 'Close other layouts when opening a Layout.', 'acf-wp-objects' ),
			'name'			=> 'is_accordion',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));

	}

}
