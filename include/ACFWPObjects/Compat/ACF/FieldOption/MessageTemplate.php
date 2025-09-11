<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;

class MessageTemplate extends Core\Singleton {

	private $saved_values = [];

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_action( 'acf/render_field/type=post_object', [$this, 'render_field'], 10 );
		add_filter( 'acf/prepare_field/type=post_object', [ $this, 'prepare_field' ] );
	}

	/**
	 *	@filter acf/prepare_field/type=post_object
	 */
	public function render_field($field) {
		if ( ! empty( $field['message_template'] ) ) {
			printf('<template>%s</template>', $field['message_template']);
		}
		return $field;
	}

	/**
	 *	@filter acf/load_field/type=post_object
	 */
	public function prepare_field( $field ) {
		$field = wp_parse_args($field, [ 'message_template' => '', 'message_target' => 0 ]);
		if ( $field['post_type'] ) {
			$field['data']['post_type'] = implode(',',(array) $field['post_type']);
		}
		if ( $field['message_target'] ) {
			$field['data']['target'] = $field['message_target'];
		}
		return $field;
	}

}
