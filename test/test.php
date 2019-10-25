<?php

namespace ACFWPObjects;

class PluginTest {

	private $current_json_save_path = null;

	public function __construct() {
		add_filter( 'acf/settings/load_json', [ $this, 'load_json' ] );

		add_filter( 'acf/settings/save_json', [ $this, 'save_json' ] );

		add_action( 'acf/delete_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/trash_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/untrash_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/update_field_group', [ $this, 'mutate_field_group' ], 9 );

		add_action('acf/init', [ $this,'add_options_page' ] );

	}

	/**
	 *	@action acf/init
	 */
	public function add_options_page() {
		$nwp = acf_add_options_page([
			'page_title'	=> 'ACF WP-Objects Network Options',
			'menu_title'	=> 'WP-Objects',
			'post_id'		=> 'network_opt_test',
			'menu_slug'		=> 'wpo-test-network-options',
			'position'		=> 50,
			'redirect'		=> false,
			'network'		=> true,
		]);
		acf_add_options_sub_page([
			'page_title'	=> 'Network Options Sub #1',
			'menu_title'	=> 'Sub #1',
			'post_id'		=> 'network_opt_test',
			'parent'		=> $nwp['menu_slug'],
			'network'		=> true,
		]);
		acf_add_options_sub_page([
			'page_title'	=> 'Network Options Sub #2',
			'menu_title'	=> 'Sub #2',
			'post_id'		=> 'acf_wpo_opt_test',
			'parent'		=> $nwp['menu_slug'],
			'network'		=> true,
		]);
		$blp = acf_add_options_page([
			'page_title'	=> 'ACF WP-Objects Options',
			'menu_title'	=> 'WP-Objects',
			'post_id'		=> 'acf_wpo_opt_test',
			'menu_slug'		=> 'wpo-test-options',
			'redirect'		=> false,
		]);
	}


	/**
	 *	@filter 'acf/settings/save_json'
	 */
	public function load_json( $paths ) {
		$paths[] = dirname(__FILE__).'/acf-json';
		return $paths;
	}

	/**
	 *	@filter 'acf/settings/save_json'
	 */
	public function save_json( $path ) {
		if ( ! is_null( $this->current_json_save_path ) ) {
			return $this->current_json_save_path;
		}
		return $path;
	}

	/**
	 *	Figure out where to save ACF JSON
	 *
	 *	@action 'acf/update_field_group'
	 */
	public function mutate_field_group( $field_group ) {
		// default

		if ( strpos( $field_group['key'], 'group_acf_wp_objects_' ) === false ) {
			$this->current_json_save_path = null;
			return;
		}
		$this->current_json_save_path = dirname(__FILE__).'/acf-json';

	}
}

new PluginTest();
