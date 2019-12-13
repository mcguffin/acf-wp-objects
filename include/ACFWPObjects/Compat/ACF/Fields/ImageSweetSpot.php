<?php
/**
 *	@package ACFWPObjects\Compat\Fields
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF\Fields;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Asset;
use ACFWPObjects\Core;

class ImageSweetSpot extends \acf_field {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'image_sweet_spot';
		$this->label = __("Sweet Spot",'acf');
		$this->category = __('WordPress', 'acf-wp-objects' );
		$this->defaults = array(
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'default_value'	=> ['x'=>50, 'y' => 50 ],
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'return_format'	=> 'object',
		);

	}


	/**
	 *	@inheritdoc
	 */
	function load_field( $field ) {

		return $field;

	}


	/*
	 *  render_field()
	 *
	 *  Create the HTML interface for your field
	 *
	 *  @param	$field (array) the $field being rendered
	 *
	 *  @type	action
	 *  @since	3.6
	 *  @date	23/01/13
	 *
	 *  @param	$field (array) the $field being edited
	 *  @return	n/a
	 */
	function render_field( $field ) {

		$core = Core\Core::instance();
		?>
		<div class="acf-input-wrap">
			<?php

			// the map
			acf_render_field( array(
				'prepend'	=> __( 'X', 'acf-wp-objects' ),
				'type'		=> 'range',
				'name'		=> $field['name'].'[x]',
				'value'		=> $field['value']['x'],
				'min'		=> 0,
				'max'		=> 100,
				'step'		=> 1,
				'class'		=> '-sweet-spot-x',
				'append'	=> '%',
			) );

			// the map
			acf_render_field( array(
				'prepend'	=> __( 'Y', 'acf-wp-objects' ),
				'type'		=> 'range',
				'name'		=> $field['name'].'[y]',
				'value'		=> $field['value']['y'],
				'min'		=> 0,
				'max'		=> 100,
				'step'		=> 1,
				'class'		=> '-sweet-spot-y',
				'append'	=> '%',
			) );
			?>
		</div>
		<?php
	}

	/*
	 *  input_admin_enqueue_scripts()
	 *
	 *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	 *  Use this action to add CSS + JavaScript to assist your render_field() action.
	 *
	 *  @type	action (admin_enqueue_scripts)
	 *  @since	3.6
	 *  @date	23/01/13
	 *
	 *  @param	n/a
	 *  @return	n/a
	 */
	function input_admin_enqueue_scripts() {

		wp_enqueue_media();

		// Asset\Asset::get('js/admin/sweet-spot.js')
		// 	->deps('acf-input')
		// 	->enqueue();

		Asset\Asset::get('css/admin/sweet-spot.css')
			->deps('acf-input')
			->enqueue();
	}

	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/
	function field_group_admin_enqueue_scripts() {

	//	wp_enqueue_media();

	}


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	function load_value( $value, $post_id, $field ) {

		// prepare data for display

		return $value;
	}


}
