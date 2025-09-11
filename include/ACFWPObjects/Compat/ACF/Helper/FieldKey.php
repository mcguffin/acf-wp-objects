<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF\Helper;

use ACFWPObjects\Core;

class FieldKey extends Core\Singleton {

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
	 *	Make sure field keys are not referenced in the db
	 *
	 *	@param Array $field
	 *	@return Array $field
	 */
	public function deep_reset_field_key( $field ) {
		if ( ! is_array( $field ) ) {
			return $field;
		}
		foreach ( $field as $k => $v ) {
			if ( is_array( $v ) ) {
				$field[$k] = $this->deep_reset_field_key( $v );
			} else if ( $this->is_field_key( $v, $k ) ) {
				$field[$k] = 'field_' . md5( $v );
			}
		}

		return $field;
	}

	/**
	 *	@param mixed $key
	 *	@param string $prop
	 *
	 *	@return bool
	 */
	public function is_field_key( $key, $prop = '' ) {
		return is_string($key) && 0 === strpos( $key, 'field_' ) && in_array( $prop, [ 'key', 'field', 'collapsed', 'message_target', 'post_id_field' ], true );
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

}
