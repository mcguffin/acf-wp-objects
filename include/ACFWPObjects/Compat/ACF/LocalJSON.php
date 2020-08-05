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


class LocalJSON {
	/** @var array */
	private static $paths = [];

	/** @var string */
	private $current_json_save_path = null;

	/** @var string */
	private $json_path = ''; // acf-json elsewhere ...

	/** @var string */
	private $search_paths = [];

	/** @var callable */
	private $active_callback = ''; // acf-json elsewhere ...


	/**
	 *	@param string $path
	 *	@param callable
	 *	@return bool
	 */
	public static function register_path( $path, $active_callback, $search_paths = [] ) {
		if ( isset( self::$paths[ $path ] ) ) {
			return false;
		}
		$search_paths = array_unique( $search_paths );
		$search_paths = array_filter( $search_paths, function( $search_path ) use ( $path ) {
			return is_dir( trailingslashit( $search_path ) . $path );
		} );
		//  paths don't exist
		if ( ! count( $search_paths ) ) {
			return false;
		}
		self::$paths[ $path ] = new self( $path, $active_callback, $search_paths );
		self::$paths[ $path ]->init();
		return true;
	}

	/**
	 *	@param string $path
	 *	@return bool
	 */
	public static function unregister_path( $path ) {
		if ( isset( self::$paths[ $path ] ) ) {
			self::$paths[ $path ]->deinit();
			unset( self::$paths[ $path ] );
			return true;
		}
		return false;
	}

	/**
	 *	@param string $json_path
	 *	@param string $active_callback
	 */
	protected function __construct( $json_path, $active_callback, $search_paths = [] ) {

		$this->json_path = $json_path;
		$this->active_callback = $active_callback;
		$this->search_paths = array_map( 'untrailingslashit', $search_paths );

	}

	/**
	 *	@inheritdoc
	 */
	protected function init() {

		// local json paths
		add_filter( 'acf/settings/load_json', [ $this, 'load_json' ] );
		add_filter( 'acf/settings/save_json', [ $this, 'save_json' ] );

		// handle json files
		add_action( 'acf/delete_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/trash_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/untrash_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/update_field_group', [ $this, 'mutate_field_group' ], 9 );

	}

	/**
	 *	@inheritdoc
	 */
	protected function deinit() {

		// local json paths
		remove_filter( 'acf/settings/load_json', [ $this, 'load_json' ] );
		remove_filter( 'acf/settings/save_json', [ $this, 'save_json' ] );

		// handle json files
		remove_action( 'acf/delete_field_group', [ $this, 'mutate_field_group' ], 9 );
		remove_action( 'acf/trash_field_group', [ $this, 'mutate_field_group' ], 9 );
		remove_action( 'acf/untrash_field_group', [ $this, 'mutate_field_group' ], 9 );
		remove_action( 'acf/update_field_group', [ $this, 'mutate_field_group' ], 9 );

	}

	/**
	 *	@filter 'acf/settings/load_json'
	 */
	public function load_json( $paths ) {

		$core = Core\Core::instance();

		$add_paths = array_map( [ $this, 'add_json_path' ], $this->search_paths );

		return array_merge( $paths, $add_paths );

	}

	/**
	 *	array_map callback
	 *	append replative json path
	 *	@param string $path
	 *	@return string
	 */
	private function add_json_path( $path ) {
		return trailingslashit( $path ) . $this->json_path;
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
		if ( call_user_func( $this->active_callback, $field_group ) === false ) {
			$this->current_json_save_path = null;
			return;
		}

		foreach ( $this->search_paths as $path ) {

			$this->current_json_save_path = "{$path}/{$this->json_path}";

			$file = trailingslashit( $path ) . trailingslashit( $this->json_path ) . $field_group['key'] .'.json';

			if ( file_exists( $file ) ) {
				return;
			}
		}

//		$this->current_json_save_path = null; // couldnt find a place to save
	}

}
