<?php
/**
 *	@package ACFWPObjects\Core
 *	@version 1.0.1
 *	2018-09-22
 */

namespace ACFWPObjects\Core;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}
use ACFWPObjects\Asset;
use ACFWPObjects\Compat;

class WP extends Singleton {

	/**
	 *	@param $args see get_taxonomies() $args param
	 *	@param $return property to return
	 *	@return array
	 */
	public function get_image_sizes( $args = [], $return = null ) {
		$args = wp_parse_args( $args, [
			'names'		=> false,
			'named'		=> '',
			'crop'		=> '',
			'_builtin'	=> '',
		] );

		$sizes = $this->get_all_image_sizes();
		$allowed = false;

		if ( $args['names'] ) {
			$allowed = $args['names'];
		} else {
			foreach ( [ 'named', 'crop', '_builtin' ] as $prop ) {
				if ( $args[$prop] !== '' ) {
					$propval = intval( $args[ $prop ] ) === 1;
					$sizes = array_filter( $sizes, function( $size ) use ( $prop, $propval ) {
						return $size[ $prop ] === $propval;
					} );
				}
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

		// format value
		foreach ( array_keys( $sizes ) as $slug ) {
			$size = $sizes[ $slug ];
			if ( ! isset( $size[ $return ] ) ) {
				continue;
			}
			$sizes[ $slug ] = $size[ $return ];
			if ( empty( $sizes[ $slug ] ) ) {
				$sizes[ $slug ] = $slug;
			}
			if ( $return === 'label' ) {
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

		$this->all_sizes = [];

		$core_sizes = [ 'thumbnail', 'medium', 'large' ];
		$builtin_sizes = array_merge( $core_sizes, [ 'medium_large', 'full' ]);

		$core_size_names = [
			'thumbnail' => __( 'Thumbnail' ),
			'medium'    => __( 'Medium' ),
			'large'     => __( 'Large' ),
			'full'      => __( 'Full Size' )
		];

		// get size names
		$size_names = apply_filters( 'image_size_names_choose', $core_size_names );

		$intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info
		foreach( $intermediate_image_sizes as $_size ) {

			if ( in_array( $_size, $core_sizes ) ) {
				$w    = intval( get_option( $_size . '_size_w' ) );
				$h    = intval( get_option( $_size . '_size_h' ) );
				$crop = (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$w    = intval( $_wp_additional_image_sizes[ $_size ]['width'] );
				$h    = intval( $_wp_additional_image_sizes[ $_size ]['height'] );
				$crop = (bool) $_wp_additional_image_sizes[ $_size ]['crop'];
			}

			$this->all_sizes[$_size] = [
				'width'			=> $w,
				'height'		=> $h,
				'crop'			=> $crop,
				'name'			=> $_size,
				'_builtin'		=> array_search( $_size, $builtin_sizes ) !== false,
				'label'			=> isset( $size_names[$_size] ) ? $size_names[$_size] : '',
				'named'			=> isset( $size_names[$_size] ),
			];
		}
		return $this->all_sizes;
	}




	/**
	 *	@param array $args see get_post_types() $args param
	 *	@param string $return Post type property to return
	 *	@return array pt_slug => $return
	 */
	public function get_post_types( $args = [], $return = null ) {
		if ( isset( $args['names'] ) ) {
			$post_types = [];
			$names = $args['names'];
			unset($args['names']);
			foreach ( $names as $name ) {
				$args['name'] = $name;
				$post_types += get_post_types( $args, 'objects' );
			}
		} else {
			$post_types = get_post_types( $args, 'objects' );
		}

		if ( is_null( $return ) ) {
			return $post_types;
		}
		foreach ( array_keys( $post_types ) as $slug ) {
			if ( ! isset( $post_types[ $slug ]->$return ) ) {
				continue;
			}
			$post_types[ $slug ] = $post_types[ $slug ]->$return;
		}

		return $post_types;
	}


	/**
	 *	@param array $args see get_taxonomies() $args param
	 *	@param string $return Taxonomy property to return
	 *	@return array
	 */
	public function get_taxonomies( $args = [], $return = null ) {
		if ( isset( $args['names'] ) ) {
			$taxonomies = [];
			$names = $args['names'];
			unset($args['names']);
			foreach ( $names as $name ) {
				$args['name'] = $name;
				$taxonomies += get_taxonomies( $args, 'objects' );
			}
		} else {
			$taxonomies = get_taxonomies( $args, 'objects' );
		}

		if ( is_null( $return ) ) {
			return $taxonomies;
		}
		foreach ( array_keys( $taxonomies ) as $slug ) {
			if ( ! isset( $taxonomies[ $slug ]->$return ) ) {
				continue;
			}
			$taxonomies[ $slug ] = $taxonomies[ $slug ]->$return;
		}

		return $taxonomies;
	}


	/**
	 *	@return array Rolenames
	 */
	public function get_roles() {

		return wp_roles()->get_names();

	}
}
