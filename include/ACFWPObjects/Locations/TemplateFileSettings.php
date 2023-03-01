<?php

namespace ACFWPObjects\Locations;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;


class TemplateFileSettings extends \acf_location {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'template_file_settings';
		$this->label = __( 'Template File Settings', 'acf-wp-objects' );
		$this->category = __( 'Generic', 'acf-wp-objects' );

	}

	/**
	 *	@inheritdoc
	 */
	function rule_match( $result, $rule, $screen ) {

		if ( ! isset( $screen[ $this->name ] ) ) {
			return false;
		}

		// return
		return $this->compare( $screen[ $this->name ], $rule );

	}

	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		//acf_get_fields();

		$template = Core\Template::instance();

		$choices = [];

		foreach ( $template->get_template_types() as $type ) {

			$choices[ $type['header_key'] ] = array_map(
				function($e){
					return $e['label'];
				},
				$template->get_templates( $type['header_key'] )
			);

		}

		// global
		return $choices;

	}

}
