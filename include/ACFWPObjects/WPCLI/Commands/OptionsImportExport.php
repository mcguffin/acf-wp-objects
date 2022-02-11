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
use ACFWPObjects\Compat\ACF\Helper;

class OptionsImportExport extends \WP_CLI_Command {


	/**
	 * Export ACF Options Page to JSON
	 *
	 * ## OPTIONS
	 *
	 * <page_slug>
	 * : ACF options page slug
	 *
	 * [<file>]
	 * : File with Exported ACF options JSON
	 *
	 */
	public function reset( $args, $assoc_args ) {

		$helper = Helper\ImportExportOptionsPage::instance();

		$page = acf_get_options_page( $args[0] );
		if ( ! $page ) {
			\WP_CLI::error( 'Options page not found' );
			return;
		}
		if ( isset( $args[1] ) && file_exists( $args[1] ) ) {
			$page['reset'] = $args[1];
		} else {
			$page['reset'] = true;
		}

		$helper->reset( $page );

		\WP_CLI::success( sprintf( 'Options Page %s has been reset', $page['menu_slug'] ) );

	}

	/**
	 * Export ACF Options Page to JSON
	 *
	 * ## OPTIONS
	 *
	 * <page_slug>
	 * : ACF options page slug
	 *
	 * [--references]
	 * : Export referenced Objects like posts or files too
	 *
	 * [--pretty]
	 * : Pretty print JSON output
	 *
	 * --
	 * default: false
	 */
	public function export( $args, $assoc_args ) {

		$pretty = isset( $assoc_args['pretty'] ) && $assoc_args['pretty'];
		$references = isset( $assoc_args['references'] ) && $assoc_args['references'];

		$helper = Helper\ImportExportOptionsPage::instance();
		$page = acf_get_options_page( $args[0] );

		if ( ! $page ) {
			\WP_CLI::error( 'Options page not found' );
			return;
		}
		echo wp_json_encode(
			$helper->export( $page, $references ),
			$pretty
				? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
				: 0
		);
		\WP_CLI::line('');
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

		$helper = Helper\ImportExportOptionsPage::instance();

		$contents = file_get_contents( $args[0] );

		if ( $helper->import( $contents ) ) {
			\WP_CLI::success( sprintf( 'Options imported' ) );
		} else {
			\WP_CLI::error( sprintf( 'invalid data in file %s', $args[0] ) );
		}

	}


}
