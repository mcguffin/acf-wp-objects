<?php
/**
 *	@package ACFWPObjects
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}
global $_awpo_loadedclasses, $_awpo_memused;
$_awpo_loadedclasses = [];

function __autoload( $class ) {
global $_awpo_loadedclasses, $_awpo_memused;
	if ( false === ( $pos = strpos( $class, '\\' ) ) ) {
		return;
	}
$mem = memory_get_usage();
	$ds = DIRECTORY_SEPARATOR;
	$top = substr( $class, 0, $pos );

	if ( false === is_dir( __DIR__ .$ds . $top ) ) {
		// not our plugin.
		return;
	}

	$file = __DIR__ . $ds . str_replace( '\\', $ds, $class ) . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
$_awpo_loadedclasses[$class] = memory_get_usage() - $mem;
$_awpo_memused += memory_get_usage() - $mem;
	} else {
		throw new \Exception( sprintf( 'Class `%s` could not be loaded. File `%s` not found.', $class, $file ) );
	}
}
add_action('shutdown',function(){
	global $_awpo_loadedclasses,$_awpo_memused;
	var_dump($_awpo_memused);
	var_dump($_awpo_loadedclasses);
});

spl_autoload_register( 'ACFWPObjects\__autoload' );
