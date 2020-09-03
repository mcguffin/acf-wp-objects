<?php


/**
 *	@param String $path where to save ACF-JSON files
 *	@param callable $active_callback Whether the field group should be translated.
 *	@param String|Array $search_paths Where to look for field groups
 *	@return Boolean
 */
function acf_register_local_json( $path, $active_callback, $search_paths ) {
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
