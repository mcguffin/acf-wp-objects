<?php

namespace ACFWPObjects\Forms;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class WPOptions extends Core\Singleton {

	private $field_groups = [];
	private $optionset = null;
	private $did_form_data = false;
	private $acf_field_options = [];

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'admin_init', [ $this, 'admin_init' ] );

	}


	/**
	 *	@action admin_init
	 */
	public function admin_init() {

		global $pagenow;

		if ( $pagenow === 'options.php' && isset( $_POST['option_page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// when options are saved
			$this->optionset = sanitize_key( wp_unslash( $_POST['option_page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		} else {

			$this->optionset = preg_replace( '/options-(.*)\.php/', '$1', $pagenow );

		}

		$this->field_groups = acf_get_field_groups( [ 'wp_options' => sprintf( 'options-%s.php', $this->optionset ) ] );

		if ( ! count( $this->field_groups ) ) {
			return;
		}

		// add_filter( 'whitelist_options', [ $this, 'whitelist_options' ] );

		acf_enqueue_scripts();


		foreach ( $this->field_groups as $group ) {
			$section = 'acf-'.$group['key'];
			add_settings_section( $section, $group['title'], function() use ( $group ) {
				if ( ! empty( $group['description'] ) ) {
					printf(
						'<p class="description">%s</p>',
						esc_html( $group['description'] )
					);
				}
			}, $this->optionset );

			$fields = acf_get_fields( $group );

			foreach ( $fields as $field ) {
				$option_name = sprintf( '%s_%s', $this->optionset, $field['name'] );
				//add_option( $option_name, '', '', false );
				register_setting( $this->optionset, $option_name );
				$field['name'] = $option_name;

				add_settings_field(
					$option_name,
					$field['label'],
					[ $this, 'render_field' ],
					$this->optionset,
					$section,
					$field
				);
				$this->acf_field_options[] = $option_name;
			}
		}
	}

	/**
	 *	add_settings_field callback
	 */
	public function render_field( $field ) {

		if ( ! $this->did_form_data ) {
			acf_form_data([
				'screen'	=> 'options',
				'post_id'	=> $this->optionset,
			]);
			$this->did_form_data = true;
		}

		add_filter('acf/get_field_label', [ $this, 'remove_label' ] );
		$field['prefix'] = '';
		$field['key'] = '';
		$field['value'] = get_option( $field['name'] );

		acf_render_field_wrap( $field );

	}

	/**
	 *	@filter acf/get_field_label
	 */
	public function remove_label( $label ) {
		$label = '';
		remove_filter('acf/get_field_label', [ $this, 'remove_label' ] );
		return $label;
	}
}
