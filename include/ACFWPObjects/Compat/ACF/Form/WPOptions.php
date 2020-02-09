<?php

namespace ACFWPObjects\Compat\ACF\Form;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class WPOptions extends Core\Singleton {

	private $field_groups = [];
	private $optionset = null;
	private $did_form_data = false;

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

		$this->field_groups = acf_get_field_groups( [ 'wp_options' => $pagenow ] );

		if ( $pagenow === 'options.php' && isset( $_POST['option_page'] ) ) {
			$this->optionset = sanitize_key( wp_unslash( $_POST['option_page'] ) );
		} else {
			$this->optionset = preg_replace( '/options-(.*)\.php/', '$1', $pagenow );
		}

		if ( ! count( $this->field_groups ) ) {
			return;
		}

		$this->maybe_save();
		//
		// add_filter( 'whitelist_options', [ $this, 'whitelist_options' ] );

		acf_enqueue_scripts();


		foreach ( $this->field_groups as $group ) {
			$section = 'acf-'.$group['key'];
			add_settings_section( 'acf-'.$group['key'], $group['title'], function() use ($group){
				if ( ! empty( $group['description'] ) ) {
					printf(
						'<p class="description">%s</p>',
						esc_html( $group['description'] )
					);
				}
			}, $this->optionset );
			$fields = acf_get_fields( $group );


			foreach ( $fields as $field ) {

				add_settings_field(
					$field['key'],
					$field['label'],
					array( $this, 'render_field' ),
					$this->optionset,
					$section,
					$field
				);

			}

//			register_setting( $settings, '', null ); // sanitize!
		}
	}

	/**
	 *	Save options
	 */
	public function maybe_save() {

		if ( ! isset( $_POST['acf'] ) ) {
			return;
		}

		// save data
	    if ( acf_verify_nonce('options') && acf_validate_save_post( true ) ) {

	    	// save
			acf_save_post( $this->optionset );

			// redirect
			// wp_redirect( add_query_arg(array('message' => '1')) );
			// exit;

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

		$field['value'] = acf_get_value( $this->optionset, $field );

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
