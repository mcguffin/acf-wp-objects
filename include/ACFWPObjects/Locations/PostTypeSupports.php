<?php

namespace ACFWPObjects\Locations;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class PostTypeSupports extends \acf_location {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'post_type_supports';
		$this->label = __("Post Type Supports",'acf-wp-objects');
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

		$supports = post_type_supports( $post_type, $rule['value'] );

		return $rule['operator'] === '=='
			? $supports
			: ! $supports;
	}


	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		$supports = [];

		$support_labels = [
			'title'           => __( 'Title', 'pp-listing' ),
			'author'          => __( 'Author', 'pp-listing' ),
			'thumbnail'       => __( 'Featured image', 'pp-listing' ),
			'excerpt'         => __( 'Excerpt', 'pp-listing' ),
			'custom-fields'   => __( 'Custom Fields', 'pp-listing' ),
			'revisions'       => __( 'Revisions', 'pp-listing' ),
			'page-attributes' => __( 'Page Attributes', 'pp-listing' ),
			'editor'          => __( 'Editor', 'pp-listing' ),
			'comments'        => __( 'Comments', 'pp-listing' ),
			'post-formats'    => __( 'Post formats', 'pp-listing' ),
			'trackbacks'      => __( 'Trackbacks', 'pp-listing' ),
		];

		// global
		foreach ( get_post_types( [ 'show_ui' => 1 ] ) as $post_type ) {
			$supports += array_filter( get_all_post_type_supports( $post_type ) );
		}

		$supports = array_combine(
			array_keys( $supports ),
			array_map(
				function( $support ) {
					return ucwords( str_replace( ['-','_'], ' ', $support ) );
				},
				array_keys( $supports )
			)
		);

		return array_intersect_key(
			$support_labels,
			$supports
		) + $supports;

	}


}
