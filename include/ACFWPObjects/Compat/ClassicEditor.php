<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;


class ClassicEditor extends Core\Singleton implements Core\ComponentInterface {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_action( 'acf/include_location_rules', [ $this, 'register_location_rules' ] );
	}

	/**
	 *	@action acf/include_location_rules
	 */
	public function register_location_rules() {

		acf_register_location_rule( 'ACFWPObjects\Locations\ClassicEditor' );

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
