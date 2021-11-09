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
		add_filter( 'acf/prepare_field/type=radio', [ $this, 'prepare_field_late' ], 100 );
		add_filter( 'acf_wpo_repeater_choices/type=radio', [ $this, 'repeater_choices' ], 10, 2 );
	}

	/**
	 *	@filter acf_wpo_repeater_choices/type=radio
	 */
	public function repeater_choices( $choices, $field ) {
		if ( $field['is_popup'] && $field['allow_null'] && ! isset( $choices[''] ) ) {
			// add NULL choice
			$choices = [ '' =>
				[
					'label' => __('– none –', 'acf-wp-objects' ),
					'visual' => '',
					'index' => 0,
				]
			] + $choices;
		}
		return $choices;
	}

	/**
	 *	@filter acf/prepare_field/type=radio
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
	 *	@filter acf/prepare_field/type=radio
	 */
	public function prepare_field_late( $field ) {
		if ( /*$field['repeater_choices'] &&*/ $field['is_popup'] && $field['allow_null'] ) {
			$field['allow_null'] = 0;
			if ( ! isset( $field['choices'][''] ) ) {
				$field['choices'] = [
					'' =>  __('– none –', 'acf-wp-objects' )
				] + $field['choices'];
			}
		}
		return $field;
	}

	/**
	 *	@action acf/render_field_settings/type=radio
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
