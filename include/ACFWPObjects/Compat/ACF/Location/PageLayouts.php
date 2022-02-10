<?php

namespace ACFWPObjects\Compat\ACF\Location;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


class PageLayouts extends \acf_location {


	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'page_layouts';
		$this->label = __( 'Page Layout', 'acf-wp-objects' );
		$this->category = __( 'Generic', 'acf-wp-objects' );

	}

	/**
	 *	@inheritdoc
	 */
	function rule_match( $result, $rule, $screen ) {

		if ( ! isset( $screen[ $this->name ] ) ) {
			return false;
		}

		// return
		return $this->compare( $screen[ $this->name ], $rule );

	}


	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		// global
		$choices = apply_filters( 'acf_page_layout_locations', [ ] );

		return $choices;

	}

}
