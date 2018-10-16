<?php
/**
 *	@package AcfWpObjects\Compat
 *	@version 1.0.0
 *	2018-09-25
 */

namespace AcfWpObjects\Compat;

use AcfWpObjects\Core;

class RegenerateThumbnails extends Core\PluginComponent {

	private $stored_meta;

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
	}


	/**
	 *	@inheritdoc
	 */
	 public function activate(){

	 }

	 /**
	  *	@inheritdoc
	  */
	 public function deactivate(){

	 }

	 /**
	  *	@inheritdoc
	  */
	 public static function uninstall() {
		 // remove content and settings
	 }

	/**
 	 *	@inheritdoc
	 */
	public function upgrade( $new_version, $old_version ) {
	}


}
