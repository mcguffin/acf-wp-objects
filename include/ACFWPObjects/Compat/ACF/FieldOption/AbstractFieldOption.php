<?php

namespace ACFWPObjects\Compat\ACF\FieldOption;

use ACFWPObjects\Core;

class AbstractFieldOption  extends Core\Singleton {

	protected $supported_fields = [];

	final protected function is_supported( $field ) {
		return
			! count( $this->supported_fields )
			|| in_array( $field['type'], $this->supported_fields );
	}

}
