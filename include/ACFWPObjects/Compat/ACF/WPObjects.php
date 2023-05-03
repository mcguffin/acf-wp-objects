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
	 *	@var array field choices [
	 *		acf_field_type => [
	 *			storage_type:storage_key => label
	 * 		]
	 * ]
	 */
	private $field_choices = [];


	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		$this->field_choices = [
			'text'		=> [
				'option:blogname'				=> __('Blogname','acf-wp-objects'),
				'option:blogdescription'		=> __('Blog description','acf-wp-objects'),
				'post:post_title'				=> __('Post Title','acf-wp-objects'),
				'post:post_name'				=> __('Post Slug','acf-wp-objects'),
				'term:term_name'				=> __('Term Title','acf-wp-objects'),
			],
			'number'	=> [
				'option:posts_per_page'			=> __('Posts per page','acf-wp-objects'),
			],
			'textarea'	=> [
				'post:post_excerpt'				=> __('Post Excerpt','acf-wp-objects'),
				'term:term_description'			=> __('Term Description','acf-wp-objects'),
			],
			'wysiwyg'	=> [
				'post:post_content'				=> __('Post Content','acf-wp-objects'),
				'term:term_description'			=> __('Term Description','acf-wp-objects'),
			],
			'image'		=> [
				'theme_mod:custom_logo'			=> __( 'Custom Logo', 'acf-wp-objects' ),
			//	'theme_mod:background_image'	=> __( 'Background Image', 'acf-wp-objects' ), // can't use ... WP saves a plain URL.
				'post:post_thumbnail'			=> __( 'Post Thumbnail', 'acf-wp-objects' ),
			],
			'post_object'	=> [
				'option:page_for_posts'			=> __( 'Page for Posts', 'acf-wp-objects' ),
				'option:page_on_front'			=> __( 'Page on Front', 'acf-wp-objects' ),
			],
			'relationship'	=> [
				'option:page_for_posts'			=> __( 'Page for Posts', 'acf-wp-objects' ),
				'option:page_on_front'			=> __( 'Page on Front', 'acf-wp-objects' ),
			],
			'gallery'	=> [
				'post:attachments'				=> __( 'Post Attachments', 'acf-wp-objects' ),
			],
		];

		foreach ( array_keys( $this->field_choices ) as $field_type ) {

			add_action( "acf/render_field_settings/type={$field_type}", [ $this, 'field_settings' ] );

			add_filter( "acf/load_field/type={$field_type}", [ $this, 'load_field'] );

		}

		add_filter( 'acf/pre_update_value', [ $this, 'pre_update_value' ], 10, 4 );


	}

	/**
	 *	@filter acf/load_field/type={$field_type}",
	 */
	public function load_field( $field ) {
		if ( isset( $field['wp_object'] ) && $field['wp_object'] ) {
			add_filter( "acf/load_value/key={$field['key']}", [ $this, 'load_value' ], 10, 3 );
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
				break;
			case 'option':
				$value = get_option( $key );
				break;
			case 'term':

				if ( $term = $this->get_term( $post_id ) ) {

					if ( 'term_name' === $key ) {
						//if ( $term = get_term() )
						$value = $term->name;
					} else if ( 'term_description' === $key ) {
						$value = $term->description;
					}
				}

				break;
			case 'post':

				if ( 'post_title' == $key ) {
					$value = get_the_title( $post_id );
				} else if ( 'post_name' == $key ) {
					$value = get_post_field( 'post_name', $post_id );
				} else if ( 'post_content' == $key ) {
					if ( $post = get_post( $post_id ) ) {
						$value = $post->post_content;
					}

				} else if ( 'post_excerpt' == $key ) {
					if ( $post = get_post( $post_id ) ) {
						$value = $post->post_excerpt;
					}

				} else if ( 'post_thumbnail' == $key ) {

					if ( is_preview() ) {
						if ( isset( $_GET['_thumbnail_id'] ) && wp_unslash( $_GET['_thumbnail_id']) > 1 ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							$value = wp_unslash( $_GET['_thumbnail_id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						} else {
							$value = 0;
						}
					} else {
						$value = get_post_thumbnail_id( $post_id );
					}

				} else if ( 'attachments' == $key ) {
					// IDs of children
					$value = $this->get_attachment_ids( $post_id );
				}
				break;
		}
		return $value;

	}


	public function wp_preview_post_thumbnail_filter( $value, $post_id, $meta_key ) {

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
				update_option( $key, $value );
				return true;
			case 'term':
				// update term ... works on create too

				if ( $term = $this->get_term( $post_id ) ) {

					$update_term = [];
					if ( 'term_name' === $key ) {
						$update_term['name'] = $value;
					} else if ( 'term_description' === $key ) {
						$update_term['description'] = $value;
					}

					if ( ! empty( $update_term ) ) {
						wp_update_term( $term->term_id, $term->taxonomy, $update_term );
					}
				}
				return true;
			case 'post':
				if ( /*$this->is_post_preview() ||*/ ! absint( $post_id ) ) {
					return $check;
				}

				$updatepost = [];
				if ( 'post_title' === $key ) {
					$updatepost['post_title'] = $value;
				} else if ( 'post_name' === $key ) {
					$post = get_post( $post_id );

					// default value
					if ( empty( $value ) ) {
						$value = sanitize_title( $post->post_title, $post_id );
					} else {
						$value = sanitize_title( $value, $post_id );
					}
					$value = wp_unique_post_slug( $value, $post_id, $post->post_status, $post->post_type, $post->post_parent );
					$updatepost['post_name'] = $value;
				} else if ( 'post_content' === $key ) {
					$updatepost['post_content'] = $value;
				} else if ( 'post_excerpt' === $key ) {
					$updatepost['post_excerpt'] = $value;
				} else if ( 'post_thumbnail' === $key ) {

					if ( $value ) {
						if ( $this->is_post_preview_saving() ) {
							$_POST['_thumbnail_id'] = $value;
						} else {
							set_post_thumbnail( $post_id, $value );
						}
					} else {
						if ( $this->is_post_preview_saving() ) {
							$_POST['_thumbnail_id'] = 0;
						} else {
							delete_post_thumbnail( $post_id );
						}
					}
					return $check;
				} else if ( 'attachments' === $key ) {
					// set attachment parent ID if not set
					$value = (array) $value;
					$value = array_filter( $value );

					// old attachment IDs ...
					$attachment_ids = $this->get_attachment_ids( $post_id );

					foreach ( $attachment_ids as $attachment_id ) {
						if ( ! in_array( $attachment_id, $value ) ) {
							$update_attachment = [
								'ID'			=> $attachment_id,
								'post_parent'	=> 0,
							];
							wp_update_post( $update_attachment );
						}
					}
					if ( empty( $value ) ) {
						// default behaviour
						return $check;
					}
					foreach ( $value as $i => $attachment_id ) {
						$update_attachment = [
							'ID'			=> $attachment_id,
							'post_parent'	=> $post_id,
							'menu_order'	=> $i,
						];
						wp_update_post( $update_attachment );
					}
				}
				if ( ! empty( $updatepost ) ) {
					//$updatepost['ID'] = $post_id;
					global $wpdb;
					$updatepost = wp_unslash( $updatepost );
					$wpdb->update( $wpdb->posts, $updatepost, [ 'ID' => $post_id ] );
			//		wp_update_post( $updatepost );
				}
				return true;
		}
	}

	/**
	 *	Whether this is a post preview
	 *	@return Boolean
	 */
	private function is_post_preview() {

		return is_preview() || $this->is_post_preview_save();
	}


	/**
	 *	Whether a post preview is currently saving
	 *
	 *	@return Boolean
	 */
	private function is_post_preview_saving() {

		return isset( $_POST['wp-preview'] ) && 'dopreview' === $_POST['wp-preview'];  // phpcs:ignore WordPress.Security.NonceVerification.Missing

	}


	/**
	 *	@param string|int $post_id ACF post_id
	 */
	private function get_term( $post_id ) {
		$info = acf_get_post_id_info( $post_id );

		if ( 'term' === $info['type'] && $info['id'] ) {
			return get_term( $info['id'] );
		}
		return null;
	}

	/**
	 *	@param int $post_id
	 */
	private function get_attachment_ids( $post_id ) {
		return get_posts( [
			'posts_per_page'	=> -1,
			'orderby'			=> 'menu_order',
			'order'				=> 'ASC',
			'fields'			=> 'ids',
			'post_type'			=> 'attachment',
			'post_parent'		=> $post_id,
		] );
	}

	/**
	 *	Render WP Object field setting
	 *
	 *	@param array $field
	 *	@action acf/render_field_settings/type={$type}
	 */
	public function field_settings( $field ) {

		$choices = $this->get_wp_objects( $field['type'] );
		if ( ! $choices ) {
			return;
		}

		acf_render_field_setting( $field, [
			'label'			=> __('WordPress Object','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'wp_object',
			'choices'		=> $choices,
			'multiple'		=> 0,
			'ui'			=> 0,
			'allow_null'	=> 1,
			'placeholder'	=> __( 'Select', 'acf-wp-objects' ),
		]);

	}

	/**
	 *	@action acf/save_value/type={$type}
	 */


	/**
	 *	@usedby pre_update_value()
	 */
	public function get_field_storage( $field ) {
		$field = wp_parse_args($field, [
			'wp_object' => false,
		]);

		if ( ! $field['wp_object'] ) {
			return false;
		}
		return explode( ':', $field['wp_object'] );
	}

	/**
	 *	@usedby field_settings()
	 */
	private function get_wp_objects( $field_type ) {

		if ( isset( $this->field_choices[ $field_type ] ) ) {
			return $this->field_choices[ $field_type ];
		}

		return false;
	}
}
