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
use ACFWPObjects\Forms;


class WPMU extends Core\Singleton implements Core\ComponentInterface {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		// filter options pages
		add_filter('acf/validate_options_page', [ $this, 'validate_options_page' ] );


		add_filter('acf/get_options_pages', [ $this, 'get_options_pages' ] );

		// save values
		add_filter('acf/pre_update_metadata', [ $this, 'pre_update_metadata' ], 10, 5 );

		// retrieve values
		// 1st approach: Results in network taking precedence over blog options!
		add_filter('acf/pre_load_metadata', [ $this, 'pre_load_metadata' ], 10, 4 );

		if ( is_network_admin() ) {
			new ACF\NetworkAdminOptionsPage();
			Forms\WPOptions::instance();
		}

	}

	/**
	 *	@filter acf/pre_load_metadata
	 */
	public function pre_load_metadata( $null, $post_id, $name, $hidden ) {

		// Something else (possibly ACF_Local_Meta) hooked into acf/pre_load_metadata before.
		if ( ! is_null( $null ) ) {
			return $null;
		}

		// Decode $post_id for $type and $id.
		extract( acf_decode_post_id( $post_id ) );

		// Hidden meta uses an underscore prefix.
		$prefix = $hidden ? '_' : '';

		// Bail early if no $id (possible during new acf_form).
		if( !$id ) {
			return $null;
		}

		// Update option.
		if( $type === 'option' ) {

			// Let blog options override network options:
			if ( ! is_network_admin() && ( $opt = get_option( "{$prefix}{$id}_{$name}", null ) ) ) {
				return $opt;
			}

			return get_site_option( "{$prefix}{$id}_{$name}", null );
		}

		return $null;
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

		if ( doing_filter('admin_menu') || doing_filter( 'network_admin_menu' ) || ! is_main_site() ) {
			$pages = array_filter( $pages, [ $this, '_filter_network' ] );
		}

		return $pages;
	}

	/**
	 *	@filter acf/get_options_pages
	 */
	public function _filter_network( $page ) {
		return $page['network'] === is_network_admin();
	}

	/**
	 *	@filter acf/pre_update_metadata
	 */
	public function pre_update_metadata( $null, $post_id, $name, $value, $hidden ) {

		if ( ! is_network_admin() ) {
			return $null;
		}
		// update values throuh update_site_option()


		// Decode $post_id for $type and $id.
		extract( acf_decode_post_id( $post_id ) );

		// Hidden meta uses an underscore prefix.
		$prefix = $hidden ? '_' : '';

		// Bail early if no $id (possible during new acf_form).
		if( ! $id ) {
			return $null;
		}

		// Update option.
		if( $type === 'option' ) {

			// Unslash value to match update_metadata() functionality.
			$value = wp_unslash( $value );
			return update_site_option( "{$prefix}{$id}_{$name}", $value );

		}
		return $null;
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
