<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;

class Popup extends Core\Singleton {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'acf/render_field_settings/type=radio', [ $this, 'field_settings' ] );
		add_filter( 'acf/prepare_field/type=radio', [ $this, 'prepare_field' ] );

	}

	/**
	 *	@filter acf/prepare_field/type=radio_button
	 */
	public function prepare_field( $field ) {

		$field = wp_parse_args( $field, [ 'is_popup' => false ] );

		if ( $field['is_popup'] ) {
			// $field['pattern'] = '[0-9a-z_-]+';
			$field['wrapper'] = wp_parse_args( $field['wrapper'], [
				'class' => '',
			] );

			$field['wrapper']['class'] .= ' acf-popup';

		}
		return $field;
	}

	/**
	 *	@action acf/render_field_settings/type=flexible_content
	 */
	public function field_settings( $field ) {

		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Popup','acf-wp-objects'),
			'instructions'	=> __( 'Only show selected choice, open choices in Popup.', 'acf-wp-objects' ),
			'name'			=> 'is_popup',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));

	}

}
