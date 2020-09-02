<?php

namespace ACFWPObjects;

class PluginTest {

	private $current_json_save_path = null;

	public function __construct() {

		add_action( 'plugins_loaded', function() {
			acf_register_local_json( 'acf-json', '__return_true', [ dirname( __FILE__ ) ] );
		} );


		// add_filter( 'acf/settings/load_json', [ $this, 'load_json' ] );
		//
		// add_filter( 'acf/settings/save_json', [ $this, 'save_json' ] );
		//
		// add_action( 'acf/delete_field_group', [ $this, 'mutate_field_group' ], 9 );
		// add_action( 'acf/trash_field_group', [ $this, 'mutate_field_group' ], 9 );
		// add_action( 'acf/untrash_field_group', [ $this, 'mutate_field_group' ], 9 );
		// add_action( 'acf/update_field_group', [ $this, 'mutate_field_group' ], 9 );

		add_action('acf/init', [ $this,'add_options_page' ] );
		add_action( 'acf/init', [ $this, 'register_blocks' ] );

		add_action('init', [ $this,'init' ] );

		// add template type
		add_filter( 'acf_wp_objects_template_types', function($types){
			$types[ 'acf-wp-objects' ] = [
				'header_key' => 'ACF WP Objects Template',
				'theme_location' => 'acf-wp-objects',
				'plugin_location' => 'test/templates',
			];
			return $types;
		});


		add_action( 'wp_footer', function(){
			?>
			<div style="position:relative;">
				<code>the_field('test_text','network_opt_test');</code>=
				<strong>
					<?php the_field('test_text','network_opt_test'); ?>
				</strong>
			</div>
			<div style="position:relative;">
				<code>the_field('test_text','acf_wpo_opt_test');</code>=
				<strong>
					<?php the_field('test_text','acf_wpo_opt_test'); ?>
				</strong>
			</div>
			<?php
		} );
	}

	/**
	 *	@action init
	 */
	public function init() {

		register_post_type('acf-wp-objects-test',[
			'label'		=> 'WP-Objects Tests',
			'public'	=> true,
			'supports'	=> ['title'],
		]);

		register_taxonomy('acf-wp-objects-test','acf-wp-objects-test',[
			'label'		=> 'WP-Objects Test Terms',
			'labels'	=> [
				'no_terms'	=> 'No Terms',
			],
			'public'	=> true,
		]);

	}

	/**
	 *	@action 'acf/init'
	 */
	public function register_blocks() {

		if ( ! function_exists('acf_register_block') ) {
			error_log("! function_exists('acf_register_block')");
			return;
		}

		// register a testimonial block
		acf_register_block(array(
			'name'				=> 'wp-objects-template-test',
			'title'				=> __('WP Objects Template Test'),
			'description'		=> __('WP Objects Template'),
			'render_callback'	=> function ( $block, $content, $is_preview, $post_id ) {
				printf('<div class="align%s">',$block['align']);
				get_template_part(get_field('some_plugin_template'));
				//the_field( 'leaflet_map_block' );
				echo '</div>';
				?><hr /><?php
			},
			'category'			=> 'embed',
			'icon'				=> 'admin-tools',
			'mode'				=> 'edit', // auto|preview|edit
			'keywords'			=> array( 'test' ),
		));

	}


	/**
	 *	@action acf/init
	 */
	public function add_options_page() {
		$nwp = acf_add_options_page([
			'page_title'	=> 'ACF WP-Objects Network Options',
			'menu_title'	=> 'WP-Objects',
			'post_id'		=> 'network_opt_test',
			'menu_slug'		=> 'wpo-test-network-options',
			'position'		=> 50,
			'redirect'		=> false,
			'network'		=> true,
		]);
		acf_add_options_sub_page([
			'page_title'	=> 'Network Options Sub #1',
			'menu_title'	=> 'Sub #1',
			'post_id'		=> 'network_opt_test',
			'parent'		=> $nwp['menu_slug'],
			'network'		=> true,
		]);
		acf_add_options_sub_page([
			'page_title'	=> 'Network Options Sub #2',
			'menu_title'	=> 'Sub #2',
			'post_id'		=> 'acf_wpo_opt_test',
			'parent'		=> $nwp['menu_slug'],
			'network'		=> true,
		]);
		$blp = acf_add_options_page([
			'page_title'	=> 'ACF WP-Objects Options',
			'menu_title'	=> 'WP-Objects',
			'post_id'		=> 'acf_wpo_opt_test',
			'menu_slug'		=> 'wpo-test-options',
			'redirect'		=> false,
		]);
		acf_add_options_sub_page([
			'page_title'	=> 'ACF WP-Objects Fields',
			'menu_title'	=> 'Fields',
			'post_id'		=> 'acf_wpo_fields_test',
			'menu_slug'		=> 'wpo-test-fields',
			'parent'		=> $blp['menu_slug'],
		]);
		acf_add_options_sub_page([
			'page_title'	=> 'ACF WP-Objects Repeater',
			'menu_title'	=> 'Repeater ...',
			'post_id'		=> 'acf_wpo_fields_test_repeater',
			'menu_slug'		=> 'wpo-test-repeater',
			'parent'		=> $blp['menu_slug'],
		]);
		acf_add_options_sub_page([
			'page_title'	=> 'ACF WP-Objects Repeater Choices',
			'menu_title'	=> '... Choices',
			'post_id'		=> 'acf_wpo_fields_test_repeater',
			'menu_slug'		=> 'wpo-test-choices',
			'parent'		=> $blp['menu_slug'],
		]);
		acf_add_options_sub_page([
			'page_title'	=> 'ACF WP-Objects Options + Theme Mods',
			'menu_title'	=> 'Options + Mods',
			'post_id'		=> 'acf_wpo_fields_test_repeater',
			'menu_slug'		=> 'wpo-test-options-mods',
			'parent'		=> $blp['menu_slug'],
		]);
	}


	/**
	 *	@filter 'acf/settings/save_json'
	 */
	public function load_json( $paths ) {
		$paths[] = dirname(__FILE__).'/acf-json';
		return $paths;
	}

	/**
	 *	@filter 'acf/settings/save_json'
	 */
	public function save_json( $path ) {
		if ( ! is_null( $this->current_json_save_path ) ) {
			return $this->current_json_save_path;
		}
		return $path;
	}

	/**
	 *	Figure out where to save ACF JSON
	 *
	 *	@action 'acf/update_field_group'
	 */
	public function mutate_field_group( $field_group ) {
		// default

		if ( strpos( $field_group['key'], 'group_acf_wp_objects_' ) === false ) {
			$this->current_json_save_path = null;
			return;
		}
		$this->current_json_save_path = dirname(__FILE__).'/acf-json';

	}
}

new PluginTest();
