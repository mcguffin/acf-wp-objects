<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF\Hook;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;

class ThumbnailPreview extends Preview {

	public function get( $value ) {
		@list( $check, $post_id, $meta_key, $single ) = func_get_args();
		if ( $meta_key !== '_thumbnail_id') {
			return $check;
		}
		return $this->value;
	}

}
