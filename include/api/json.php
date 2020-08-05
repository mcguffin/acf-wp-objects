<?php


/**
 *	@param string $path where to save ACF-JSON files
 *	@param callable $active_callback Whether the field group should be translated.
 *	@return boolean
 */
function acf_register_json_path( $path, $active_callback, $search_paths = [] ) {
	if ( ! is_dir( $path ) ) {
		return false;
	}
	if ( ! is_callable( $active_callback ) ) {
		return false;
	}
	if ( ! is_array( $search_paths ) ) {
		return false;
	}
	return ACFWPObjects\Compat\ACF\JSON::register_path( $path, $active_callback, $search_paths );
}

/**
 *	@param string $path where to save ACF-JSON files
 *	@param callable $active_callback Whether the field group should be translated.
 *	@return boolean
 */
function acf_deregister_json_path( $path ) {
	return ACFWPObjects\Compat\ACF\JSON::deregister_path( $path );
}
