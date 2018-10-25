<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;



class ACFCustomizer extends Core\PluginComponent {
	private $changeset_data = null;

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_action( 'customize_register', array( $this, 'customize_register' ), 20, 1 );
	}
	/**
	 *	@action customize_register
	 */
	public function customize_register( $manager ) {

		if ( is_customize_preview() && ! is_admin() ) {
			$preview = \ACFCustomizer\Compat\ACF\CustomizePreview::instance();
			$this->changeset_data = $preview->changeset_data( $manager );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		}
	}

	/**
	 *	@action template_redirect
	 */
	public function template_redirect( ) {

		$acf = ACF\ACF::instance();

		foreach ( $this->changeset_data as $section => $changes ) {
			if ( ! is_array( $changes['value'] ) ) {
				continue;
			}
			foreach ( $changes['value'] as $field_key => $value ) {

				$field = acf_get_field( $field_key );

				if ( ! $storage_key = $acf->get_field_storage( $field ) ) {
					continue;
				}

				list( $storage, $key ) = $storage_key;

				if ( 'post' === $storage && is_singular() ) {
					if ( 'post_title' === $key ) {
						add_filter( 'the_title', array( new ACF\Hook\Preview( $field, $value ), 'get' ), 0 );
					} else if ( 'post_content' === $key ) {
						add_filter( 'the_content', array( new ACF\Hook\Preview( $field, $value ), 'get' ), 0 );
					} else if ( 'post_thumbnail' === $key ) {
						add_filter('get_post_metadata', array( new ACF\Hook\ThumbnailPreview( $field, $value ), 'get' ), 0, 4 );
					}
				}

			}
		}

	}

	/**
	 *
	 */


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
