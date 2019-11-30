<?php

namespace ACFWPObjects\Compat\ACF\Location;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class ClassicEditor extends \acf_location {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'wp_editor';
		$this->label = __("Post Editor",'acf-wp-objects');
		$this->category = __('WordPress','acf-wp-objects');

	}

	/**
	 *	@inheritdoc
	 */
	function rule_match( $result, $rule, $screen ) {

		$post_id = acf_maybe_get( $screen, 'post_id' );

		if( ! $post_id ) return false;

		$post = get_post( $post_id );

		$is_block_editor = \Classic_Editor::choose_editor( use_block_editor_for_post( $post ), $post );

        // return
        return $this->compare( $is_block_editor ? 'block-editor' : 'classic-editor', $rule );

	}


	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		// global

		$choices = array(
			'block-editor'		=> __('Block Editor','acf-wp-obects'),
			'classic-editor'	=> __('Classic Editor','acf-wp-obects'),
		);

		return $choices;

	}


}
