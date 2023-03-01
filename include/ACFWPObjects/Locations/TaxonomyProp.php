<?php

namespace ACFWPObjects\Locations;

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
			'_builtin'			=> __( 'Builtin', 'acf-wp-objects' ),
			'public'			=> __( 'Public', 'acf-wp-objects' ),
			'show_ui'			=> __( 'Show UI', 'acf-wp-objects' ),
			'show_in_menu'		=> __( 'Show in Menus', 'acf-wp-objects' ),
			'show_in_nav_menus'	=> __( 'Show in Nav Menus', 'acf-wp-objects' ),
			'hierarchical'		=> __( 'Hierarchical', 'acf-wp-objects' ),
		];

		return $choices;

	}


}
