<?php


if ( ! function_exists('acf_recreate_field_keys') ) {
	/**
	 *	Generate deterministic Field Keys and set them everywhere.
	 *
	 *	@param Array $fields ACF Fields including sub_fields
	 *	@return Array
	 */
	function acf_recreate_field_keys( $fields = [] ) {
		return ACFWPObjects\Compat\ACF\Helper\FieldKey::instance()->recreate_field_keys( $fields );
	}
}

if ( ! function_exists('acf_is_fieldgroup_admin') ) {
	/**
	 *	@return Boolean
	 */
	function acf_is_fieldgroup_admin() {
		return ACFWPObjects\Compat\ACF\ACF::instance()->is_fieldgroup_admin();
	}
}
