<?php

namespace ACFWPObjects\Compat\ACF\Location;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class TaxonomyProp extends \acf_location {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'taxonomy_prop';
		$this->label = __("Taxonomy Property",'acf-wp-objects');
		$this->category = __('WordPress','acf-wp-objects');

	}


	/**
	 *	@inheritdoc
	 */
	function rule_match( $result, $rule, $screen ) {

		// vars
		$taxonomy = acf_maybe_get( $screen, 'taxonomy' );

		// bail early if not taxononomy
		if( ! $taxonomy ) return false;

		$prop = $rule['value'];

		$txo = get_taxonomy( $taxonomy );

        // return
        return $this->compare( $txo->$prop, $rule );

	}


	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		// global

		$choices = [
			'_builtin'			=> __('Builtin','acf-wp-obects'),
			'public'			=> __('Public','acf-wp-obects'),
			'show_ui'			=> __('Show UI','acf-wp-obects'),
			'show_in_menu'		=> __('Show in Menus','acf-wp-obects'),
			'show_in_nav_menus'	=> __('Show in Nav Menus','acf-wp-obects'),
		];

		return $choices;

	}


}
