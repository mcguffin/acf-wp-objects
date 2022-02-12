<?php
/**
 *	@package ACFWPObjects\Core
 *	@version 1.0.1
 *	2018-09-22
 */

namespace ACFWPObjects\Core;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}
use ACFWPObjects\Asset;
use ACFWPObjects\Compat;
use ACFWPObjects\WPCLI;

class Core extends Plugin implements CoreInterface {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'plugins_loaded', [ $this, 'init_compat' ], 0 );

		$args = func_get_args();
		parent::__construct( ...$args );
	}

	/**
	 *	Load Compatibility classes
	 *
	 *  @action plugins_loaded
	 */
	public function init_compat() {

		if ( function_exists('\acf') && version_compare( acf()->version,'5.6.0','>=') ) {

			$acf = acf();

			Template::instance();

			Compat\ACF\ACF::instance();
			$init_wpmu = false;
			// So many conditions...
			if ( is_multisite() && acf_get_setting( 'pro' ) ) {

				if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
    				require_once ABSPATH . '/wp-admin/includes/plugin.php';
				}
				$init_wpmu = is_plugin_active_for_network( $this->get_wp_plugin() )
							&& is_plugin_active_for_network( acf_get_setting('basename') );
			}

			if ( $init_wpmu ) {
				Compat\WPMU::instance();
			} else {
				Compat\NoWPMU::instance();
			}

			if ( acf_get_setting( 'pro' ) ) {
				require_once $this->get_plugin_dir() . '/include/api/page-layouts.php';
				require_once $this->get_plugin_dir() . '/include/api/options-page.php';
			}

			if ( class_exists( '\ACFCustomizer\Core\Core' ) ) {
				Compat\ACFCustomizer::instance();
			}
			if ( class_exists( '\Classic_Editor' ) ) {
				Compat\ClassicEditor::instance();
			}

			if ( defined( 'POLYLANG_VERSION' ) ) {
				Compat\Polylang::instance();
			}

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				WPCLI\WPCLI::instance();
			}

			require_once $this->get_plugin_dir() . '/include/api/api.php';
			require_once $this->get_plugin_dir() . '/include/api/local-json.php';
			require_once $this->get_plugin_dir() . '/include/api/localization.php';

		} else {
			add_action('admin_notices', [ $this, 'print_no_acf_notice' ] );
			return;
		}
	}


	/**
	 *	@action admin_notices
	 */
	public function print_no_acf_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php
				printf(
					wp_kses(
						/* Translators: 1: ACF Pro URL, 2: plugins page url */
						__( 'The <strong>ACF WP-Objects</strong> plugin requires <a href="%1$s" target="_blank" rel="noopener noreferrer">ACF version 5.8 or later</a>. You can disable and uninstall it on the <a href="%2$s">plugins page</a>.',
							'acf-quickedit-fields'
						),
						[
							'strong' => [],
							'a'	=> [ 'href' => [], 'target' => [], 'rel' => '' ]
						]
					),
					esc_url( 'https://www.advancedcustomfields.com/' ),
					esc_url( admin_url('plugins.php' ) )

				);
			?></p>
		</div>
		<?php
	}
}
