<?php
/**
 *	@package AcfWpObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace AcfWpObjects\Compat\Hook;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use AcfWpObjects\Core;

class ThumbnailPreview extends Preview {

	public function get( $value ) {
		@list( $check, $post_id, $meta_key, $single ) = func_get_args();
		if ( $meta_key !== '_thumbnail_id') {
			return $check;
		}
		return $this->value;
	}

}
