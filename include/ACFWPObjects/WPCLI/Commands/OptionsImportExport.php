<?php
/**
 *	@package LocalFontLibrary\WPCLI
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\WPCLI\Commands;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF;

class OptionsImportExport extends \WP_CLI_Command {


	/**
	 * Export ACF Options Page to JSON
	 *
	 * ## OPTIONS
	 *
	 * <page_slug>
	 * : ACF options page slug
	 */
	public function reset( $args, $assoc_args ) {

		$options = ACF\OptionsPage::instance();
		$page = acf_get_options_page( $args[0] );
		if ( ! $page ) {
			\WP_CLI::error( 'Options page not found' );
			return;
		}
		$options->reset_page( $page );
		\WP_CLI::success( sprintf( 'Options Page %s has been reset', $page['menu_slug'] ) );

	}

	/**
	 * Export ACF Options Page to JSON
	 *
	 * ## OPTIONS
	 *
	 * <page_slug>
	 * : ACF options page slug
	 */
	public function export( $args, $assoc_args ) {

		$options = ACF\OptionsPage::instance();
		$page = acf_get_options_page( $args[0] );
		if ( ! $page ) {
			\WP_CLI::error( 'Options page not found' );
			return;
		}
		echo json_encode( $options->get_export_data( $page ) );

	}

	/**
	 * Import ACF Options Page from JSON file
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : File with Exported ACF options JSON
	 */
	public function import( $args, $assoc_args ) {
		if ( ! file_exists( $args[0] ) ) {
			\WP_CLI::error( sprintf( 'no such file %s', $args[0] ) );
			return;
		}
		$contents = file_get_contents( realpath( getcwd() . DIRECTORY_SEPARATOR . $args[0] ) );

		if ( empty( $contents ) ) {
			\WP_CLI::error( sprintf( 'file %s is empty', $args[0] ) );
			return;
		}
		$data = json_decode( $contents, true );
		if ( is_null( $data ) ) {
			\WP_CLI::error( sprintf( 'Could not parse file %s', $args[0] ) );
			return;
		}
		if ( ! isset( $data['values'] ) || ! isset( $data['page'] ) || ! isset( $data['page']['post_id'] ) ) {
			\WP_CLI::error( sprintf( 'invalid data in file %s', $args[0] ) );
			return;
		}

		acf_update_values( $data['values'], $data['page']['post_id'] );
		\WP_CLI::success( sprintf( 'Options Page %s imported', $data['page']['menu_slug'] ) );
	}


}
