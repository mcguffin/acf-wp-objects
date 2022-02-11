<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF\Helper;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

use ACFWPObjects\Core;

class ImportExportOptionsPage extends Core\Singleton {

	/** @var Array referenced data */
	private $export_references = null;

	/** @var Array mapping reference => ID  */
	private $resolve_references = null;

	/** @var Array  mapping post_id:fieldname => reference */
	private $field_export_references = null;


	/**
	 *	@param String|Array $data
	 *	@return Boolean Success
	 */
	public function import( $data ) {

		$data = $this->sanitize_import_data( $data );

		if ( is_array( $data['references'] ) ) {
			$this->init_reference_import( $data['references'] );
		}
		acf_update_values( $data['values'], $data['page']['post_id'] );
		return true;
	}

	/**
	 *	@param String|Array $page Options page slug or config
	 *	@param Boolean $reference Whether reference values like post objects or images should be exported too. Implies format_values
	 *	@return Array
	 */
	public function export( $page, $references = false ) {

		if ( is_string( $page ) ) {
			$page = acf_get_options_page( $page );
		}

		$page['reset'] = $page['reset'] !== false; // convert to boolean. Will disclose directory structure otherwise

		if ( $references ) {
			$this->init_reference_export();
		}

		$fields = $this->get_fields( $page );
		$values = [];
		foreach ( $fields as $field ) {
			$value = get_field( $field['name'], $page['post_id'], true );
			if ( ! is_null( $value ) ) {
				$values += [ $field['name'] => $value ];
			}
		}
		$export_data = [
			'page' => $page,
			'values' => $values,
			'references' => $this->export_references,
		];
		return $export_data;
	}


	/**
	 *	Reset an options page. Maybe load default values
	 *	@param Array $page Options page config
	 */
	public function reset( $page ) {

		$fields = $this->get_fields( $page );

		foreach ( $fields as $field ) {
			acf_delete_value( $page['post_id'], $field );
		}
	}

	/**
	 *	Create content, build reference resultion mapping.
	 *	Add necessary filters to acf/format_value
	 */
	private function init_reference_import( $references ) {

		$this->resolve_references = [];

		foreach ( $references as $key => $reference ) {

			if ( strpos( $key, 'attachment:' ) === 0 ) {
				// create attachment
				$attachment_id = $this->import_attachment( $reference['file'] );
				$this->resolve_references[$key] = $attachment_id;
				// $reference['file']['name'];
			} else if ( strpos( $key, 'post:' ) === 0 ) {
				// create post
			} else if ( strpos( $key, 'term:' ) === 0 ) {
				// create term
			}
		}

		add_filter('acf/update_value/type=file', [ $this, 'resolve_reference' ], 9, 3 );
		add_filter('acf/update_value/type=image', [ $this, 'resolve_reference' ], 9, 3 );
		//add_filter('acf/format_value/type=gallery', [ $this, 'resolve_reference' ], 9, 3 );
		add_filter('acf/update_value/type=post_object', [ $this, 'resolve_reference' ], 9, 3 );
		add_filter('acf/update_value/type=relationship', [ $this, 'resolve_reference' ], 9, 3 );
		add_filter('acf/update_value/type=taxonomy', [ $this, 'resolve_reference' ], 9, 3 );
		add_filter('acf/update_value/type=nav_menu_select', [ $this, 'resolve_reference' ], 9, 3 );

	}

	/**
	 *	Resolve references
	 *
	 *	@filter acf/update_value/type=*
	 */
	public function resolve_reference( $value, $post_id, $field ) {
		if ( isset( $this->resolve_references[ $value ] ) ) {
			return $this->resolve_references[ $value ];
		}
		return $value;
	}


	/**
	 *	@param Array $attachment_data [ 'name' => 'xxx.jpg', 'hash', <md5 fingerprint of file contents> => <base64 encoded file contents> ]
	 *	@return Integer Attachment ID
	 */
	private function import_attachment( $attachment_data ) {
		global $wpdb;
		// look if attachment already exists
		$attachment_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value LIKE %s",
			'_wp_attached_file',
			intval( get_option( 'uploads_use_yearmonth_folders' ) )
				? '%' . $wpdb->esc_like( '/' . basename( $attachment_data['name'] ) )
				: $wpdb->esc_like( basename( $attachment_data['name'] ) )
		));
		foreach ( $attachment_ids as $attachment_id ) {
			$file = get_attached_file( $attachment_id );
			if ( md5_file( $file ) === $attachment_data['hash'] ) {
				return $attachment_id;
			}
		}

		$filetype = wp_check_filetype( $attachment_data['name'] );

		if ( ! $filetype['type'] ) {
			return false;
		}

		$contents = base64_decode( $attachment_data['contents'] );

		if ( empty( $contents ) ) {
			return false;
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'acf_wpo_' );
		file_put_contents( $temp_file, $contents );

		// create new attachment
		if ( isset( $_FILES ) ) {
			$prev_files = $_FILES;
		}
		// Override superglobal. Don't try this at home.
		$_FILES = [
			'acf_wpo_tmp' => [
				'name'	=> $attachment_data['name'],
				'type'	=> $filetype['type'],
				'tmp_name'	=> $temp_file,
				'size'	=> filesize( $temp_file ),
				// 'error'	=> UPLOAD_ERR_OK,
			],
		];

		$attachment_id = media_handle_upload( 'acf_wpo_tmp', 0, [], [ 'test_form' => false, 'action' => 'acf_wpo_import' ] );

		// clean up our mess
		if ( isset( $prev_files ) ) {
			 $_FILES = $prev_files;
		}
		return $attachment_id;
	}

	/**
	 *	Add necessary filters to acf/format_value
	 */
	private function init_reference_export() {

		$this->export_references = [];

		$this->field_export_references = [];

		add_filter('acf/format_value/type=file', [ $this, 'reference_file' ], 9, 3 );
		add_filter('acf/format_value/type=image', [ $this, 'reference_file' ], 9, 3 );
		add_filter('acf/format_value/type=post_object', [ $this, 'reference_posts' ], 9, 3 );
		add_filter('acf/format_value/type=relationship', [ $this, 'reference_posts' ], 9, 3 );
		add_filter('acf/format_value/type=taxonomy', [ $this, 'reference_terms' ], 9, 3 );
		add_filter('acf/format_value/type=nav_menu_select', [ $this, 'reference_terms' ], 9, 3 );

		add_filter('acf/format_value/type=file', [ $this, 'get_reference' ], 11, 3 );
		add_filter('acf/format_value/type=image', [ $this, 'get_reference' ], 11, 3 );
		add_filter('acf/format_value/type=post_object', [ $this, 'get_reference' ], 11, 3 );
		add_filter('acf/format_value/type=relationship', [ $this, 'get_reference' ], 11, 3 );
		add_filter('acf/format_value/type=taxonomy', [ $this, 'get_reference' ], 11, 3 );
		add_filter('acf/format_value/type=nav_menu_select', [ $this, 'get_reference' ], 11, 3 );

		add_filter('acf/format_value/type=user', '__return_empty_string', 11, 3 ); // we do not export users
		add_filter('acf/format_value/type=gallery', '__return_empty_array', 11, 3 ); // too many files
	}

	/**
	 *	@filter acf/format_value/type=file|image
	 */
	public function reference_file( $value, $post_id, $field ) {
		$reference = "attachment:{$value}";
		if ( isset( $this->export_references[ $reference ] ) ) {
			$this->field_export_references[ $post_id.':'.$field['name'] ] = $reference;
			return $value;
		}
		if ( $attachment = get_post( $value ) ) {
			if ( ( $file = get_attached_file( $attachment->ID ) ) && file_exists( $file ) ) {
				$contents = file_get_contents( $file );
				$this->export_references[ $reference ] = [
					'type' => $field['type'],
					'file' => [
						'name' => basename( $file ),
						'hash' => md5( $contents ),
						'contents' => base64_encode( $contents ),
					],
				];
				// Files in repeaters?
				$this->field_export_references[ $post_id.':'.$field['name'] ] = $reference;
			}
		}
		return $value;
	}

	/**
	 *	@filter acf/format_value/type=post_object|relationship
	 */
	public function reference_posts( $value, $post_id, $field ) {
		$refs = array_map( function( $post_id ) {
			$reference = "post:$post_id";
			if ( ! isset( $this->export_references[ $reference ] ) ) {

				$post = get_post( $post_id );

				$post_data = get_object_vars( $post );
				$post_meta = get_post_meta( $post->ID );

				// cleanup post data
				foreach ( [ 'ID','post_author','to_ping','pinged','guid','comment_count','filter', ] as $prop ) {
					if ( isset( $post_data[$prop] ) ) {
						 unset( $post_data[$prop] );
					}
				}

				// cleanup post meta
				foreach ( [ '_edit_lock', '_edit_last', '_wp_old_date', '_wp_old_slug', ] as $prop ) {
					if ( isset( $post_meta[$prop] ) ) {
						 unset( $post_meta[$prop] );
					}
				}

				$this->export_references[ $reference ] = [
					'post' => $post_data,
					'meta' => $post_meta,
				];
			}
			return $reference;
		}, array_filter( (array) $value ) );

		if ( ! is_array( $value ) ) {
			$this->field_export_references[ $post_id.':'.$field['name'] ] = $refs[0];
		} else {
			$this->field_export_references[ $post_id.':'.$field['name'] ] = $refs;
		}
		return $value;
	}

	/**
	 *	@filter acf/format_value/type=txonomy
	 */
	public function reference_terms( $value, $post_id, $field ) {
		$refs = array_map( function( $term_id ) {
			$reference = "term:$term_id";
			if ( ! isset( $this->export_references[ $reference ] ) ) {
				$term = get_term( absint( $term_id ) );

				$term_data = get_object_vars( $term );
				$term_meta = get_term_meta( $term->term_id );

				foreach ( [ 'term_id', 'term_taxonomy_id', 'parent', 'count', 'filter', ] as $prop ) {
					if ( isset( $term_data[ $prop ] ) ) {
						unset( $term_data[ $prop ] );
					}
				}

				$this->export_references[ $reference ] = [
					'post' => $term_data
					'meta' => $term_meta,
				];
			}
			return $reference;
		}, array_filter( (array) $value ) );

		if ( ! is_array( $value ) ) {
			$this->field_export_references[ $post_id.':'.$field['name'] ] = $refs[0];
		} else {
			$this->field_export_references[ $post_id.':'.$field['name'] ] = $refs;
		}
		return $value;
	}

	/**
	 *	Replace ACF value with reference
	 *
	 *	@filter acf/format_value/type=*
	 */
	public function get_reference( $value, $post_id, $field ) {
		if ( isset( $this->field_export_references[ $post_id.':'.$field['name'] ] ) ) {
			return $this->field_export_references[ $post_id.':'.$field['name'] ];
		}
		return $value;
	}

	/**
	 *	@param String|Array $data
	 *	@return Array|Boolean json decoded $data or false on failure
	 */
	private function sanitize_import_data( $data ) {
		// maybe decode json
		if ( is_string( $data ) ) {
			$data = json_decode( $data, true );
		}

		if ( ! is_array( $data ) ) {
			return false;
		}

		if ( ! isset( $data['values'] ) || ! isset( $data['page'] ) || ! isset( $data['page']['post_id'] ) ) {
			return false;
		}
		return wp_parse_args( $data, [
			'references' => false,
		]);
	}

	/**
	 *	@param Array $page
	 *	@return Array
	 */
	private function get_fields( $page ) {

		$fields = [];

		$field_groups = acf_get_field_groups( [ 'options_page' => $page['menu_slug'] ] );

		foreach ( $field_groups as $i => $field_group ) {

			$fields = array_merge( $fields, acf_get_fields( $field_group ) );

		}

		return $fields;
	}

}
