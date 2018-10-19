<?php

namespace ACFWPObjects\Compat\ACF\Location;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class PostTypeProp extends \acf_location {

	/*
	 *  __construct
	 *
	 *  This function will setup the class functionality
	 *
	 *  @type	function
	 *  @date	5/03/2014
	 *  @since	5.0.0
	 *
	 *  @param	n/a
	 *  @return	n/a
	 */

	function initialize() {

		// vars
		$this->name = 'post_type_prop';
		$this->label = __("Post Type Property",'acf-wp-objects');
		$this->category = __('WordPress','acf-wp-objects');

	}


	/*
	 *  rule_match
	 *
	 *  This function is used to match this location $rule to the current $screen
	 *
	 *  @type	function
	 *  @date	3/01/13
	 *  @since	3.5.7
	 *
	 *  @param	$match (boolean)
	 *  @param	$rule (array)
	 *  @return	$options (array)
	 */

	function rule_match( $result, $rule, $screen ) {

		// vars
		$post_type = acf_maybe_get( $screen, 'post_type' );

		// bail early if not post
		if( ! $post_type ) return false;

		$prop = $rule['value'];

		$pto = get_post_type_object( $post_type );

        // return
        return $this->compare( $pto->$prop, $rule );

	}


	/*
	 *  rule_operators
	 *
	 *  This function returns the available values for this rule type
	 *
	 *  @type	function
	 *  @date	30/5/17
	 *  @since	5.6.0
	 *
	 *  @param	n/a
	 *  @return	(array)
	 */

	function rule_values( $choices, $rule ) {

		// global

		$choices = array(
			'_builtin'			=> __('Builtin','acf-wp-obects'),
			'public'			=> __('Public','acf-wp-obects'),
			'show_ui'			=> __('Show UI','acf-wp-obects'),
			'show_in_menu'		=> __('Show in Menus','acf-wp-obects'),
			'show_in_nav_menus'	=> __('Show in Nav Menus','acf-wp-obects'),
		);

		return $choices;

	}


}
