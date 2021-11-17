<?php
/**
 *	@package PluginTemplates\Core
 *	@version 1.0.1
 *	2018-09-22
 */

namespace ACFWPObjects\Core;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

class Template extends Singleton {

	/** @var array */
	private $templates = [];

	/**
	*	@inheritdoc
	*/
	protected function __construct() {

		add_action( 'get_template_part', [ $this, 'get_template_part'] , 20, 3 );

	}


	/**
	 *	@return null|array
	 */
	public function get_template_type( $type = '' ) {
		$types = $this->get_template_types();
		if ( isset( $types[ $type ] ) ) {
			return $types[ $type ];
		}
		return null;
	}

	/**
	 *	@return array
	 */
	public function get_template_types() {

		$types = apply_filters('acf_wp_objects_template_types', [
		/*	'' => [
				'header_key' => 'Template Name',
				'theme_location' => '', // recursive
				'plugin_location' => false,
			], */
		], $this );

		foreach ( $types as $slug => &$type ) {
			if ( is_string( $type ) ) {
				$type = [ 'header_key' => $type ];
			}
			$type = wp_parse_args( $type, [
				'theme_location'	=> $slug,
				'plugin_location'	=> false,
			] );
			if ( is_null( $type['plugin_location'] ) ) {
				$type['plugin_location'] = 'templates';
			}
		}

		return $types;
	}


	/**
	 *	@param string $header_key Scan for templates having this header key
	 *	@return [
	 *		file => [
	 *			label		=> string Label
	 *			path		=> string path to file
	 *			file		=> string path in theme / plugin
	 *			settings	=> string acf settings field group key
	 *		],
	 *		...
	 *	]
	 */
	public function get_templates( $header_key ) {

		if ( ! isset( $this->templates[ $header_key ] ) ) {

			$this->scan_templates();

		}

		if ( isset( $this->templates[ $header_key ] ) ) {

			return $this->templates[ $header_key ];

		}

		return [];
	}

	/**
	 *	@return array see get_template_by
	 */
	private function scan_templates() {

		foreach ( $this->get_template_types() as $slug => $type ) {

			$header_key = $type['header_key'];

			if ( ! isset( $this->templates[ $header_key ] ) ) {
				$this->templates[ $header_key ] = [];
			}

			// get scan paths
			$paths_to_scan = [ trailingslashit( get_stylesheet_directory() ) . $type['theme_location'] ];

			if ( is_child_theme() ) {
				$paths_to_scan[] = trailingslashit( get_template_directory() ) . $type['theme_location'];
			}
			if ( false !== $type['plugin_location'] ) {
				$paths_to_scan[] = trailingslashit( WP_PLUGIN_DIR ) . trailingslashit( $slug ) . $type['plugin_location'];
			}

			foreach ( $paths_to_scan as $scan_path ) {

				$scan_path = trailingslashit( $scan_path );

//				$plugin_files = glob( $scan_path . '{*/*/*/,*/*/,*/,}*.php', GLOB_BRACE );
				$plugin_files = glob( $scan_path . '*.php', GLOB_BRACE );

				// plugin templates
				foreach ( $plugin_files as $full_path ) {

					$data = get_file_data( $full_path, [ $header_key => $header_key, "$header_key Settings" => "$header_key Settings" ] );

					if ( empty( $data[ $header_key ] ) ) {
						continue;
					}

					$theme_location = ! empty( $type['theme_location'] )
						? trailingslashit( $type['theme_location'] )
						: '';

					$file = str_replace( $scan_path, $theme_location, $full_path );

					$name = preg_replace( '/\.php$/', '', $file );

					$this->templates[ $header_key ][ $name ] = [
						'label'		=> $data[ $header_key ],
						'path'		=> $full_path,
						'file'		=> $file, // some-plugin/some-file.php
						'name'		=> $name, // some-plugin/some-file
						'slug'		=> str_replace( '-', '_', sanitize_title( $name )),
						'settings'	=> empty( $data[ "$header_key Settings" ] ) ? false : json_decode( $data[ "$header_key Settings" ], true ),
					];
				}
			}
		}
	}

	/**
	 *	@action get_template_part
	 */
	public function get_template_part( $slug, $name, $templates ) {

		$located = locate_template( $templates, false, false );

		if ( $located === '' ) {


			foreach ( $this->get_template_types() as $type_slug => $type ) {

				if ( false === $type['plugin_location'] ) {
					continue;
				}

				if ( false === strpos( $slug, $type['theme_location'] ) ) {
					continue;
				}

				$slug = str_replace( $type['theme_location'] . '/', '', $slug );

				$plugin_template_path = trailingslashit( WP_PLUGIN_DIR ) . trailingslashit( $type_slug ) . $type['plugin_location'];

				$templates = [];

				if ( $name ) {
					$templates[] = $plugin_template_path . "/{$slug}-{$name}.php";
				}

				$templates[] = $plugin_template_path . "/{$slug}.php";

				foreach ( $templates as $file ) {
					if ( file_exists( $file ) ) {
						load_template( $file, false );
						return;
					}
				}
			}
		}
	}
}
