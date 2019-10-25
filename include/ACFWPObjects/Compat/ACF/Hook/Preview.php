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

	/**
	 *	@param array $field ACF Field
	 *	@param string $value
	 */
	public function __construct( $field, $value ) {
		$this->field = $field;
		$this->value = $value;
	}

	/**
	 *	@filter the_title
	 *	@filter the_content
	 */
	public function get( $value ) {
		return $this->value;
	}
}
