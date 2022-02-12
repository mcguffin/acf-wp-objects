<?php


/**
 *	@param String|Array $page Options page slug or config
 *	@param Boolean $reference Whether reference values like post objects or images should be exported too. Implies format_values
 *	@return Array
 */
function acf_export_options_page( $page, $references = false ) {

	return ACFWPObjects\Compat\ACF\Helper\ImportExportOptionsPage::instance()->export( $page, $references );

}

/**
 *	@param String|Array $data
 *	@return Boolean Success
 */
function acf_import_options_page( $data ) {

	return ACFWPObjects\Compat\ACF\Helper\ImportExportOptionsPage::instance()->import( $data );

}

/**
 *	@param String|Array $data
 *	@return Boolean Success
 */
function acf_reset_options_page( $page ) {

	return ACFWPObjects\Compat\ACF\Helper\ImportExportOptionsPage::instance()->reset( $page );

}
