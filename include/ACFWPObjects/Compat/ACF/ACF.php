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
use ACFWPObjects\Compat\ACF\FieldOption;
use ACFWPObjects\Forms;
use ACFWPObjects\Fields;
use ACFWPObjects\Locations;

class ACF extends Core\Singleton {

	private $supported_fields = [
		'text',
		'textarea',
		'wysiwyg',
		'image',
		'post_object',
		'relation',
	];

	private $acf_input_js;
	private $acf_input_css;
	private $acf_field_group_js;

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'acf/init', [ $this, 'init' ] );

		add_action( 'acf/include_field_types', [ $this, 'register_field_types' ] );

		add_action( 'acf/include_location_rules', [ $this, 'register_location_rules' ] );

		WPObjects::instance();
		OptionsPage::instance();

		FieldOption\InlineWYSIWYG::instance();
		FieldOption\LayoutAccordion::instance();
		FieldOption\MessageTemplate::instance();
		FieldOption\Popup::instance();
		FieldOption\PrimaryTerm::instance();
		FieldOption\TextID::instance();

		if ( acf_get_setting('pro') ) {
			RepeaterChoices::instance();
			FieldOption\RepeaterPostSync::instance();
		}
		if ( is_admin() ) {
			Forms\WPOptions::instance();
		}

		add_action( 'acf/enqueue_scripts', [ $this, 'enqueue_style' ] );
		add_action( 'acf/field_group/admin_enqueue_scripts', [ $this, 'enqueue_field_group' ] );
		add_action( 'acf/input/admin_enqueue_scripts', [ $this, 'enqueue_input' ] );

		$this->acf_input_js			= Asset\Asset::get( 'js/admin/acf-input.js' );
		$this->acf_input_css		= Asset\Asset::get( 'css/admin/acf-input.css' );
		$this->acf_field_group_js	= Asset\Asset::get( 'js/admin/acf-field-group.js' );

		add_action( 'acf_wpo_load_fields', [ $this, 'load_all_fields' ] );
	}

	/**
	 *	@action acf_load_all_fields
	 */
	public function load_all_fields() {
		// we need this only once...
		foreach ( acf_get_field_groups() as $field_group ) {
			acf_get_fields( $field_group );
		}
	}

	/**
	 *	@action acf/init
	 */
	public function init() {

		$wp = Core\WP::instance();

		$this->acf_input_js
			->deps('wp-api-fetch')
			->localize( [
				'post_types'	=> array_map( [ $this, 'reduce_pt' ], $wp->get_post_types() ),
				'taxonomies'	=> array_map( [ $this, 'reduce_taxo' ], $wp->get_taxonomies() ),
				'image_sizes'	=> array_map( [ $this, 'mk_image_sizes' ], $wp->get_image_sizes() ),
				'object_type_props' => [
					'_builtin'			=> __( 'Builtin', 'acf-wp-objects' ),
					'public'			=> __( 'Public', 'acf-wp-objects' ),
					'show_ui'			=> __( 'Show UI', 'acf-wp-objects' ),
					'show_in_menu'		=> __( 'Show in Menus', 'acf-wp-objects' ),
					'show_in_nav_menus'	=> __( 'Show in Nav Menus', 'acf-wp-objects' ),
					'hierarchical'		=> __( 'Hierarchical', 'acf-wp-objects' ),
				],
			], 'acf_wp_objects' );

		if ( apply_filters( 'acf_image_sweetspot_enable', false ) ) {
			ImageSweetSpot::instance();
		}
	}

	/**
	 *	Whether we are in the fieldgroup admin
	 *
	 *	@return boolean
	 */
	public function is_fieldgroup_admin() {

		// local json compare ajax request
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && wp_unslash($_REQUEST['action']) === 'acf/ajax/local_json_diff' ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return true;
		}

		// is sync
		if ( isset( $_REQUEST['post_type'] ) && 'acf-field-group' === wp_unslash( $_REQUEST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return true;
		}

		// is field group admin
		global $pagenow;

		if (  $pagenow === 'post.php' ) {

			// sometimes WP knows post_id already, sometimes not
			$post = get_post( isset( $_REQUEST['post'] ) ? intval( $_REQUEST['post'] ) : null );

			if ( $post && 'acf-field-group' === $post->post_type ) {

				return true;
			}
		}

		return false;

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
		$this->acf_field_group_js
			->localize([
				'repeated_fields' => $choices->get_repeated_fields(),
			], 'acf_wp_objects_fieldgroup')
			->footer( false )
			->deps( 'acf-field-group', $this->acf_input_js )
			->enqueue();

	}

	/**
	 *	@param Array $size Image size
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
		return array_intersect_key( get_object_vars( $pto ), [
			'_builtin'			=> 1,
			'name'				=> 1,
			'label'				=> 1,
			'show_ui'			=> 1,
			'show_in_nav_menus'	=> 1,
			'show_in_menu'		=> 1,
			'public'			=> 1,
			'hierarchical'		=> 1,
		]);
	}

	/**
	 *	@param Object $tx Taxonomy object
	 */
	private function reduce_taxo( $tx ) {
		return array_intersect_key( get_object_vars( $tx ), [
			'_builtin'			=> 1,
			'name'				=> 1,
			'label'				=> 1,
			'show_ui'			=> 1,
			'show_in_nav_menus'	=> 1,
			'show_in_menu'		=> 1,
			'public'			=> 1,
			'hierarchical'		=> 1,
		]);
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

		acf_register_location_rule( 'ACFWPObjects\Locations\PostTypeProp' );
		acf_register_location_rule( 'ACFWPObjects\Locations\PostTypeSupports' );
		acf_register_location_rule( 'ACFWPObjects\Locations\TaxonomyProp' );
		acf_register_location_rule( 'ACFWPObjects\Locations\WPOptionsPage' );
		acf_register_location_rule( 'ACFWPObjects\Locations\TemplateFileSettings' );
		acf_register_location_rule( 'ACFWPObjects\Locations\Everywhere' );

	}


	/**
	 *	@action acf/include_field_types
	 */
	public function register_field_types() {

		acf_register_field_type( 'ACFWPObjects\Fields\ImageSweetSpot' );
		acf_register_field_type( 'ACFWPObjects\Fields\Includer' );
		acf_register_field_type( 'ACFWPObjects\Fields\SelectImageSize' );
		acf_register_field_type( 'ACFWPObjects\Fields\SelectNavMenu' );
		acf_register_field_type( 'ACFWPObjects\Fields\SelectNavMenuLocation' );
		acf_register_field_type( 'ACFWPObjects\Fields\SelectPostType' );
		acf_register_field_type( 'ACFWPObjects\Fields\SelectRole' );
		acf_register_field_type( 'ACFWPObjects\Fields\SelectTaxonomy' );
		acf_register_field_type( 'ACFWPObjects\Fields\TemplateFileSelect' );
	}

}
