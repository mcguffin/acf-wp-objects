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


		add_action( 'init', array( $this, 'register_field_types' ) );

		add_action( 'acf/include_location_rules', array( $this, 'register_location_rules' ) );

		WPObjects::instance();

		if ( acf_get_setting('pro') ) {
			// init repeater choices
			RepeaterChoices::instance();
		}

		add_action( 'acf/enqueue_scripts', array( $this, 'enqueue_style' ) );
		add_action( 'acf/field_group/admin_enqueue_scripts', array( $this, 'enqueue_field_group' ) );
	}

	/**
	 *	@action acf/enqueue_scripts
	 */
	public function enqueue_style() {
		$core = Core\Core::instance();
		wp_enqueue_style( 'acf-wp-objects-input', $core->get_asset_url('css/admin/acf-input.css'), array('acf-input') );
	}

	/**
	 *	@action acf/field_group/admin_enqueue_scripts
	 */
	public function enqueue_field_group() {
		$core = Core\Core::instance();
		$choices = RepeaterChoices::instance();
		wp_enqueue_script( 'acf-wp-objects-field-group', $core->get_asset_url('js/admin/acf-field-group.js'), array('acf-field-group') );
		wp_localize_script( 'acf-wp-objects-field-group', 'acf_wp_objects', array(
			'repeated_fields' => $choices->get_repeated_fields(),
		) );

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
