<?php


/**
 *	@param string $path where to save ACF-JSON files
 *	@param callable $active_callback Whether the field group should be translated.
 *	@return boolean
 */
function acf_register_local_json( $path, $active_callback, $search_paths = [] ) {
//var_dump(is_callable( $active_callback ),is_array( $search_paths ));
	if ( ! is_callable( $active_callback ) ) {
		return false;
	}
	if ( ! is_array( $search_paths ) ) {
		return false;
	}
	return ACFWPObjects\Compat\ACF\LocalJSON::register_path( $path, $active_callback, $search_paths );
}

/**
 *	@param string $path where to save ACF-JSON files
 *	@param callable $active_callback Whether the field group should be translated.
 *	@return boolean
 */
function acf_deregister_local_json( $path ) {
	return ACFWPObjects\Compat\ACF\LocalJSON::deregister_path( $path );
}
