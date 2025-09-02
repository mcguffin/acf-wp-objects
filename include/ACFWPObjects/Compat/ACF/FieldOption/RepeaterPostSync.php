<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF\Helper;

/**
 *	Generate a post for each repeater value
 */
class RepeaterPostSync extends Core\Singleton {

	private $saved_values = [];

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_filter( 'acf/load_field/type=repeater', [$this, 'load_field'] );
		add_filter( 'acf/update_value/type=repeater', [$this, 'update_value'], 10, 3 );
		add_action( 'acf/save_post', [ $this, 'save_post' ]);
	}

	/**
	 *	Collect values for syncing
	 *
	 *	@filter acf/update_value/type=repeater
	 */
	public function update_value( $value, $post_id, $field ) {
		if ( is_array( $field['post_sync'] ) ) {
			$this->saved_values[$field['key']] = get_field($field['key'], $post_id, false);
		}
		return $value;
	}

	/**
	 *	Sync repeaters on save post
	 *
	 *	@filter acf/update_value/type=repeater
	 */
	public function save_post( $post_id ) {
		foreach ( $this->saved_values as $field_key => $prev_value ) {
			$this->sync( $field_key, $prev_value, $post_id );
		}
	}

	/**
	 *	Sync a repeater
	 *
	 *	@param string $field_key
	 *	@param array $previous
	 *	@param int $post_id
	 */
	private function sync( $field_key, $previous, $post_id ) {
		$current       = get_field( $field_key, $post_id, false );
		$field         = get_field_object( $field_key );
		$post_id_field = $field['post_sync']['post_id_field'];

		foreach ( $previous as $prev ) {
			if ( $prev[$post_id_field] && is_null( array_find($current,function($item) use ($post_id_field,$prev) { return (int) $item[$post_id_field] === (int) $prev[$post_id_field]; }) ) ) {
				wp_delete_post($prev[$post_id_field], true);
			}
		}

		// save posts
		foreach ( $current as $i => $curr ) {
			$postdata = array_diff_key( $field['post_sync'], [ 'post_id_field' => '' ] );
			$postdata['post_parent'] = $post_id;
			$postdata['menu_order']  = $i + 1;
			$postdata['meta_input']  = $this->translate_field_keys( array_diff_key( $curr, [ $post_id_field => '' ] ) );
			if ( $curr[$post_id_field] && get_post( $curr[$post_id_field] ) ) {
				$postdata['ID'] = $curr[$post_id_field];
			}
			$saved_post_id = wp_insert_post( $postdata, false, false );

			$sub_field     = get_field_object( $post_id_field );
			$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

			acf_update_value( $saved_post_id, $post_id, $sub_field );
		}
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

}
