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


use ACFWPObjects\Asset;
use ACFWPObjects\Core;


class ACF extends Core\Singleton {

	private $supported_fields = array(
		'text',
		'textarea',
		'wysiwyg',
		'image',
		'post_object',
		'relation',
	);

	private $acf_input_js;
	private $acf_input_css;
	private $acf_field_group_js;

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

		if ( is_admin() ) {
			Form\WPOptions::instance();
		}

		add_action( 'acf/enqueue_scripts', array( $this, 'enqueue_style' ) );
		add_action( 'acf/field_group/admin_enqueue_scripts', array( $this, 'enqueue_field_group' ) );
		add_action( 'acf/input/admin_enqueue_scripts', array( $this, 'enqueue_input' ) );

		$this->acf_input_js			= Asset\Asset::get( 'js/admin/acf-input.js' );
		$this->acf_input_css		= Asset\Asset::get( 'css/admin/acf-input.css' );
		$this->acf_field_group_js	= Asset\Asset::get( 'js/admin/acf-field-group.js' );

	}

	/**
	 *	@action acf/enqueue_scripts
	 */
	public function enqueue_style() {
		$this->acf_input_css
			->deps('acf-input')
			->enqueue();
	}

	/**
	 *	@action acf/field_group/admin_enqueue_scripts
	 */
	public function enqueue_field_group() {

		$choices = RepeaterChoices::instance();
		$core = Core\Core::instance();

		$this->acf_field_group_js
			->footer( false )
			->deps( 'acf-field-group', $this->acf_input_js )
			->localize(array(
				'repeated_fields' => $choices->get_repeated_fields(),
				'post_types'	=> array_map( [ $this, 'reduce_pt' ], $core->get_post_types() ),
				'taxonomies'	=> array_map( [ $this, 'reduce_taxo' ], $core->get_taxonomies() ),
				'image_sizes'	=> array_map( [ $this, 'mk_image_sizes' ], $core->get_image_sizes() ),
			), 'acf_wp_objects' )
			->enqueue();

	}

	/**
	 *	@param Array $im Image size
	 */
	private function mk_image_sizes( $size ) {
		$size['label'] = sprintf( '%s (%d×%d)',
			$size['label'] ? $size['label'] : $size['name'],
			$size[ 'width' ],
			$size[ 'height' ]
		);
		return $size;
	}

	/**
	 *	@param Object $pto Post type object
	 */
	private function reduce_pt( $pto ) {
		return array_intersect_key( get_object_vars( $pto ), array(
			'_builtin'			=> 1,
			'name'				=> 1,
			'label'				=> 1,
			'show_ui'			=> 1,
			'show_in_nav_menus'	=> 1,
			'show_in_menu'		=> 1,
			'public'			=> 1,
		));
	}

	/**
	 *	@param Object $tx Taxonomy object
	 */
	private function reduce_taxo( $tx ) {
		return array_intersect_key( get_object_vars( $tx ), array(
			'_builtin'			=> 1,
			'name'				=> 1,
			'label'				=> 1,
			'show_ui'			=> 1,
			'show_in_nav_menus'	=> 1,
			'show_in_menu'		=> 1,
			'public'			=> 1,
		));
	}



	/**
	 *	@action acf/input/admin_enqueue_scripts
	 */
	public function enqueue_input() {

		$this->acf_input_js
			->footer( false )
			->enqueue();

	}


	/**
	 *	@action acf/include_location_rules
	 */
	public function register_location_rules() {

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\ClassicEditor' );

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\PostTypeProp' );

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\TaxonomyProp' );

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\WPOptionsPage' );

	}

	/**
	 *	@action init
	 */
	public function register_field_types() {

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\ImageSweetSpot' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectPostType' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectTaxonomy' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectImageSize' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectRole' );

	}

}
