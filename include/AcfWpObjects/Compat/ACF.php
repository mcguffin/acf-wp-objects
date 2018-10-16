<?php
/**
 *	@package AcfWpObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace AcfWpObjects\Compat;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use AcfWpObjects\Core;


class ACF extends Core\PluginComponent {

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
