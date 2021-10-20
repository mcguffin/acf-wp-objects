<?php
/**
 *	@package LocalFontLibrary\WPCLI
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\WPCLI;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

use ACFWPObjects\Core;

class WPCLI extends Core\Singleton {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		$cmd = new Commands\OptionsImportExport();

		\WP_CLI::add_command( 'acf-options-page export', [ $cmd, 'export' ], [
//			'before_invoke'	=> 'a_callable',
//			'after_invoke'	=> 'another_callable',
			'shortdesc'		=> __( 'Export ACF Options Page to JSON', 'acf-wp-objects' ),
//			'when'			=> 'before_wp_load',
			'is_deferred'	=> true,
		] );

		\WP_CLI::add_command( 'acf-options-page import', [ $cmd, 'import' ], [
//			'before_invoke'	=> 'a_callable',
//			'after_invoke'	=> 'another_callable',
			'shortdesc'		=> __( 'Import ACF Options Page from JSON file', 'acf-wp-objects' ),
//			'when'			=> 'before_wp_load',
			'is_deferred'	=> false,
		] );


		\WP_CLI::add_command( 'acf-options-page reset', [ $cmd, 'reset' ], [
//			'before_invoke'	=> 'a_callable',
//			'after_invoke'	=> 'another_callable',
			'shortdesc'		=> __( 'Reset ACF Options Page to defaults', 'acf-wp-objects' ),
//			'when'			=> 'before_wp_load',
			'is_deferred'	=> false,
		] );
	}
}
