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



class OptionsPage extends Core\Singleton {

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

		if ( ! empty( $_GET['message'] ) ) {
			$page = $this->pages_by_slug[ $page_hook ];
			if ( $_GET['message'] === 'reset' ) {
				acf_add_admin_notice( $page['reset_message'], 'success' );
			} else if ( $_GET['message'] === 'import' ) {
				acf_add_admin_notice( $page['import_message'], 'success' );
			} else if ( $_GET['message'] === 'import_error' ) {
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

		if ( ! isset( $_POST['options_page_action'] ) ) {
			return;
		}

		$action = wp_unslash( $_POST['options_page_action'] );

		$page_slug = wp_unslash( $_POST['options_page_slug'] );

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

		$this->reset_page( $page );

		wp_redirect( add_query_arg( array( 'message' => 'reset' ) ) );

		exit();

	}

	public function action_import( $page ) {
		if ( isset( $_POST['import_json'] ) ) {
			$data = json_decode( wp_unslash( $_POST['import_json'] ), true );
			if ( is_null( $data ) || ! isset( $data['values'] ) ) {
				wp_redirect( add_query_arg( array( 'message' => 'import_error' ) ) );
				exit();
			}
			$_POST['acf'] = $data['values'];
		}
	}

	/**
	 *	Export values from options page
	 *
	 *	@param Array $page ACF options page
	 */
	public function action_export( $page ) {

		// export after values have been saved
		add_action( 'acf/save_post', [ $this, 'action_export_after' ], 99 );

	}

	/**
	 *	@action load-{$page_hook}
	 */
	public function action_export_after() {

		$data = $rhis->get_export_data( $this->current_page );
		$json_str = json_encode( $data );

		header('Content-Type: application/json; charset=utf-8' );
		header( sprintf('Content-Disposition: attachment; filename="%s_%s.json"', $this->current_page['post_id'], date( 'YmdHis' ) ) );
		header( sprintf('Content-Length: %d', strlen( $json_str ) ) );

		echo $json_str;

		exit();
	}

	/**
	 *	@param String|Array $page Options page slug or config
	 */
	public function get_export_data( $page ) {

		if ( is_string( $page ) ) {
			$page = acf_get_options_page( $page );
		}

		$fields = $this->get_fields( $page );
		$values = [];
		foreach ( $fields as $field ) {
			$value = get_field( $field['name'], $page['post_id'], false );
			if ( ! is_null( $value ) ) {
				$values += [ $field['key'] => $value ];
			}
		}
		return [
			'page' => $page,
			'values' => $values,
		];

	}

	public function reset_page( $page ) {

		$fields = $this->get_fields( $page );

		foreach ( $fields as $field ) {
			acf_delete_value( $page['post_id'], $field );
		}

	}


	/**
	 *	@param Array $page
	 *	@return Array
	 */
	private function get_fields( $page ) {

		$fields = [];

		$field_groups = acf_get_field_groups( [ 'options_page' => $page['menu_slug'] ] );

		foreach ( $field_groups as $i => $field_group ) {

			$fields = array_merge( $fields, acf_get_fields( $field_group ) );

		}

		return $fields;
	}

	/**
	 *	@filter acf/validate_options_page
	 */
	public function validate_options_page( $page ) {
		return wp_parse_args( $page, [
			'import' => false,
			'import_message' => __( 'Options Imported', 'acf-wp-objects' ),
			'import_error_message' => __( 'Invalid Import Data', 'acf-wp-objects' ),
			'reset_message' => __( 'Options Reset to Defaults', 'acf-wp-objects' ),
			'export' => false,
			'reset' => false,
			'reset_button' => __( 'Restore defaults', 'acf-wp-objects' ),
			'import_button' => __( 'Import', 'acf-wp-objects' ),
			'import_select_file' => __( 'Select File…', 'acf-wp-objects' ),
			'export_button' => __( 'Export Settings', 'acf-wp-objects' ),
		]);
	}

	/**
	 *	@action acf/options_page/submitbox_before_major_actions
	 */
	public function submitbox_before_major_actions( $page ) {
		if ( $page['export'] ) {
			?>
			<div class="acf-wpo-export">
				<button type="submit" name="options_page_action" value="export" class="button button-large widefat" id="export">
					<?php echo $page['export_button']; ?>
				</button>
			</div>
			<?php
		}
		if ( $page['import'] ) {
			?>
			<div class="acf-wpo-import">
				<h4><?php _e( 'Import', 'acf-wp-objects' ); ?></h4>
				<input type="text" class="acf-hidden" id="acf-wpo-import-json" name="import_json" value="" />
				<input type="file" class="acf-hidden" id="acf-wpo-import-file" accept="application/json" />
				<label class="button button-large widefat" for="acf-wpo-import-file">
					<?php echo $page['import_select_file']; ?>
				</label>
				<button type="submit" name="options_page_action" value="import" class="button button-primary button-large widefat" id="import" disabled>
					<?php echo $page['import_button']; ?>
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
			<button type="submit" name="options_page_action" value="reset" class="button button-large" id="reset">
				<?php echo $page['reset_button']; ?>
			</button>
			<?php
		}
	}

}
