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



class ACFRGBAColorPicker extends Core\Singleton implements Core\ComponentInterface {
	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_filter( 'acf_wp_objects_repeater_choices_allow_fields', array( $this, 'repeater_choice_fields' ));
	}
	public function repeater_choice_fields( $field_types ) {
		$field_types[ 'extended-color-picker' ] = 'color';
		return $field_types;
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
