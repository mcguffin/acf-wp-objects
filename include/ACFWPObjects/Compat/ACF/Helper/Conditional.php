<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF\Helper;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Asset;
use ACFWPObjects\Core;


class Conditional extends Core\Singleton {

	/**
	 *	Combine conditional logic rules
	 *
	 *	@param array $conditions1 [ [ [ 'field' = 'field_1', 'operator' => 'op_1' ], ... ], .. ]
	 *	@param array $conditions2
	 *	@return array
	 */
	public function combine( $conditions1, $conditions2 ) {
		$c1 = $this->normalize( $conditions1 );
		$c2 = $this->normalize( $conditions2 );

		// deal with empty rulesets
		if ( ! count( $c1 ) ) {
			return $c2;
		}
		if ( ! count( $c2 ) ) {
			return $c1;
		}

		$new_rules = [];

		foreach ( $c1 as $or1 ) {
			foreach ( $c2 as $or2 ) {
				$new_rules[] = array_merge( $or1, $or2 );
			}
		}
		return $new_rules;

		if ( $this->is_single_condition( $c1 ) ) {
			// is single condition
			return $this->and( $c2, $c1[0][0] );
		} else if ( $this->is_single_condition( $c2 ) ) {
			return $this->and( $c1, $c2[0][0] );
		}

	}

	public function is_single_condition( $conditions ) {
		$conditions = $this->normalize( $conditions );
		return count( $conditions ) === 1 && count( $conditions[0] ) === 1;
	}

	/**
	 *
	 */
	public function or( $conditions, $single ) {
		$conditions = $this->normalize( $conditions );
		$conditions[] = [ $single ];
		return $conditions;
	}

	/**
	 *
	 */
	public function and( $conditions, $single ) {
		$conditions = $this->normalize( $conditions );
		foreach ( $conditions as $i => &$cond ) {
			$cond[] = $single;
		}
		return $conditions;
	}

	/**
	 *	@param array $conditions
	 *	@return [
	 *		[ AND, AND ], [ AND, AND ],
 	 *	]
	 */
	public function normalize( $conditions ) {
		if ( ! $conditions ) {
			$conditions = [];
		}
		// is inner
		if ( isset( $conditions['field'] ) ) {
			return [ [ $conditions ] ];
		}
		return $conditions;
	}

}
