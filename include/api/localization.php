<?php


/**
 *	@param string $textdomain to take strings from
 *	@param callable $active_callback Whether the field group should be translated.
 *	@return boolean
 */
function acf_localize_field_groups( $textdomain, $active_callback, $context = false ) {
	if ( ! is_string( $textdomain ) ) {
		return false;
	}
	if ( ! is_callable( $active_callback ) ) {
		return false;
	}
	return ACFWPObjects\Compat\ACF\I18N::register_localization( $textdomain, $active_callback, $context );
}


/**
 *	@param string $textdomain to take strings from
 *	@param callable $active_callback Whether the field group should be translated.
 *	@return boolean
 */
function acf_unlocalize_field_groups( $textdomain ) {
	if ( ! is_string( $textdomain ) ) {
		return false;
	}
	return ACFWPObjects\Compat\ACF\I18N::deregister_localization( $textdomain );
}
