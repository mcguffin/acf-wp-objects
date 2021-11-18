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


class I18N {

	private static $localizations = [];

	/** @var string */
	private $textdomain = '';

	/** @var string */
	private $context = false;

	/** @var callable */
	private $active_callback = null;

	/** @var string field keys to translate */
	private $text_fields = [
		'title',
		'description',
		'label',
		'button_label',
		'instructions',
		'prepend',
		'append',
		'message',
		'placeholder',
		'prefix_label',
		'suffix_label',
	];
	/** @var string field keys to recurse */
	private $sub_fields = [
		'fields',
		'sub_fields',
		'layouts',
	];
	/** @var string choices field keys */
	private $choices_fields = [
		'choices',
	];
	/** @var string field keys to ignore */
	private $ignore_fields = [
		'conditional_logic',
		'wrapper',
		'location',
		'key',
	];

	private $should_translate_cache = [];

	/**
	 *	@param String $textdomain
	 *	@return bool
	 */
	public static function has_localization( $textdomain = null ) {
		if ( is_null( $textdomain ) ) {
			return count( self::$localizations ) > 0;
		}
		return isset( self::$localizations[ $textdomain ] );
	}

	/**
	 *	@param String $textdomain
	 *	@return Boolean|I18N
	 */
	public static function get_localization( $textdomain ) {
		if ( isset( self::$localizations[ $textdomain ] ) ) {
			return self::$localizations[ $textdomain ];
		}
		return false;
	}

	/**
	 *	@return Array
	 */
	public static function get_localizations() {
		return array_keys( self::$localizations );
	}


	/**
	 *	@param String $textdomain
	 *	@param Callable $active_callback
	 *	@param Boolean|String $context
	 *	@return bool
	 */
	public static function register_localization( $textdomain, $active_callback, $context = false ) {
		if ( self::has_localization( $textdomain ) ) {
			return false;
		}
		self::$localizations[ $textdomain ] = new self( $textdomain, $active_callback, $context );
		self::$localizations[ $textdomain ]->init();
		return true;
	}

	/**
	 *	@param String $textdomain
	 *	@return Booelan
	 */
	public static function unregister_localization( $textdomain ) {
		if ( isset( self::$localizations[ $textdomain ] ) ) {
			self::$localizations[ $textdomain ]->deinit();
			unset( self::$localizations[ $textdomain ] );
			return true;
		}
		return false;
	}

	/**
	 *	@param String $textdomain
	 *	@param Callable $active_callback
	 *	@param Boolean|String $context
	 */
	protected function __construct( $textdomain, $active_callback, $context = false ) {

		$this->textdomain = $textdomain;
		$this->context = $context;
		$this->active_callback = $active_callback;

		// localization
//		add_filter( 'acf/load_field', [ $this, 'translate_acf_object' ], 10, 2 );

	}

	/**
	 *	Destructor ... remove
	 */
	protected function init() {
		if ( ACF::instance()->is_fieldgroup_admin() ) {
			return;
		}
		add_filter( 'acf/load_fields', [ $this, 'translate_fields' ], 10, 2 );
		add_filter( 'acf/load_field_group', [ $this, 'translate_field_group' ] );
	}


	/**
	 *	Destructor ... remove
	 */
	protected function deinit() {
		remove_filter( 'acf/load_fields', [ $this, 'translate_fields' ], 10 );
		remove_filter( 'acf/load_field_group', [ $this, 'translate_field_group' ] );
	}

	/**
	 *	Translate ACF Field Group
	 *
	 *	@param Array $field_group
	 *	@return Array
	 *
	 *	@filter acf/load_field_group
	 */
	public function translate_field_group( $field_group ) {

		if ( $this->should_translate( $field_group ) ) {
			return $this->translate_acf_object( $field_group );
		}
		return $field_group;
	}

	/**
	 *	Translate ACF Fields
	 *
	 *	@param Array $field_group
	 *	@return Array
	 *
	 *	@filter acf/load_fields
	 */
	public function translate_fields( $fields, $parent ) {
		// front- & backend

		if ( $this->should_translate( $parent ) ) {
			return $this->translate_acf_object( $fields );
		}
		return $fields;
	}

	/**
	 *	@param array $field_group
	 *	@return boolean
	 */
	private function should_translate( $field_group ) {

		if ( ! isset( $this->should_translate_cache[ $field_group['key'] ] ) ) {
			// is sync
			if ( isset( $_REQUEST['post_status'], $_REQUEST['post_type'] ) && 'sync' === $_REQUEST['post_status'] && 'acf-field-group' === $_REQUEST['post_type'] ) {
				return false;
			}

			// is field group admin
			global $pagenow;
			if (  $pagenow === 'post.php' && 'acf-field-group' === get_post_type() ) {
				return false;
			}

			// given through constructor
			$this->should_translate_cache[ $field_group['key'] ] = call_user_func( $this->active_callback, $field_group );
		}
		return $this->should_translate_cache[ $field_group['key'] ];
	}

	/**
	 *	Recursively Translate ACF Group or Field
	 *
	 *	@param Array $field_group
	 *	@return Array
	 *
	 *	@filter acf/load_fields
	 */
	public function translate_acf_object( $object ) {
		foreach ( $object as $key => $value ) {

			if ( in_array( $key, $this->text_fields, true ) ) {

				if ( ! empty( $value ) && is_scalar( $value ) ) {

					$object[$key] = $this->translate_string( $value );
				}

			} else if ( in_array( $key, $this->sub_fields, true ) ) {

				$object[$key] = $this->translate_acf_object($value);

			} else if ( in_array( $key, $this->choices_fields, true ) ) {

				foreach ( (array) $value as $c => $choice ) {
					if ( ! empty( $choice ) && is_scalar( $choice ) ) {
						// flat
						$object[$key][$c] = $this->translate_string( $choice );
					} else if ( is_array( $choice ) ) {
						// nasty nested optgroup
						$translated_c = $this->translate_string( $c );
						if ( $translated_c !== $c ) {
							unset( $object[$key][$c] );
							$object[$key][ $translated_c ] = array_map( [  $this, 'translate_string' ], $choice );
						}
					}
				}

			} else if ( in_array( $key, $this->ignore_fields, true ) ) {
				continue;

			} else if( is_array( $value ) ) {
				$object[$key] = $this->translate_acf_object($value);
			}

		}
		return $object;
	}
	/**
	 *	@param String $string To translate
	 *	@return String
	 */
	private function translate_string( $string ) {
		if ( false === $this->context ) {
			return __( $string, $this->textdomain ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		}
		return _x( $string, $this->context, $this->textdomain ); // phpcs:ignore WordPress.WP.I18n
	}


}
