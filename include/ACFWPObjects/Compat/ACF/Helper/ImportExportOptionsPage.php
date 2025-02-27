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
	private $reference_cache = [];

	/** @var Array referenced data */
	private $export_references = null;

	/** @var Array mapping reference => ID  */
	private $resolve_references = [];

	/** @var Array  mapping post_id:fieldname => reference */
	private $field_export_references = null;


	/**
	 *	@param String|Array $data
	 *	@return Boolean Success
	 */
	public function import( $data ) {

		if ( ! ( $data = $this->sanitize_import_data( $data ) ) ) {
			return false;
		}

		if ( is_array( $data['references'] ) ) {
			$this->init_reference_import( $data['references'] );
		}
		if ( is_array( $data['page']['export'] ) ) {
			foreach ( $data['page']['export'] as $page ) {
				if ( isset( $data['values'][$page] ) ) {
					$this->_import( $data['values'][$page], $page );
				}
			}
		} else {
			$this->_import( $data['values'], $data['page']['menu_slug'] );
		}

		return true;
	}

	/**
	 *	@param String|Array $data
	 *	@param String|Array $page
	 *	@return Boolean Success
	 */
	private function _import( $values, $page ) {
		if ( is_string( $page ) ) {
			$page = acf_get_options_page( $page );
		}
		if ( ! $page ) {
			return;
		}
		acf_update_values( $values, $page['post_id'] );
	}

	/**
	 *	@param String|Array $page Options page slug or config
	 *	@param Boolean $reference Whether reference values like post objects or images should be exported too. Implies format_values
	 *	@return Array
	 */
	public function export( $page, $references = false ) {

		if ( $references ) {
			$this->init_reference_export();
		} else {
			$this->init_reference_reset();
		}

		if ( is_array( $page['export'] ) ) {
			// multiple pages
			if ( is_string( $page ) ) {
				$page = acf_get_options_page( $page );
			}
			$export_data = [
				'page' =>  $this->get_export_page_data( $page ),
				'values' => [],
				'references' => [],
			];
			foreach ( $page['export'] as $page_slug ) {
				$exported = $this->_export( $page_slug, $references );
				$export_data['values'][$page_slug] = $exported['values'];
				if ( $references ) {
					$export_data['references'] += $exported['references'];
					$this->reference_cache = $export_data['references'];
				}
			}
		} else {
			// single page
			$export_data = $this->_export( $page, $references );
		}

		$export_data = $this->filter_export_data( $export_data );
		/**
		 *	Filter export data
		 *	@param Array $export_data [
		 *		'page' => [
		 *			'page_title' => String,
		 *			'menu_slug'  => String,
		 *			'post_id'    => String,
		 *			'export'     => Boolean|Array,
		 *		],
		 *		'values' => [
		 *			...
		 *		],
		 *		'references' => [
		 *			...
		 *		],
		 *	]
		 *	@param Boolean $references
		 */
		return apply_filters( 'acf_options_page_export_data', $export_data, $references );
	}

	/**
	 *	Remove empty keys from export data
	 *
	 *	@param Array $export_data
	 *	@return Array
	 */
	private function filter_export_data( $export_data ) {
		$export_data = array_map( function( $value ) {
			if ( is_array( $value ) ) {
				$value = $this->filter_export_data( $value );
			}
			return $value;
		}, $export_data );

		return array_filter( $export_data, function($key) {
			return '' !== $key;
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 *	@param String|Array $page Options page slug or config
	 *	@param Boolean $reference Whether reference values like post objects or images should be exported too. Implies format_values
	 *	@return Array
	 */
	private function _export( $page, $references = false ) {

		// resolve page slug
		if ( is_string( $page ) ) {
			$page = acf_get_options_page( $page );
		}

		$fields = $this->get_fields( $page );

		$values = [];

		acf_get_store( 'values' )->reset();

		add_filter( 'acf/format_value', [ $this, 'get_raw_value'], 99, 3 );

		foreach ( $fields as $field ) {
			if ( empty( $field['name'] ) ) {
				continue;
			}
			$value = get_field( $field['name'], $page['post_id'], true );
			if ( ! is_null( $value ) ) {
				$values += [ $field['name'] => $value ];
			}
		}
		remove_filter( 'acf/format_value', [ $this, 'get_raw_value'], 99 );

		return [
			'page' => $this->get_export_page_data( $page ),
			'values' => $values,
			'references' => $this->export_references,
		];
	}

	/**
	 *	@filter acf/format_value
	 */
	public function get_raw_value( $value, $post_id, $field ) {
		if ( 'true_false' === $field['type'] ) {
			return (bool) $value
				? '1'
				: '0';
		} else if ( in_array( $field['type'], [ 'group', 'repeater', 'flexible_content', 'number', 'range' ] ) ) {
			return $value;
		}

		return get_field( $field['name'], $post_id, false );
	}

	/**
	 *	reduce ACF Options page to exportable
	 *
	 *	@param Array $page ACF Options page config
	 *	@return Array
	 */
	private function get_export_page_data( $page ) {
		return array_intersect_key(
			$page,
			array_flip( [ 'page_title', 'menu_slug', 'post_id', 'export' ] )
		);
	}

	/**
	 *	Reset an options page. Maybe load default values
	 *	@param Array $page Options page config
	 */
	public function reset( $page ) {

		// resolve page slug
		if ( is_string( $page ) ) {
			$page = acf_get_options_page( $page );
		}

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

		// import all references
		foreach ( $references as $key => $reference ) {
			if ( strpos( $key, 'attachment:' ) === 0 ) {
				// create attachment
				$attachment_id = $this->import_attachment( $reference['file'] );
				$this->resolve_references[$key] = $attachment_id;
				// $reference['file']['name'];
			} else if ( strpos( $key, 'post:' ) === 0 ) {
				// create post
				$post_id = $this->import_post( $reference );
				$this->resolve_references[$key] = $post_id;
			} else if ( strpos( $key, 'term:' ) === 0 ) {
				// create term
				$term_id = $this->import_term( $reference );
				$this->resolve_references[$key] = $term_id;
			}
		}

		add_filter('acf/update_value/type=file', [ $this, 'resolve_reference' ], 9, 3 );
		add_filter('acf/update_value/type=image', [ $this, 'resolve_reference' ], 9, 3 );
		add_filter('acf/update_value/type=gallery', [ $this, 'resolve_reference' ], 9, 3 );
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

		if ( empty( $value ) ) {
			return $value;
		}

		$refs = array_map( function( $value ) {

			if ( is_scalar( $value ) && isset( $this->resolve_references[ $value ] ) ) {
				return $this->resolve_references[ $value ];
			}
			return $value;
		}, array_filter( (array) $value ) );

		if ( is_array( $value ) ) {
			return $refs;
		} else if ( count($refs) ) {
			return $refs[0];
		}

		return $value;

	}


	/**
	 *	@param Array $attachment_data [ 'name' => 'xxx.jpg', 'hash' => <md5 fingerprint of file contents>, 'contents' => <base64 encoded file contents> ]
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
	 *	@param Array $post_data [ 'post' => [ ... ], 'meta' => [ ... ] ]
	 *	@return Integer Post ID
	 */
	private function import_post( $post_data ) {
		global $wpdb;
		// see if posts already exists
		$post_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s;",
			$post_data['post']['post_name'],
			$post_data['post']['post_type']
		) );
		// post found.
		foreach ( $post_ids as $post_id ) {
			return $post_id;
		}

		$post_id = wp_insert_post( $post_data['post'] );
		// didnt work ...
		if ( 0 === $post_id ) {
			return null;
		}
		// add meta
		foreach ( $post_data['meta'] as $meta_key => $meta_values ) {
			foreach ( $meta_values as $meta_value ) {
				add_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
		return $post_id;
	}

	/**
	 *	@param Array $term_data [ 'post' => [ ... ], 'meta' => [ ... ] ]
	 *	@return Integer Term ID
	 */
	private function import_term( $term_data ) {
		global $wpdb;
		// see if posts already exists
		$term_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT
				t.term_id
			FROM $wpdb->terms AS t
			LEFT JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
			WHERE t.slug = %s AND tt.taxonomy = %s;",
			$term_data['term']['slug'],
			$term_data['term']['taxonomy']
		) );
		// post found.
		foreach ( $term_ids as $term_id ) {
			return $term_id;
		}

		// insert new term
		$inserted_term = wp_insert_term( $term_data['term']['name'], $term_data['term']['taxonomy'], [
			'description' => $term_data['term']['description'],
			'slug' => $term_data['term']['slug'],
		] );
		// idn't work
		if ( is_wp_error( $inserted_term ) ) {
			return null;
		}

		// add meta
		foreach ( $term_data['meta'] as $meta_key => $meta_values ) {
			foreach ( $meta_values as $meta_value ) {
				add_term_meta( $inserted_term['term_id'], $meta_key, $meta_value );
			}
		}

		return $inserted_term['term_id'];

	}

	/**
	 *	Export: Add necessary filters to acf/format_value
	 */
	private function init_reference_export() {

		$this->export_references = $this->reference_cache;

		$this->field_export_references = [];

		add_filter('acf/format_value/type=file', [ $this, 'reference_file' ], 9, 3 );
		add_filter('acf/format_value/type=image', [ $this, 'reference_file' ], 9, 3 );
		add_filter('acf/format_value/type=gallery', [ $this, 'reference_file' ], 9, 3 );
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
		add_filter('acf/format_value/type=gallery', [ $this, 'get_reference' ], 11, 3 );

		add_filter('acf/format_value/type=user', '__return_empty_string', 11, 3 ); // we do not export users
	}

	/**
	 *	Export: Add necessary filters to acf/format_value
	 */
	private function init_reference_reset() {

		$this->export_references = $this->reference_cache;

		$this->field_export_references = [];

		add_filter('acf/format_value/type=file', '__return_empty_string' );
		add_filter('acf/format_value/type=image', '__return_empty_string' );
		add_filter('acf/format_value/type=post_object', '__return_empty_string' );
		add_filter('acf/format_value/type=relationship', '__return_empty_string' );
		add_filter('acf/format_value/type=taxonomy', '__return_empty_string' );
		add_filter('acf/format_value/type=nav_menu_select', '__return_empty_string' );
		add_filter('acf/format_value/type=gallery', '__return_empty_string' );

		add_filter('acf/format_value/type=user', '__return_empty_string' ); // we do not export users
	}

	/**
	 *	Export: generate post reference
	 *
	 *	@filter acf/format_value/type=file|image|gallery
	 */
	public function reference_file( $value, $post_id, $field ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$refs = array_map( function( $value ) use ( $field ) {
			$reference = "attachment:{$value}";
			if ( ! isset( $this->export_references[ $reference ] ) ) {
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
					}
				} else {
					return '';
				}
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
	 *	Export: generate post reference
	 *
	 *	@filter acf/format_value/type=post_object|relationship
	 */
	public function reference_posts( $value, $post_id, $field ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$refs = array_map( function( $post_id ) {
			$reference = "post:$post_id";
			if ( ! isset( $this->export_references[ $reference ] ) ) {

				$post = get_post( $post_id );

				// something is broken
				if ( ! ( $post instanceOf \WP_Post ) ) {
					return '';
				}

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
	 *	Export: generate term reference
	 *
	 *	@filter acf/format_value/type=taxonomy
	 */
	public function reference_terms( $value, $post_id, $field ) {
		if ( empty( $value ) ) {
			return $value;
		}

		// make sure to reference only term IDs
		$values = array_filter( array_map( function( $val ) {
			if ( $val instanceOf \WP_Term ) {
				return $val->term_id;
			} else if ( is_scalar( $val ) ) {
				return $val;
			}
			return false;
		}, array_filter( (array) $value ) ) );

		// term IDs to array
		$refs = array_map( function( $term_id ) {
			$reference = "term:$term_id";
			if ( ! isset( $this->export_references[ $reference ] ) ) {
				$term = get_term( absint( $term_id ) );

				// something is broken
				if ( ! ( $term instanceOf \WP_Term ) ) {
					return false;
				}

				$term_data = get_object_vars( $term );
				$term_meta = get_term_meta( $term->term_id );

				foreach ( [ 'term_id', 'term_taxonomy_id', 'parent', 'count', 'filter', ] as $prop ) {
					if ( isset( $term_data[ $prop ] ) ) {
						unset( $term_data[ $prop ] );
					}
				}

				$this->export_references[ $reference ] = [
					'term' => $term_data,
					'meta' => $term_meta,
				];
			}
			return $reference;
		}, $values );

		if ( ! is_array( $value ) ) {
			$this->field_export_references[ $post_id.':'.$field['name'] ] = $refs[0];
		} else {
			$this->field_export_references[ $post_id.':'.$field['name'] ] = $refs;
		}
		return $value;
	}

	/**
	 *	Export: Replace ACF value with reference
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
	 *	Import: Replace ACF value with reference
	 *
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
