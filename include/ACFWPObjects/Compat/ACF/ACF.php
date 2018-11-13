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


class ACF extends Core\PluginComponent {

	private $field_choices = array();
	private $supported_fields = array(
		'text',
		'textarea',
		'wysiwyg',
		'image',
		'post_object',
		'relation',
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
		);

		add_action( 'init', array( $this, 'register_field_types' ) );

		add_action( 'acf/include_location_rules', array( $this, 'register_location_rules' ) );

		WPObjects::instance();

		if ( acf_get_setting('pro') ) {
			// init repeater choices
			RepeaterChoices::instance();
		}
	}

	/**
	 *	@action acf/include_location_rules
	 */
	public function register_location_rules() {

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\PostTypeProp' );

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\TaxonomyProp' );

	}

	/**
	 *	@action init
	 */
	public function register_field_types() {

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectPostType' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectTaxonomy' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectImageSize' );

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
