<?php

namespace ACFWPObjects\Forms;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class PolylangLanguage extends \acf_form_taxonomy {


	/**
	 *	@inheritdoc
	 */
	function __construct() {

		add_action('admin_enqueue_scripts',	[ $this, 'admin_enqueue_scripts' ] );

	}


	/**
	 *	@inheritdoc
	 */
	function validate_page() {

		// global
		global $pagenow;

		// validate page
		if( $pagenow === 'admin.php' && isset($_GET['page']) && 'mlang' === wp_unslash( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			return true;

		}


		// return
		return false;
	}

	/**
	 *	@inheritdoc
	 */
	function admin_enqueue_scripts() {

		// validate page
		if( !$this->validate_page() ) {

			return;

		}

		// load acf scripts
		acf_enqueue_scripts();

		// actions
		add_action("pll_language_add_form_fields", 	  array($this,'add_pll_term' ), 10, 1);
		add_action("pll_language_edit_form_fields", array($this, 'edit_pll_term' ), 10, 2);

	}

	/**
	 *	@action pll_language_add_form_fields
	 */
	function add_pll_term() {

		parent::add_term('language');
	}

	/**
	 *	@action pll_language_edit_form_fields
	 */
	function edit_pll_term($term) {

		parent::edit_term($term,'language');
	}

}
