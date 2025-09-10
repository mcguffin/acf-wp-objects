<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF\Helper;

/**
 *	Generate a post for each repeater value
 */
class RepeaterPostSync extends Core\Singleton {

	// private $saved_values = [];

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_filter( 'acf/load_field/type=repeater', [$this, 'load_field'] );
		add_filter( 'acf/prepare_field/type=repeater', [ $this, 'prepare_field' ] );
		// add_filter( 'acf/update_value/type=repeater', [$this, 'update_value'], 10, 3 );
		add_action( 'acf/save_post', [ $this, 'sync_post' ]);
		add_action('trashed_post', [ $this, 'sync_post'] );
		add_action('deleted_post', [ $this, 'deleted_post'] );

		add_action( 'duplicate_post_post_copy', [ $this, 'duplicate_post' ], 10, 4 );
	}

	/**
	 *	@filter acf/load_field/type=post_object
	 */
	public function prepare_field( $field ) {

		if ( is_array( $field['post_sync'] ) ) {
			$field['data']['post_id_field'] = $field['post_sync']['post_id_field'];
		}
		return $field;
	}

	/**
	 *	Setup sync
	 *
	 *	@filter acf/load_field/type=repeater
	 */
	public function load_field($field) {
		$field = wp_parse_args( $field, [
			'post_sync' => false,
		] );

		if ( is_array( $field['post_sync'] ) ) {
			$field['post_sync'] = wp_parse_args( $field['post_sync'], [
				'post_id_field' => false,
				// new post props
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_title'    => $field['label'],
			] );
		}
		return $field;
	}

	/**
	 *	Convert value array from field keys to field names
	 *
	 *	@param array $data
	 *	@return array
	 */
	private function translate_field_keys( $data ) {
		$new_data = [];
		foreach ( $data as $key => $value ) {
			$name = $this->key_to_name( $key );
			if ( is_array( $value ) ) {
				$new_data[ $name ] = $this->translate_field_keys($value);
			} else {
				$new_data[ $name ] = $value;
			}
		}
		return $new_data;
	}

	/**
	 *	Convert field key to name
	 *
	 *	@param string $key
	 *	@return string
	 */
	private function key_to_name( $key ) {
		if ( $field = get_field_object( $key ) ) {
			return $field['name'];
		}
		return $key;
	}

	/**
	 *	@param int $post_id
	 *	@return array
	 */
	private function get_sync_fields($post_id) {
		$sync_field_keys = get_post_meta( $post_id, '_acfwpo_sync_repeater_fields', true );
		if ( ! is_array( $sync_field_keys )) {
			return [];
		}
		return $sync_field_keys;
	}

	/**
	 *	@param int $post_id
	 *	@param string $fieldkey
	 */
	private function add_sync_field( $post_id, $field_key ) {
		$sync_field_keys = $this->get_sync_fields($post_id);
		$sync_field_keys[] = $field_key;
		update_post_meta($post_id,'_acfwpo_sync_repeater_fields',array_unique($sync_field_keys),true);
	}

	/**
	 *	@param int $post_id
	 *	@action deleted_post
	 */
	public function deleted_post( $post_id ) {
		global $wpdb;
		$delete_post_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} AS p INNER JOIN {$wpdb->postmeta} AS m ON m.meta_key = %s WHERE p.post_parent = %d",
			[ '_acfwpo_sync_repeater_field', $post_id ]
		));

		foreach ( $delete_post_ids as $delete_post_id ) {
			wp_delete_post($delete_post_id, true);
		}
	}

	/**
	 *	@action duplicate_post_post_copy (yoast duplicate post plugin)
	 */
	public function duplicate_post( $post_id, $source_post, $status, $parent_id ) {

		$sync_field_keys = $this->get_sync_fields($post_id);

		if ( ! count( $sync_field_keys ) ) {
			return;
		}

		foreach ( $sync_field_keys as $field_key ) {
			$field         = acf_get_field($field_key);
			$post_id_field = $field['post_sync']['post_id_field'];
			$current       = get_field( $field['key'], $post_id, false );
			if ( ! is_array( $current ) ) {
				continue;
			}
			// reset post ids
			foreach ( $current as $i => $row ) {
				$sub_field     = get_field_object( $post_id_field );
				$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
				acf_update_value( 0, $post_id, $sub_field );
			}
			acf_get_store( 'values' )->reset();
			$this->sync($field_key, $post_id);
		}
	}

	/**
	 *	Sync all repeaters
	 */
	public function sync_all() {
		global $wpdb;
		$post_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE m.meta_key = %s",
			[ '_acfwpo_sync_repeater_field' ]
		));
		foreach ( $post_ids as $post_id ) {
			$this->sync_post($post_id);
		}
	}

	/**
	 *	Sync repeaters on save post
	 *
	 *	@action acf/sync_post
	 *	@action trashed_post
	 */
	public function sync_post( $post_id ) {
		foreach ( $this->get_sync_fields($post_id) as $field_key ) {
			$this->sync( $field_key, $post_id );
		}
	}

	/**
	 *	Sync a repeater
	 *
	 *	@param string $field_key Repeter field key
	 *	@param array $previous
	 *	@param int $post_id
	 */
	private function sync( $field_key, $post_id ) {
		$field         = get_field_object( $field_key );
		if ( ! $field['post_sync'] ) {
			return;
		}
		$current       = get_field( $field_key, $post_id, false );
		$post_id_field = $field['post_sync']['post_id_field'];
		$post_status   = get_post_status($post_id);

		// save synced in parent field
		$this->add_sync_field($post_id, $field_key);

		$saved_post_ids = [];
		// save posts
		foreach ( $current as $i => $curr ) {
			$postdata = array_diff_key( $field['post_sync'], [ 'post_id_field' => '' ] );
			$postdata['post_parent'] = $post_id;
			$postdata['post_status'] = $post_status;
			$postdata['menu_order']  = $i + 1;
			$postdata['meta_input']  = $this->translate_field_keys( array_diff_key( $curr, [ $post_id_field => '' ] ) );
			$postdata['meta_input']['_acfwpo_sync_repeater_field'] = $field_key;

			if ( $curr[$post_id_field] && get_post( $curr[$post_id_field] ) ) {
				$postdata['ID'] = $curr[$post_id_field];
			}
			if ( is_array( $postdata['post_title'] ) ) {
				$postdata['post_title'] = implode('', array_map(
					function($segment) use ( $post_id, $curr,$i ) {
						if ( 'post_title' === $segment ) {
							return get_the_title($post_id);
						} else if ( strpos($segment,'field_') !== false ) {
							$field = acf_get_field($segment);
							return acf_format_value($curr[$segment],"{$post_id}:{$i}",$field);
						} else if ( is_string($segment) ) {
							return $segment;
						}
						return '';
					},
					$postdata['post_title']
				));
			}
			$saved_post_id = wp_insert_post( $postdata, false, true );

			// save post id in acf repeater row
			$sub_field     = get_field_object( $post_id_field );
			$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

			acf_update_value( $saved_post_id, $post_id, $sub_field );

			$saved_post_ids[] = $saved_post_id;
		}

		// handle deleted rows
		global $wpdb;
		$all_post_ids = array_map('intval', $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s",
				[ $post_id, $field['post_sync']['post_type'] ]
			)
		) );

		foreach( array_diff( $all_post_ids, $saved_post_ids ) as $delete_post_id ) {
			wp_delete_post($delete_post_id, true);
		}
	}

}
