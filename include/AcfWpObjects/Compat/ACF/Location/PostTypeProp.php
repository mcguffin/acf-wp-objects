<?php

namespace ACFWPObjects\Compat\ACF\Location;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class PostTypeProp extends \acf_location {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'post_type_prop';
		$this->label = __("Post Type Property",'acf-wp-objects');
		$this->category = __('WordPress','acf-wp-objects');

	}

	/**
	 *	@inheritdoc
	 */
	function rule_match( $result, $rule, $screen ) {

		// vars
		$post_id = acf_maybe_get( $screen, 'post_id' );

		// bail early if not post
		if( ! $post_id ) return false;

		$post_type = get_post_type( $post_id );

		if( ! $post_type ) return false;

		$prop = $rule['value'];

		$pto = get_post_type_object( $post_type );

        // return
        return $this->compare( $pto->$prop, $rule );

	}


	/**
	 *	@inheritdoc
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
