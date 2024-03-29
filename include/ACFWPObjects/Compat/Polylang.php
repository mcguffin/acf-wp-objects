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
use ACFWPObjects\Forms;


class Polylang extends Core\Singleton {

	private $pll = null;

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		$this->pll = new Forms\PolylangLanguage();

		// make ACF location rule match on Polylang Admin page
		add_filter( 'acf/location/match_rule/type=taxonomy', [ $this, 'location_rule_match' ], 10, 3 );

	}

	/**
	 *	@filter acf/location/match_rule/type=taxonomy
	 */
	public function location_rule_match( $result, $rule, $screen ) {

		if ( $rule['value'] === 'language' ) {

			$result = $this->pll->validate_page();

			if ( $rule['operator'] !== '==' ) {
				$result = ! $result;
			}

		}

		return $result;
	}

}
