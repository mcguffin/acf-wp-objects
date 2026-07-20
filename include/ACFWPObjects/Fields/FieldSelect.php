<?php

namespace ACFWPObjects\Fields;

class FieldSelect extends \acf_field_select {

	public function ajax_query() {
		$nonce = acf_request_arg( 'nonce', '' );
		$key   = acf_request_arg( 'field_key', '' );

		$is_field_key = acf_is_field_key( $key );

		// Back-compat for field settings.
		if ( ! $is_field_key ) {
			if ( ! acf_current_user_can_admin() ) {
				die();
			}

			$nonce = '';
			$key   = '';
		}

		if ( ! acf_verify_ajax( $nonce, $key, $is_field_key, $this->name ) ) {
			die();
		}

		acf_send_ajax_results( $this->get_ajax_query( $_POST ) );
	}

}
