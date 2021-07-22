<?php

namespace ACFWPObjects\Compat\ACF\Location;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF as CompatACF;


class WPOptionsPage extends \acf_location {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'wp_options';
		$this->label = __( 'WP Options Page', 'acf-wp-objects' );
		$this->category = __( 'WordPress', 'acf-wp-objects' );

	}

	/**
	 *	@inheritdoc
	 */
	function rule_match( $result, $rule, $screen ) {

		global $pagenow;

		if ( $pagenow === 'options.php' && isset( $_POST['option_page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$options_page = sprintf(
				'options-%s.php',
				sanitize_key( wp_unslash( $_POST['option_page'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			);
		} else {
			$options_page = $pagenow;
		}

		return $this->compare( $options_page, $rule );

	}


	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		// global
		$choices = [
			'options-general.php'		=> __( 'General', 'acf-wp-objects' ), // do_settings_sections('general')
			'options-writing.php'		=> __( 'Writing', 'acf-wp-objects' ),
			'options-reading.php'		=> __( 'Reading', 'acf-wp-objects' ),
			'options-discussion.php'	=> __( 'Discussion', 'acf-wp-objects' ),
			'options-media.php'			=> __( 'Media', 'acf-wp-objects' ),
			'options-permalink.php'		=> __( 'Permalinks', 'acf-wp-objects' ),
			//'options-privacy.php'		=> __( 'Privacy', 'acf-wp-objects' ), // Guys! There's more than just the PP page!
		];

		return $choices;

	}


}
