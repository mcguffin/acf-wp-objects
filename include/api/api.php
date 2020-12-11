<?php


if ( ! function_exists('acf_recreate_field_keys') ) {
	function acf_recreate_field_keys( $fields = [] ) {
		return ACFWPObjects\Compat\ACF\ACF::instance()->recreate_field_keys( $fields );
	}
}
