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

use ACFWPObjects\Core;

/*

[ COND1 ] x [ COND2 ] = [ [ COND1 ] ] x [ [ COND2 ] ] = [ [ COND1, COND2 ] ]
[ COND1, COND2 ] x [ COND3 ] = [ [ COND1, COND2 ] ] x [ [ COND3 ] ] = [ [ COND1, COND2, COND3 ] ]

[ [ COND1 ], [ COND2 ] ] x [ COND3 ] = [ [ COND1 ], [ COND2 ] ] x [ [ COND3 ] ] = [ [ COND1, COND3 ], [ COND2, COND3 ] ]
[ [ COND1 ], [ COND2 ] ] x [ [ COND3 ], [ COND4 ] ] = [ [ COND1, COND3 ], [ COND1, COND4 ], [ COND2, COND3 ], [ COND2, COND4 ] ]
[ [ COND1 ], [ COND2 ] ] x [ [ COND3, COND4 ] ] = [ [ COND1, COND3, COND4 ], [ COND1, COND3, COND4 ] ]



// [ $cond1 ] x [ $cond2 ] = [ [ $cond1, $cond2 ] ]
// [ $cond1, $cond2 ] x [ $cond3 ] = [ [ $cond1, $cond2 ] ] x [ [ $cond3 ] ] = [ [ $cond1, $cond2, $cond3 ] ]
// [ [ $cond1 ], [ $cond2 ] ] x [ [ $cond3, $cond4 ] ] = [ [ $cond1, $cond3, $cond4 ], [ $cond2, $cond3, $cond4 ] ]
//
// [ [ $cond1 ], [ $cond2 ] ] x [ $cond3 ] = [ [ $cond1 ], [ $cond2 ] ] x [ [ $cond3 ] ] = [ [ $cond1, $cond3 ], [ $cond2, $cond3 ] ]
// [ [ $cond1 ], [ $cond2 ] ] x [ [ $cond3 ], [ $cond4 ] ] = [ [ $cond1, $cond3 ], [ $cond1, $cond4 ], [ $cond2, $cond3 ], [ $cond2, $cond4 ] ]



*/

class Conditional extends Core\Singleton {

	/**
	 *	Combine conditional logic rules
	 *
	 *	@param array $conditions1 [ [ [ 'field' = 'field_1', 'operator' => 'op_1' ], ... ], .. ]
	 *	@param array $conditions2
	 *	@return array
	 */
	public function combine( $conditions1, $conditions2, $and = true ) {
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
				if ( true === $and ) {
					$new_rules[] = array_merge( $or1, $or2 );
				} else {
					$new_rules[] = $or1;
					$new_rules[] = $or2;
				}
			}
		}
		return $new_rules;

	}

	/**
	 *
	 */
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

		if ( empty( $conditions ) ) {
			$conditions = $this->normalize( $single );
		} else {
			foreach ( $conditions as $i => &$cond ) {
				$cond[] = $single;
			}
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
		$conditions = array_values( $conditions );
		if ( isset( $conditions[0]['field'] ) ) {
			return [ $conditions ];
		}
		return $conditions;
	}

}
