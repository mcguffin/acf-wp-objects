<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;


class WPObjects extends Core\Singleton {

	/**
	 *	storage_type: option, theme_mod, term, post
	 *	storage_key: post/term property (like post_title), option_name or theme mod name
	 *
	 *	@var array field choices array(
	 *		acf_field_type => array(
	 *			storage_type:storage_key => label
	 * 		)
	 * )
	 */
	private $field_choices = array();

	/**
	 *	@var array supported field types
	 */
	private $supported_fields = array(
		'text',
		'textarea',
		'wysiwyg',
		'image',
		'post_object',
		'relation',
		'gallery',
	);

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		$this->field_choices = array(
			'text'		=> array(
				'option:blogname'				=> __('Blogname','acf-wp-objects'),
				'option:blogdescription'		=> __('Blog description','acf-wp-objects'),
				'post:post_title'				=> __('Post Title','acf-wp-objects'),
				'term:term_name'				=> __('Term Title','acf-wp-objects'),
			),
			'textarea'	=> array(
				'post:post_excerpt'				=> __('Post Excerpt','acf-wp-objects'),
				'term:term_description'			=> __('Term Description','acf-wp-objects'),
			),
			'wysiwyg'	=> array(
				'post:post_content'				=> __('Post Content','acf-wp-objects'),
				'term:term_description'			=> __('Term Description','acf-wp-objects'),
			),
			'image'		=> array(
				'theme_mod:custom_logo'			=> __( 'Custom Logo', 'acf-wp-objects' ),
			//	'theme_mod:background_image'	=> __( 'Background Image', 'acf-wp-objects' ), // can't use ... WP saves a plain URL.
				'post:post_thumbnail'			=> __( 'Post Thumbnail', 'acf-wp-objects' ),
			),
			'relation'	=> array(
				'option:page_for_posts'			=> __( 'Page for Posts', 'acf-wp-objects' ),
				'option:page_on_front'			=> __( 'Page on Front', 'acf-wp-objects' ),
			),
			'gallery'	=> array(
				'post:attachments'				=> __( 'Post Attachments', 'acf-wp-objects' ),
			),
		);

		foreach ( $this->supported_fields as $field_type ) {

			add_action( "acf/render_field_settings/type={$field_type}", array( $this, 'field_settings' ) );

			add_filter( "acf/load_field/type={$field_type}", array( $this, 'load_field') );

		}

		add_filter( 'acf/pre_update_value', array( $this, 'pre_update_value' ), 10, 4 );


	}

	/**
	 *	@filter acf/load_field/type={$field_type}",
	 */
	public function load_field( $field ) {
		if ( isset( $field['wp_object'] ) && $field['wp_object'] ) {
			add_filter( "acf/load_value/key={$field['key']}", array( $this, 'load_value' ), 10, 3 );
		}

		return $field;
	}



	/**
	 *	NOT IN USE
	 *	@action acf/pre_load_value
	 */
	// public function pre_load_value( $check, $post_id, $field ) {
	//
	// 	if ( is_customize_preview() ) {
	// 		// return $check;
	// 	}
	//
	// 	if ( ! $storage_key = $this->get_field_storage( $field ) ) {
	// 		return $check;
	// 	}
	// 	list( $storage, $key ) = $storage_key;
	//
	//
	// 	switch ( $storage ) {
	// 		case 'theme_mod':
	// 			return get_theme_mod( $key );
	// 		case 'option':
	// 			return get_option( $key );
	// 		case 'term':
	// 			return 'NOT IMPLEMENTED YET';
	// 		case 'post':
	//
	// 			if ( 'post_title' == $key ) {
	// 				return get_the_title( $post_id );
	// 			} else if ( 'post_excerpt' == $key ) {
	// 				if ( $post = get_post( $post_id ) ) {
	// 					return $post->post_excerpt;
	// 				}
	// 				return $check;
	//
	// 			} else if ( 'post_content' == $key ) {
	// 				if ( $post = get_post( $post_id ) ) {
	// 					return $post->post_content;
	// 				}
	// 				return $check;
	//
	// 			} else if ( 'post_thumbnail' == $key ) {
	// 				return get_post_thumbnail_id( $post_id );
	// 			}
	// 	}
	// 	return $check;
	//
	// }

	/**
	 *	@action acf/load_value/key={$field_key}
	 */
	public function load_value( $value, $post_id, $field ) {

		if ( ! $storage_key = $this->get_field_storage( $field ) ) {
			return $value;
		}

		list( $storage, $key ) = $storage_key;


		switch ( $storage ) {
			case 'theme_mod':
				$value = get_theme_mod( $key );
			case 'option':
				$value = get_option( $key );
			case 'term':
				$info = acf_get_post_id_info( $post_id );
				if ( 'term_name' === $key ) {
					//if ( $term = get_term() )
				} else if ( 'term_description' === $key ) {

				}
				$value = 'NOT IMPLEMENTED YET';
			case 'post':

				if ( 'post_title' == $key ) {
					$value = get_the_title( $post_id );
				} else if ( 'post_content' == $key ) {
					if ( $post = get_post( $post_id ) ) {
						$value = $post->post_content;
					}

				} else if ( 'post_excerpt' == $key ) {
					if ( $post = get_post( $post_id ) ) {
						$value = $post->post_excerpt;
					}

				} else if ( 'post_thumbnail' == $key ) {
					$value = get_post_thumbnail_id( $post_id );
				} else if ( 'attachments' == $key ) {
					// IDs of children
					$value = $this->get_attachment_ids( $post_id );
				}
		}
		return $value;

	}
	/**
	 *	@filter acf/pre_update_value
	 */
	public function pre_update_value( $check, $value, $post_id, $field ) {
		if ( ! $storage_key = $this->get_field_storage( $field ) ) {
			return $check;
		}

		list( $storage, $key ) = $storage_key;

		switch ( $storage ) {
			case 'theme_mod':
				set_theme_mod( $key, $value );
				return true;
			case 'option':
				update_option($key);
				return true;
			case 'term':
				// update term
				return true;
			case 'post':
				if ( ! absint( $post_id ) ) {
					return $check;
				}
				$updatepost = array();

				if ( 'post_title' === $key ) {
					$updatepost['post_title'] = $value;
				} else if ( 'post_content' === $key ) {
					$updatepost['post_content'] = $value;
				} else if ( 'post_excerpt' === $key ) {
					$updatepost['post_excerpt'] = $value;
				} else if ( 'post_thumbnail' === $key ) {
					if ( $value ) {
						set_post_thumbnail( $post_id, $value );
					} else {
						delete_post_thumbnail( $post_id );
					}
				} else if ( 'attachments' === $key ) {
					// set attachment parent ID if not set
					$value = (array) $value;
					$value = array_filter( $value );

					// old attachment IDs ...
					$attachment_ids = $this->get_attachment_ids( $post_id );

					foreach ( $attachment_ids as $attachment_id ) {
						if ( ! in_array( $attachment_id, $value ) ) {
							$update_attachment = array(
								'ID'			=> $attachment_id,
								'post_parent'	=> 0,
							);
							wp_update_post( $update_attachment );
						}
					}
					if ( empty( $value ) ) {
						// default behaviour
						return $check;
					}
					foreach ( $value as $i => $attachment_id ) {
						$update_attachment = array(
							'ID'			=> $attachment_id,
							'post_parent'	=> $post_id,
							'menu_order'	=> $i,
						);
						wp_update_post( $update_attachment );
					}
				}
				if ( ! empty( $updatepost ) ) {
					$updatepost['ID'] = $post_id;
					wp_update_post( $updatepost );
				}
				return true;
		}
	}

	/**
	 *	@param int $post_id
	 */
	private function get_attachment_ids( $post_id ) {
		return get_posts( array(
			'posts_per_page'	=> -1,
			'orderby'			=> 'menu_order',
			'order'				=> 'ASC',
			'fields'			=> 'ids',
			'post_type'			=> 'attachment',
			'post_parent'		=> $post_id,
		) );
	}

	/**
	 *	Render WP Object field setting
	 *
	 *	@param array $field
	 *	@action acf/render_field_settings/type={$type}
	 */
	public function field_settings( $field ) {
		// default_value
		if ( ! $choices = $this->get_wp_objects( $field['type'] ) ) {
			return;
		}

		acf_render_field_setting( $field, array(
			'label'			=> __('WordPress Object','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'wp_object',
			'choices'		=> $choices,
			'multiple'		=> 0,
			'ui'			=> 0,
			'allow_null'	=> 1,
			'placeholder'	=> __( 'Select', 'acf-wp-objects' ),
		));

	}

	/**
	 *	@action acf/save_value/type={$type}
	 */


	/**
	 *	@usedby pre_update_value()
	 */
	public function get_field_storage( $field ) {
		$field = wp_parse_args($field, array(
			'wp_object' => false,
		));

		if ( ! $field['wp_object'] ) {
			return false;
		}
		return explode( ':', $field['wp_object'] );
	}

	/**
	 *	@usedby field_settings()
	 */
	private function get_wp_objects( $field_type ) {
		switch ( $field_type ) {
			case 'relation':
			case 'post_object':
				return $this->field_choices[ 'relation' ];
			case 'text':
			case 'textarea':
			case 'image':
			case 'wysiwyg':
			default:
				return $this->field_choices[ $field_type ];
		}
		return false;
	}
}