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
		FieldOptionID::instance();

		if ( acf_get_setting('pro') ) {
			RepeaterChoices::instance();
		}
		if ( is_admin() ) {
			Form\WPOptions::instance();
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

		if ( apply_filters( 'acf_image_sweetspot_enable', false ) ) {
			ImageSweetSpot::instance();
		}
	}

	/**
	 *	Recursivley search-replace a string in a field
	 *
	 *	@param array $field
	 *	@param string $search
	 *	@param string $replace
	 *
	 *	@return array The field
	 */
	public function replace_field_key( $field, $search, $replace ) {
		if ( is_array( $field ) ) {
			foreach ( $field as $k => $v ) {
				$field[$k] = $this->replace_field_key( $v, $search, $replace );
			}
		} else if ( $field === $search ) {
			return $replace;
		}

		return $field;
	}

	/**
	 *	@param Array $fields
	 */
	public function recreate_field_keys( $fields = [] ) {
		$replace_keys = [];
		$this->prepare_key_replace( $fields, null, $replace_keys );

		$replace_keys = array_map( function($key) {
			return 'field_'.$key;
		}, $replace_keys );


		foreach ( $replace_keys as $search => $replace ) {
			$fields = $this->replace_field_key( $fields, $search, $replace );
		}
		return $fields;
	}

	/**
	 *	Recurse fields
	 */
	private function prepare_key_replace( &$fields, $parent_field, &$keys ) {
		foreach ( $fields as &$field ) {
			$field['_tmp_key'] = '';
			if ( isset( $field['name'] ) && ! empty( $field['name'] ) ) {
				$field_name = $field['name'];
			} else if ( isset( $field['key'] ) && ! empty( $field['key'] ) ) {
				$field_name = str_replace( 'field_', '', $field['key'] );
			} else {
				continue;
			}
			if ( ! is_null( $parent_field ) ) {
				$field['_tmp_key'] .= $parent_field['_tmp_key'] . '_';
			}
			$field['_tmp_key'] .= $field_name;
			$keys[ $field['key'] ] = $field['_tmp_key'];

			if ( isset( $field['sub_fields'] ) ) {
				$this->prepare_key_replace( $field['sub_fields'], $field, $keys );
			}
			if ( isset( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as &$layout ) {
					$this->prepare_key_replace( $layout['sub_fields'], [
						'_tmp_key' => $field['_tmp_key'] . '_' . $layout['name'],
					], $keys );
				}
			}
		}
	}

	/**
	 *	Whether we are in the fieldgroup admin
	 *
	 *	@return boolean
	 */
	public function is_fieldgroup_admin() {

		// local json compare ajax request
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && wp_unslash($_REQUEST['action']) === 'acf/ajax/local_json_diff' ) {
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
		$wp = Core\WP::instance();

		$this->acf_field_group_js
			->footer( false )
			->deps( 'acf-field-group', $this->acf_input_js )
			->localize( [
				'repeated_fields' => $choices->get_repeated_fields(),
				'post_types'	=> array_map( [ $this, 'reduce_pt' ], $wp->get_post_types() ),
				'taxonomies'	=> array_map( [ $this, 'reduce_taxo' ], $wp->get_taxonomies() ),
				'image_sizes'	=> array_map( [ $this, 'mk_image_sizes' ], $wp->get_image_sizes() ),
			], 'acf_wp_objects' )
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

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\PostTypeProp' );

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\TaxonomyProp' );

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\WPOptionsPage' );

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\TemplateFileSettings' );

		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\Everywhere' );

	}


	/**
	 *	@action acf/include_field_types
	 */
	public function register_field_types() {

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\ImageSweetSpot' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\Includer' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectPostType' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectTaxonomy' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectImageSize' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\SelectRole' );

		acf_register_field_type( '\ACFWPObjects\Compat\ACF\Fields\TemplateFileSelect' );
	}

}
