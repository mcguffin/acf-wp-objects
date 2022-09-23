<?php
/**
 *	@package ACFWPObjects\Compat
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWPObjects\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFWPObjects\Asset;
use ACFWPObjects\Core;
use ACFWPObjects\Compat\ACF\Helper;



class OptionsPage extends Core\Singleton {

	/** @var Array $curent_page ACF options Page */
	private $current_page = null;

	private $pages_by_slug = [];

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action('acf/options_page/submitbox_before_major_actions', [ $this, 'submitbox_before_major_actions' ]);
		add_action('acf/options_page/submitbox_major_actions', [ $this, 'submitbox_major_actions' ]);
		add_filter('acf/validate_options_page', [ $this, 'validate_options_page'] );

		add_action('acf/save_post', [ $this, 'save_post' ], 1 );

		add_action( 'admin_init', [ $this, 'admin_init' ], 100 );

	}

	/**
	 *	@action admin_init
	 */
	public function admin_init() {
		$pages = acf_get_options_pages();

		// bail early if no pages
		if ( empty( $pages ) ) {
			return;
		}


		global $plugin_page, $pagenow;
		// loop
		foreach ( $pages as $page ) {
			if ( ! $page['reset'] && ! $page['import'] && ! $page['export'] ) {
				continue;
			}
			$page_hook = $this->get_page_hook( $page );
			$this->pages_by_slug[ $page_hook ] = $page;

			add_action( "load-{$page_hook}", [ $this, 'admin_load' ] );
		}
	}

	/**
	 *	Reset values from options page
	 *
	 *	@param Array $page ACF options page
	 */
	private function get_page_hook( $page ) {
		if ( $page['parent_slug'] ) {
			$page_hook = get_plugin_page_hook( $page['menu_slug'], $page['parent_slug'] );
		} else {
			$page_hook = get_plugin_page_hook( $page['menu_slug'], $page['menu_slug'] );
		}
		return $page_hook;
	}

	/**
	 *	@action load-{$page_hook}
	 */
	public function admin_load() {
		global $page_hook;

		add_action( 'acf/input/admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// show import/export status messages
		if ( ! empty( $_GET['message'] ) ) {
			$page = $this->pages_by_slug[ $page_hook ];
			if ( $_GET['message'] === 'reset' ) {
				acf_add_admin_notice( $page['reset_message'], 'success' );
			} else if ( $_GET['message'] === 'import' ) {
				acf_add_admin_notice( $page['import_message'], 'success' );
			} else if ( $_GET['message'] === 'import_error' ) {
				acf_add_admin_notice( $page['import_error_message'], 'success' );
			}
		}
	}

	/**
	 *	@action acf/input/admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {

		Asset\Asset::get('js/admin/acf-options-page.js')->enqueue();

	}


	/**
	 *	@action acf/save_post
	 */
	public function save_post( $post_id ) {
		global $page_hook;

		// nonce already verified by WP
		if ( ! isset( $_POST['options_page_action'] ) || ! isset( $_POST['options_page_slug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		// no sanitation necessaray, just testing equality
		$action = wp_unslash( $_POST['options_page_action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// just an array key, no sanitation necessaray,
		$page_slug = wp_unslash( $_POST['options_page_slug'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$this->current_page = acf_get_options_page( $page_slug );

		if ( 'reset' === $action ) {

			$this->action_reset( $this->current_page );

		} else if ( 'import' === $action ) {

			$this->action_import( $this->current_page );

		} else if ( 'export' === $action ) {

			$this->action_export( $this->current_page );

		}
	}

	/**
	 *	Reset values from options page
	 *
	 *	@param Array $page ACF options page
	 */
	public function action_reset( $page ) {

		$helper = Helper\ImportExportOptionsPage::instance();
		$helper->reset( $page );

		if ( is_string( $page['reset'] ) && file_exists( $page['reset'] ) ) {

			$helper->import( file_get_contents( $page['reset'] ) );

		}

		wp_redirect( add_query_arg( array( 'message' => 'reset' ) ) );

		exit();

	}

	/**
	 *	@param Array $page ACF Options Page
	 */
	public function action_import( $page ) {
		// import values.
		if ( isset( $_POST['import_json'] ) && ! empty( $_POST['import_json'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$helper = Helper\ImportExportOptionsPage::instance();

			// sanitation done by Helper\ImportExportOptionsPage::import()
			if ( $helper->import( wp_unslash( $_POST['import_json'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				wp_redirect( add_query_arg( array( 'message' => 'import' ) ) );
			} else {
				wp_redirect( add_query_arg( array( 'message' => 'import_error' ) ) );
			}

			exit();
		}
	}

	/**
	 *	Export values from options page
	 *
	 *	@param Array $page ACF Options Page
	 */
	public function action_export( $page ) {

		// save values from form first, then export.
		add_action( 'acf/save_post', [ $this, 'action_export_after' ], 99 );

	}

	/**
	 *	@action load-{$page_hook}
	 */
	public function action_export_after() {

		$helper = Helper\ImportExportOptionsPage::instance();

		$json_str = json_encode(
			$helper->export( $this->current_page, true ),
			defined('WP_DEBUG') && WP_DEBUG
				? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
				: 0
		);

		header('Content-Type: application/json; charset=utf-8' );
		header( sprintf('Content-Disposition: attachment; filename="%s_%s.json"', $this->current_page['post_id'], date( 'YmdHis' ) ) );
		header( sprintf('Content-Length: %d', strlen( $json_str ) ) );

		echo $json_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		exit();
	}


	/**
	 *	@filter acf/validate_options_page
	 */
	public function validate_options_page( $page ) {
		return wp_parse_args( $page, [
			'import' => false,
			'import_message' => esc_html__( 'Options Imported', 'acf-wp-objects' ),
			'import_error_message' => esc_html__( 'Invalid Import Data', 'acf-wp-objects' ),
			'import_button' => esc_html__( 'Import', 'acf-wp-objects' ),
			'import_select_file' => esc_html__( 'Select File…', 'acf-wp-objects' ),

			'export' => false,
			'export_references' => false,
			'export_button' => esc_html__( 'Export Settings', 'acf-wp-objects' ),

			'reset' => false,
			'reset_button' => esc_html__( 'Restore defaults', 'acf-wp-objects' ),
			'reset_message' => esc_html__( 'Options Reset to Defaults', 'acf-wp-objects' ),
		]);
	}

	/**
	 *	@action acf/options_page/submitbox_before_major_actions
	 */
	public function submitbox_before_major_actions( $page ) {
		if ( $page['export'] ) {
			?>
			<div class="acf-wpo-export">
				<button type="button" name="options_page_action" value="export" class="button button-large widefat" id="export">
					<?php echo $page['export_button']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- code is assumed trusted ?>
				</button>
			</div>
			<?php
		}
		if ( $page['import'] ) {
			?>
			<div class="acf-wpo-import">
				<h4><?php esc_html_e( 'Import', 'acf-wp-objects' ); ?></h4>
				<input type="text" class="acf-hidden" id="acf-wpo-import-json" name="import_json" value="" />
				<input type="file" class="acf-hidden" id="acf-wpo-import-file" accept="application/json" />
				<label class="button button-large widefat" for="acf-wpo-import-file">
					<?php echo $page['import_select_file']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- code is assumed trusted ?>
				</label>
				<button type="button" name="options_page_action" value="import" class="button button-primary button-large widefat" id="import" disabled>
					<?php echo $page['import_button']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- code is assumed trusted ?>
				</button>
			</div>
			<?php
		}
	}

	/**
	 *	@action acf/options_page/submitbox_major_actions
	 */
	public function submitbox_major_actions( $page ) {
		if ( $page['reset'] || $page['import'] || $page['export'] ) {
			?>
			<input type="hidden" name="options_page_slug" value="<?php echo esc_attr( $page['menu_slug'] ); ?>" />
			<?php
		}
		if ( $page['reset'] ) {
			?>
			<button type="button" name="options_page_action" value="reset" class="button button-large" id="reset">
				<?php echo $page['reset_button']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- code is assumed trusted ?>
			</button>
			<?php
		}
	}

}
