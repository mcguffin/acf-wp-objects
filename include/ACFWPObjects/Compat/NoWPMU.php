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


class NoWPMU extends Core\Singleton {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_filter('acf/validate_options_page', [ $this, 'validate_options_page' ] );
		add_filter('acf/get_options_pages', [ $this, 'get_options_pages' ] );
	}

	/**
	 *	@filter acf/validate_options_page
	 */
	public function validate_options_page( $page ) {
		return wp_parse_args( $page, [
			'network'	=> false,
		]);
	}

	/**
	 *	@filter acf/get_options_pages
	 */
	public function get_options_pages( $pages ) {
		return array_filter( $pages, [ $this, '_filter_network' ] );
	}

	/**
	 *	@filter acf/get_options_pages
	 */
	public function _filter_network( $page ) {
		return boolval($page['network']) === false;
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
