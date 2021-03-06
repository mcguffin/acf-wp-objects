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

class LocalJSON {
	/** @var array */
	private static $paths = [];

	/** @var string */
	private $current_json_save_path = null; // ACF < 5.9

	/** @var string */
	private $json_path = ''; // acf-json elsewhere ...

	/** @var string */
	private $search_paths = [];

	/** @var callable */
	private $active_callback = ''; // acf-json elsewhere ... ACF < 5.9


	/**
	 *	@param String $path Path within $search paths
	 *	@param Callable $active_callback Whether a field group should be saved DEPRECATED AS OF ACF 5.9
	 *	@param Array $search_paths Where to search for Fields. Typically a plugin, theme and/or child theme path
	 *	@return Boolean|String ID of local JSON
	 */
	public static function register_path( $path, $active_callback, $search_paths ) {
		if ( isset( self::$paths[ $path ] ) ) {
			return false;
		}
		// save json needs a verification callback
		if ( ! is_callable( $active_callback ) && version_compare( acf()->version, '5.9.0', '<' ) ) {
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
		$key = implode( ';', $search_paths ) . ';' . $path;
		self::$paths[ $key ] = new self( $path, $active_callback, (array) $search_paths );
		self::$paths[ $key ]->init();
		return $key;
	}

	/**
	 *	@param string $path
	 *	@return bool
	 */
	public static function unregister_path( $path, $search_paths ) {
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
	protected function __construct( $json_path, $active_callback, $search_paths ) {

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

		// remove default
		$paths = array_filter( $paths, [ $this, 'is_not_default_json_path' ] );
		$add_paths = array_map( [ $this, 'add_json_path' ], $this->search_paths );

		return array_merge( $paths, $add_paths );

	}

	/**
	 *	array_map callback
	 *	append relative json path
	 *
	 *	@param string $path
	 *	@return string
	 */
	private function add_json_path( $path ) {
		return trailingslashit( $path ) . $this->json_path;
	}

	/**
	 *	array_filter callback
	 *	Whether a path is a custom json path (not ending with 'acf-json')
	 *
	 *	@return boolean
	 */
	private function is_not_default_json_path( $path ) {
		foreach ( $this->search_paths as $search_path ) {
			if ( $path === trailingslashit( $search_path ) . 'acf-json' ) {
				return false;
			}
		}
		return true;
	}

	/**
	 *	ACF < 5.9
	 *
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
	 *	ACF < 5.9
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
