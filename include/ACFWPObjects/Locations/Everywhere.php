<?php

namespace ACFWPObjects\Locations;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class Everywhere extends \acf_location {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'other';
		$this->label = __( 'Other', 'acf-wp-objects' );
		$this->category = __( 'Generic', 'acf-wp-objects' );

	}

	/**
	 *	@inheritdoc
	 */
	function rule_match( $result, $rule, $screen ) {

		return $rule['operator'] === '==';

	}


	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		// global
		$choices = [
			'everywhere' => __( 'Everywhere', 'acf-wp-objects' ),
		];

		return $choices;

	}


}
