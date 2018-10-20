<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF\Hook;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;

class Preview {
	private $field;

	public function __construct( $field, $value ) {
		$this->field = $field;
		$this->value = $value;
	}
	public function get( $value ) {
		return $this->value;
	}
}
