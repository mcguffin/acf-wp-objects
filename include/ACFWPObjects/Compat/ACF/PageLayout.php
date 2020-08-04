<?php


namespace ACFWPObjects\Compat\ACF;

use ACFWPObjects\Core;

class PageLayout extends Core\Singleton {

	private $page_layouts = [];

	/**
	 *	@param string $group_key
	 *	@param string $name
	 */
	public function __construct() {


		add_action( 'acf/init', [ $this, 'init' ] );

		add_action( 'acf/render_field_group_settings', [ $this, 'field_group_settings' ] );

		add_action( 'acf/include_location_rules', [ $this, 'register_location_rules' ] );

	}


	/**
	 *	@action acf/include_location_rules
	 */
	public function register_location_rules() {

		add_filter( 'acf_page_layout_locations', [ $this, 'add_locations' ] );
		acf_register_location_rule( 'ACFWPObjects\Compat\ACF\Location\PageLayouts' );

	}

	/**
	 *	@filter acf_sections_locations
	 */
	public function add_locations( $values ) {

		foreach ( $this->page_layouts as $key => $layout ) {
			$values[ $key ] = $layout['title'];
		}

		return $values;
	}

	/**
	 *	@param string|array $args
	 */
	public function register( $args = '' ) {
		if ( empty( $args ) ) {
			return null;
		}
		if ( is_string( $args ) ) {
			$args = [ 'title' => $args ];
		}
		$key = sanitize_title( $args['title'], sanitize_key( $args['title'] ), 'save' ); //
		$args = wp_parse_args( $args, [
			'key'					=> 'group_' . $key,
			'name'					=> $key,
			'style'					=> 'seamless',
			'label_placement'		=> 'top',
			'instruction_placement'	=> 'label',
			'type'					=> 'flexible_content',
			'location'				=> [
				[
					[
						'param'		=> 'post_type',
						'operator'	=> '==',
						'value'		=> 'page',
					],
				]
			],

		]);

		$this->page_layouts[ $key ] = $args;
	}

	/**
	 *	Field group setting
	 *
	 *	@action acf/render_field_group_settings
	 */
	public function field_group_settings( $field_group ) {
		if ( ! $field_group['ID'] ) {
			return;
		}
		$active = false;
		// has these locations
		$locations = array_filter( $field_group['location'], function( $el ) {
			$f = array_filter( $el, function($el2){
				return 'page_layouts' === $el2['param'];
			});
			return count($f) > 0;
		});
		if ( ! count( $locations ) ) {
			return;
		}

		$default_title = sanitize_title( $field_group['title'] );
		$field_group = wp_parse_args( $field_group, [ 'row_layout' => $default_title ] );
		$instructions = '';

		if ( empty( $field_group['row_layout'] ) ) {
			$field_group['row_layout'] = $default_title;
		}
		if ( ! empty( $field_group['row_layout'] ) ) {
			$instructions = sprintf(
				__( 'To render this field group place a template file in your theme: %s', 'acf-wp-objects' ),
				sprintf( '<code>acf/layout-%s.php</code>', $field_group[ 'row_layout' ] )
			);
		}

		// description
		acf_render_field_wrap( [
			'label'			=> __('Row Layout Slug','acf-wp-objects'),
			'instructions'	=> $instructions,
			'type'			=> 'text',
			'name'			=> 'row_layout',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group[ 'row_layout' ],
		] );
	}



	/**
	 *	@action acf_init
	 */
	public function init() {
		array_map( [ $this, 'init_layout' ], array_keys($this->page_layouts) );
	}

	/**
	 *	@param string $layout_key
	 *	@param array $args
	 */
	private function init_layout( $layout_key ) {
		$layouts = [];
		$field_groups = acf_get_field_groups( [ 'page_layouts' => $layout_key ] );
		$args = $this->page_layouts[ $layout_key ];

		// usort( $field_groups, function( $a, $b ) {
		// 	return $b['menu_order'] - $b['menu_order'];
		// } );

		foreach ( $field_groups as $field_group ) {
			$key = 'layout_' . $field_group[ 'row_layout' ];// str_replace( 'group_', 'layout_',  );

			$sub_fields = array_map( function( $field ) use ( $field_group ) {
				// detach field from database
				foreach ( [ 'ID', 'id', 'prefix', 'parent', 'value', 'menu_order' ] as $k ) {
					if ( isset( $field[ $k ] ) ) {
						unset( $field[ $k ] );
					}
				}
				// key to be guaranteed not in DB
				$field['ID'] = 0;
				$field['key'] = 'field_' . md5( $field['key'] );
				return $field;
			}, acf_get_fields( $field_group ) );

			$layouts[ $key ] = [
				'key'			=> $key,
				'name'			=> $field_group[ 'row_layout' ],
				'label'			=> $field_group['title'],
				'display'		=> $field_group['label_placement'] === 'top' ? 'block' : 'row',
				'sub_fields'	=> $sub_fields,
				'min'			=> '',
				'max'			=> '',
			];
//vaR_dump(array_keys($layouts));
		}

		acf_add_local_field_group([
			'key'		=> 'group_'.$layout_key,
			'title' 	=> $args['title'],
			'fields'	=> [
				[
					'key'				=> 'field_qtlife_sections',
					'label'				=> $args['title'],
					'name'				=> $args['name'],
					'type'				=> $args['type'],
					'instructions'		=> '',
					'required'			=> 0,
					'conditional_logic'	=> 0,
					'wrapper'			=> [
						'width'	=> '',
						'class'	=> '',
						'id'	=> '',
					],
					'layouts'			=> $layouts,
					'button_label'		=> __( 'Add section', 'acf-wp-objects' ),
					'min'				=> '',
					'max'				=> '',
				]
			],
			'location'				=> $args['location'],
			'menu_order'			=> 0,
			'position'				=> 'normal',
			'style'					=> 'seamless',
			'label_placement'		=> $args['label_placement'],
			'instruction_placement'	=> $args['instruction_placement'],
			'hide_on_screen'		=> [
				'the_content',
		        'excerpt'
			],
		]);
	}
}
