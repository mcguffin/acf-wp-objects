<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;


class Polylang extends Core\Singleton {

	private $pll = null;

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		$this->pll = new ACF\Form\PolylangLanguage();

		// make ACF location rule match on Polylang Admin page
		add_filter( 'acf/location/match_rule/type=taxonomy', [ $this, 'location_rule_match' ], 10, 4 );

	}

	/**
	 *	@filter acf/location/match_rule/type=taxonomy
	 */
	public function location_rule_match( $result, $rule, $screen, $field_group ) {

		if ( $rule['value'] === 'language' ) {

			$result = $this->pll->validate_page();

			if ( $rule['operator'] !== '==' ) {
				$result = ! $result;
			}

		}

		return $result;
	}

}
