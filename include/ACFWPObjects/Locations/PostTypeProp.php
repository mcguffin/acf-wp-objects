<?php

namespace ACFWPObjects\Locations;

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
		if ( ! $post_type = acf_maybe_get( $screen, 'post_type' ) ) {

			$post_id = acf_maybe_get( $screen, 'post_id' );

			if( ! $post_id ) return false;

			$post_type = get_post_type( $post_id );

		}

		if( ! $post_type ) {
			return false;
		}

		if ( $pto = get_post_type_object( $post_type ) ) {

			$prop = $rule['value'];

			return $this->compare( $pto->$prop, $rule );

		}
		return false;
	}


	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		// global

		$choices = [
			'_builtin'			=> __( 'Builtin', 'acf-wp-objects' ),
			'public'			=> __( 'Public', 'acf-wp-objects' ),
			'show_ui'			=> __( 'Show UI', 'acf-wp-objects' ),
			'show_in_menu'		=> __( 'Show in Menus', 'acf-wp-objects' ),
			'show_in_nav_menus'	=> __( 'Show in Nav Menus', 'acf-wp-objects' ),
			'hierarchical'		=> __( 'Hierarchical', 'acf-wp-objects' ),
		];

		return $choices;

	}


}
