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


use ACFWPObjects\Core;

class SelectImageSize extends \acf_field_select {

	private $all_sizes = array();

	function initialize() {

		// vars
		$this->name = 'image_size_select';
		$this->label = __("Select Image Size",'acf-wp-objects');
		$this->category = __('WordPress', 'acf-wp-objects' );
		$this->defaults = array(
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'return_format'	=> 'array',

			'image_sizes'	=> array(),
			'pick'			=> 0,
			'named'			=> '',
			'cropped'		=> '',
			'builtin'		=> '',
		);
	}


	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function render_field( $field ) {

		$args_keys = array(
			'builtin',
			'named',
			'cropped',
		);

		if ( $field['pick'] ) {
			if ( empty( $field['image_sizes'] ) ) {
				$choices = $this->get_image_sizes( array(), 'name' );
			} else {
				$choices = $this->get_image_sizes( array( 'names' => $field['image_sizes'] ), 'name' );
			}
		} else {

			$args = array();

			foreach ( $args_keys as $key ) {

				if ( $field[$key] !== '' ) {
					$args[ $key ] = boolval( intval( $field[$key] ) );
				}
			}

			$choices = $this->get_image_sizes( $args, 'name' );
		}

		$field['choices'] = $choices;

		parent::render_field( $field );

	}



	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/

	function render_field_settings( $field ) {



		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));


		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));


		// ui
		acf_render_field_setting( $field, array(
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'name'			=> 'ui',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));




		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf-wp-objects'),
			'instructions'	=> __('Specify the returned value on front end','acf-wp-objects'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'array'			=> __("Size Array",'acf-wp-objects'),
				'slug'			=> __("Slug",'acf-wp-objects'),
			),
		));




		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Pick from List','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'pick',
			'ui'			=> 1,
		));

		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Select Image Sizes','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'image_sizes',
			'choices'		=> $this->get_image_sizes( array(), 'name' ),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All Image Sizes",'acf'),
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 1
			)
		));


		acf_render_field_setting( $field, array(
			'label'			=> __('Named ','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'named',
			'ui'			=> 1,
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			)
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Cropped ','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'cropped',
			'ui'			=> 1,
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			)
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Builtin','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'button_group',
			'choices'		=> array(
				''		=> __( 'Don‘t care', 'wp-acf-objects' ),
				'1'		=> __('Yes', 'wp-acf-objects' ),
				'0'		=> __('No', 'wp-acf-objects' ),
			),
			'name'			=> 'builtin',
			'ui'			=> 1,
			'conditions'	=> array(
				'field'		=> 'pick',
				'operator'	=> '==',
				'value'		=> 0
			)
		));


		// ajax
		acf_hidden_input(array(
			'name'			=> 'ajax',
			'value'			=> 0,
		));


	}

	/**
	 *	@param $args see get_taxonomies() $args param
	 *	@param $return property to return
	 *	@return array
	 */
	private function get_image_sizes( $args = array(), $return = null ) {
		$args = wp_parse_args( $args, array(
			'names'		=> false,
			'named'		=> '',
			'cropped'	=> '',
			'builtin'	=> '',
		) );

		$sizes = $this->get_all_image_sizes();
		$allowed = false;

		if ( $args['names'] ) {
			$allowed = $args['names'];
		} else {
			$allowed = array_keys($sizes);
			if ( $args['builtin'] !== '' ) {
				$builtin = array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' );
				if ( $args['builtin'] ) {
					$allowed = array_intersect( $allowed, $builtin );
				} else {
					$allowed = array_diff( $allowed, $builtin );
				}
			}

			if ( $args['named'] !== '' ) {
				$named = intval($args['named']) === 1;
				$sizes = array_filter( $sizes, function( $val ) use ($named) {
					return ( $val['name'] === '' ) === ! $named;
				} );

			}

			if ( $args['cropped'] !== '' ) {
				$cropped = intval($args['cropped']) === 1;
				$sizes = array_filter( $sizes, function( $val ) use ($cropped) {
					return $val['crop'] === $cropped;
				} );
			}
		}

		if ( $allowed !== false ) {
			$sizes = array_filter( $sizes, function( $key ) use ( $allowed ) {
				return in_array( $key, $allowed );
			}, ARRAY_FILTER_USE_KEY );
		}


		if ( is_null( $return ) ) {
			return $sizes;
		}

		foreach ( array_keys( $sizes ) as $slug ) {
			$size = $sizes[ $slug ];
			if ( ! isset( $size[ $return ] ) ) {
				continue;
			}
			$sizes[ $slug ] = $size[ $return ];
			if ( empty( $sizes[ $slug ] ) ) {
				$sizes[ $slug ] = $slug;
			}
			if ( $return === 'name' ) {
				$sizes[ $slug ] .= sprintf( ' (%d&times;%d)', $size[ 'width' ], $size[ 'height' ] );
			}
		}

		return $sizes;
	}



	/**
	 *	Get all image sizes
	 *
	 *	@param	bool	$with_core	Include Core sizes thumbnail, medium, medium_large
	 *
	 *	@return assocative array with all image sizes, their names and labels
	 */
	public function get_all_image_sizes() {

		global $_wp_additional_image_sizes;

		if ( ! empty( $this->all_sizes ) ) {
			return $this->all_sizes;
		}

		$this->all_sizes = array();

		$core_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' );

		$core_size_names = array(
			'thumbnail' => __( 'Thumbnail' ),
			'medium'    => __( 'Medium' ),
			'large'     => __( 'Large' ),
			'full'      => __( 'Full Size' )
		);

		// get size names
		$size_names = apply_filters( 'image_size_names_choose', $core_size_names );

		$intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info
		foreach( $intermediate_image_sizes as $_size ) {

			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$w    = intval( get_option( $_size . '_size_w' ) );
				$h    = intval( get_option( $_size . '_size_h' ) );
				$crop = (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$w    = intval( $_wp_additional_image_sizes[ $_size ]['width'] );
				$h    = intval( $_wp_additional_image_sizes[ $_size ]['height'] );
				$crop = (bool) $_wp_additional_image_sizes[ $_size ]['crop'];
			}

			$this->all_sizes[$_size] = array(
				'width'			=> $w,
				'height'		=> $h,
				'crop'			=> $crop,
				'slug'			=> $_size,
				'name'			=> isset( $size_names[$_size] ) ? $size_names[$_size] : '',
			);
		}
		return $this->all_sizes;
	}


	function format_value_single( $value, $post_id, $field ) {

		// bail ealry if is empty
		if( acf_is_empty($value) ) {
			return $value;
		}


		// value
		if( $field['return_format'] == 'array' ) {

			// do nothing
			$sizes = $this->get_all_image_sizes();
			if ( isset( $sizes[ $value ] ) ) {
				$value = $sizes[ $value ];
			}

			// label
		} elseif( $field['return_format'] == 'slug' ) {

		}
		// return
		return $value;

	}

}
