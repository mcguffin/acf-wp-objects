<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;

class Popup extends AbstractFieldOption {

	protected $supported_fields = [
		'group',
		'radio',
		'wysiwyg',
	];

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		foreach ( $this->supported_fields as $field_type ) {
			add_filter( "acf/prepare_field/type={$field_type}", [ $this, 'prepare_field' ] );
			add_action( "acf/render_field/type={$field_type}", [$this, 'render_field'], 9 );
			add_action( "acf/render_field/type={$field_type}", [$this, 'render_field_late'], 11 );
			add_action( "acf_wpo/render_value/type={$field_type}", [ $this, "render_{$field_type}_value"] );
			add_action( "acf/render_field_presentation_settings/type={$field_type}", [$this, 'render_field_settings'] );
		}
		add_filter( 'acf_wpo_repeater_choices/type=radio', [ $this, 'repeater_choices' ], 10, 2 );
		add_action( "acf/render_field_presentation_settings/type=group", [$this, 'render_field_settings_group'] );
		add_filter( "acf/prepare_field/type=group", [ $this, 'prepare_field_group' ] );
	}

	/**
	 *	@filter acf/prepare_field/type={$field_type}
	 */
	public function prepare_field( $field ) {
		return wp_parse_args( $field, [ 'is_popup' => false ] );
	}

	/**
	 *	@filter acf/prepare_field/type=group
	 */
	public function prepare_field_group( $field ) {
		return wp_parse_args( $field, [ 'preview' => '' ] );
	}

	/**
	 *	@action acf/render_field/type={$type}:9
	 */
	public function render_field($field) {
		if ( ! $field['is_popup']) {
			return;
		}

		?>
		<button type="button" class="no-button" title="Edit" data-name="preview-edit">
			<?php do_action("acf_wpo/render_value/type={$field['type']}", $field ); ?>
		</button>

		<dialog class="acf-dialog">
			<div class="acf-dialog-head">
				<?php echo esc_html($field['label']); ?>
				<button type="button" class="acf-icon -cancel grey" data-name="preview-edit-reset" title="<?php esc_attr_e('Cancel','acf-wp-objects'); ?>"></button>
			</div>
			<div class="acf-dialog-body">
		<?php
	}

	/**
	 *	@action acf/render_field/type={$type}:11
	 */
	public function render_field_late($field) {
		if ( ! $field['is_popup']) {
			return;
		}
			?>
			</div>
			<?php if ( in_array($field['type'], ['group','wysiwyg' ] ) ) { ?>
				<div class="acf-dialog-foot">
					<button type="button" class="button-primary" data-name="preview-edit-finish"><?php esc_html_e('Okay','acf-wp-objects'); ?></button>
				</div>
			<?php } ?>
		</dialog>
		<?php
	}

	public function render_radio_value($field) {
		if ( isset( $field['choices'][ $field['value'] ] )) {
			printf(
				'<div class="acf-field-preview"><input type="radio" value="%s" class="acf-hidden" />%s</div>',
				$field['value'],
				$field['choices'][ $field['value'] ]
			);
		}

	}

	public function render_group_value($field) {
		$preview = $previewTemplate = $field['preview'];
		?>
		<span class="dashicons dashicons-edit"></span>
		<div class="acf-field-preview">
			<?php
			$has_values = false;
			foreach ( $field['sub_fields'] as $sub_field ) {
				$value = $field['value'][ $sub_field['key'] ]??'';
				$name  = $sub_field['name'];
				if ( is_scalar( $value ) ) {
					$preview = str_replace( "{{$name}}", $value, $preview );
				}
				$has_values |= !! $value;
			}
			if ( $has_values ) {
				echo $preview;
			}
			?>
		</div>
		<template>
			<?php echo $previewTemplate; ?>
		</template>
		<?php
	}

	public function render_wysiwyg_value($field) {
		printf('<span class="dashicons dashicons-edit"></span><div class="acf-field-preview">%s</div>', acf_format_value($field['value'], null, $field) );
	}

	/**
	 *	@filter acf_wpo_repeater_choices/type=radio
	 */
	public function repeater_choices( $choices, $field ) {
		if ( $field['is_popup'] && $field['allow_null'] && ! isset( $choices[''] ) ) {
			// add NULL choice
			$choices = [ '' =>
				[
					'label'  => __('– none –', 'acf-wp-objects' ),
					'visual' => '',
					'index'  => 0,
				]
			] + $choices;
		}
		return $choices;
	}

	/**
	 *	@action acf/render_field_presentation_settings/type=radio
	 */
	public function render_field_settings( $field ) {

		// allow_null
		acf_render_field_setting( $field, array(
			'label'        => __( 'Popup','acf-wp-objects'),
			'instructions' => __( 'Edit field value in Popup.', 'acf-wp-objects' ),
			'name'         => 'is_popup',
			'type'         => 'true_false',
			'ui'           => 1,
		));
	}


	/**
	 *	@action acf/render_field_presentation_settings/type=radio
	 */
	public function render_field_settings_group( $field ) {

		// allow_null
		acf_render_field_setting( $field, array(
			'label'        => __( 'Preview template','acf-wp-objects'),
			'instructions' => __( 'Use sub field names in curly braces as placeholders.', 'acf-wp-objects' ),
			'name'         => 'preview',
			'type'         => 'textarea',
		));

	}

}
