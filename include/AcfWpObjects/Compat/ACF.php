<?php
/**
 *	@package AcfWpObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace AcfWpObjects\Compat;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use AcfWpObjects\Core;


class ACF extends Core\PluginComponent {

	private $field_choices = array();

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
			'wysiwyg'	=> array(
				'post:post_content'				=> __('Post Content','acf-wp-objects'),
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
		);

		add_action('init',array($this,'init'));

		// options blogname, blogdescription, post_title, term name
		add_action( 'acf/render_field_settings/type=text', array( $this, 'field_settings' ) );
		add_action( 'acf/render_field_settings/type=wysiwyg', array( $this, 'field_settings' ) );
		add_action( 'acf/render_field_settings/type=image', array( $this, 'field_settings' ) );
		add_action( 'acf/render_field_settings/type=post_object', array( $this, 'field_settings' ) );
		add_action( 'acf/render_field_settings/type=relation', array( $this, 'field_settings' ) );
		//*
		add_filter( 'acf/load_value/type=text', array( $this, 'load_value' ), 10, 3 );
		add_filter( 'acf/load_value/type=wysiwyg', array( $this, 'load_value' ), 10, 3 );
		add_filter( 'acf/load_value/type=image', array( $this, 'load_value' ), 10, 3 );
		add_filter( 'acf/load_value/type=post_object', array( $this, 'load_value' ), 10, 3 );
		add_filter( 'acf/load_value/type=relation', array( $this, 'load_value' ), 10, 3 );
		/*/
		add_filter( 'acf/pre_load_value', array( $this, 'pre_load_value' ), 10, 3 );
		//*/

		add_filter( 'acf/pre_update_value', array( $this, 'pre_update_value' ), 10, 4 );
	}

	public function init() {

		acf_register_field_type( '\AcfWpObjects\Compat\Fields\SelectPostType' );

		acf_register_field_type( '\AcfWpObjects\Compat\Fields\SelectTaxonomy' );

	}

	/**
	 *	@action acf/pre_load_value
	 */
	public function pre_load_value( $check, $post_id, $field ) {

		if ( is_customize_preview() ) {
			// return $check;
		}

		if ( ! $storage_key = $this->get_field_storage( $field ) ) {
			return $check;
		}
		list( $storage, $key ) = $storage_key;


		switch ( $storage ) {
			case 'theme_mod':
				return get_theme_mod( $key );
			case 'option':
				return get_option( $key );
			case 'term':
				return 'NOT IMPLEMENTED YET';
			case 'post':

				if ( 'post_title' == $key ) {
					return get_the_title( $post_id );
				} else if ( 'post_content' == $key ) {
					if ( $post = get_post( $post_id ) ) {
						return $post->post_content;
					}
					return $check;

				} else if ( 'post_thumbnail' == $key ) {
					return get_post_thumbnail_id( $post_id );
				}
		}
		return $check;

	}
	/**
	 *	@action acf/load_value/type={$type}
	 */
	public function load_value( $value, $post_id, $field ) {

		if ( ! $storage_key = $this->get_field_storage( $field ) ) {
			return $value;
		}

		list( $storage, $key ) = $storage_key;


		switch ( $storage ) {
			case 'theme_mod':
				return get_theme_mod( $key );
			case 'option':
				return get_option( $key );
			case 'term':
				return 'NOT IMPLEMENTED YET';
			case 'post':

				if ( 'post_title' == $key ) {
					return get_the_title( $post_id );
				} else if ( 'post_content' == $key ) {
					if ( $post = get_post( $post_id ) ) {
						return $post->post_content;
					}
					return $value;

				} else if ( 'post_thumbnail' == $key ) {
					return get_post_thumbnail_id( $post_id );
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
				// return get_option($key);
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
				} else if ( 'post_thumbnail' === $key ) {
					if ( $value ) {
						set_post_thumbnail( $post_id, $value );
					} else {
						delete_post_thumbnail( $post_id );
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
	 * ...
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
	 *	...
	 */
	private function get_wp_objects( $field_type ) {
		switch ( $field_type ) {
			case 'text':
			case 'image':
			case 'wysiwyg':
				return $this->field_choices[ $field_type ];
			case 'relation':
			case 'post_object':
				return $this->field_choices[ 'relation' ];
		}
		return false;
	}


	/**
	 *	@inheritdoc
	 */
	 public function activate(){

	 }

	 /**
	  *	@inheritdoc
	  */
	 public function deactivate(){

	 }

	 /**
	  *	@inheritdoc
	  */
	 public static function uninstall() {
		 // remove content and settings
	 }

	/**
 	 *	@inheritdoc
	 */
	public function upgrade( $new_version, $old_version ) {
	}

}
