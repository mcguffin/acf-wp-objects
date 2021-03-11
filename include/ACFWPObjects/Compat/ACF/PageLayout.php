<?php


namespace ACFWPObjects\Compat\ACF;

use ACFWPObjects\Core;

class PageLayout extends Core\Singleton {

	/** @var Array */
	private $page_layouts = [];

	/** @var Boolean|String Layout name to save */
	private $should_save_post_content = false;

	/**
	 *	@inheritdoc
	 */
	public function __construct() {

		add_action( 'acf/init', [ $this, 'init' ] );

		add_action( 'acf/render_field_group_settings', [ $this, 'field_group_settings' ] );

		add_action( 'acf/include_location_rules', [ $this, 'register_location_rules' ] );

		add_action('save_post', [ $this, 'save_post' ], 20, 3 );

	}



	/**
	 *	@action save_post
	 */
	public function save_post( $post_id, $post, $update ) {

		if ( false !== $this->should_save_post_content ) {

			$content = false;

			$layout = $this->should_save_post_content;

			$this->should_save_post_content = false;

			$save_post_content = $this->get( $layout, 'save_post_content' );

			if ( true === $save_post_content ) {

				ob_start();

				acf_page_layouts( $layout, $post_id );

				$contents = ob_get_clean();

				wp_update_post([
					'ID' => $post_id,
					'post_content' => $contents,
				]);

			} else if ( is_callable( $save_post_content ) ) {

				$content = call_user_func_array( $save_post_content, [ $layout, $post_id, $this ] );

			}

		}
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
	 *	@param string|array $args [
	 *		'key'					=> String '',
	 *		'name'					=> String '',
	 *		'title'					=> String '',
	 *		'label_placement'		=> String '',
	 *		'instruction_placement'	=> String '',
	 *		'type'					=> String '',
	 *		'menu_order'			=> Integer,
	 *		'save_post_content'		=> Boolean|Callable True: runs acf_page_layouts, callable: must save the post itself
	 *		'button_label'			=> String '',
	 *		'location'				=> Array,
	 *		'hide_on_screen'		=> Array,
 	 * ]
	 */
	public function register( $args = '' ) {
		if ( empty( $args ) ) {
			return null;
		}
		if ( is_string( $args ) ) {
			$args = [
				'title' => $args,
				'name' => $args,
			];
		}
		$key = sanitize_title( $args['name'], sanitize_key( $args['name'] ), 'save' ); //
		$args = wp_parse_args( $args, [
			'key'					=> 'group_' . $key, // ?
			'name'					=> $key,
			'title'					=> $args['name'],
			'label_placement'		=> 'top',
			'instruction_placement'	=> 'label',
			'type'					=> 'flexible_content',
			'menu_order'			=> 0,
			'save_post_content'		=> false,
			'style'					=> 'seamless',
			'button_label'			=> __( 'Add section', 'acf-wp-objects' ),
			'location'				=> [
				[
					[
						'param'		=> 'post_type',
						'operator'	=> '==',
						'value'		=> 'page',
					],
				]
			],
			'hide_on_screen' => [
				'the_content',
				'excerpt'
			],
		]);

		$this->page_layouts[ $key ] = $args;
		return $args;
	}


	/**
	 *	@param Boolean|String $layout
	 *	@param Boolean|String $property
	 *	@return mixed
	 */
	public function get( $layout = false, $property = false ) {
		if ( false === $layout ) {
			return $this->page_layouts;
		}
		if ( ! isset( $this->page_layouts[ $layout ] ) ) {
			return null;
		}
		if ( false !== $property ) {
			if ( isset( $this->page_layouts[ $layout ][ $property ] ) ) {
				return $this->page_layouts[ $layout ][ $property ];
			}
			return null;
		}
		return $this->page_layouts[ $layout ];
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

		$field_group = wp_parse_args($field_group, [
			'row_layout' => '',
			'layout_min' => '',
			'layout_max' => '',
		]);

		if ( empty( $field_group['row_layout'] ) ) {
			$field_group['row_layout'] = $default_title;
		}
		if ( ! empty( $field_group['row_layout'] ) ) {
			$instructions = sprintf(
				/* translators: theme file location */
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


		acf_render_field_wrap( [
			'label'			=> __('Min Layouts','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'layout_min',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group[ 'layout_min' ],
		] );

		acf_render_field_wrap( [
			'label'			=> __('Max Layouts','acf-wp-objects'),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'layout_max',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group[ 'layout_max' ],
		] );

	}



	/**
	 *	@action acf/init
	 */
	public function init() {
		array_map( [ $this, 'init_layout' ], array_keys( $this->page_layouts ) );
	}


	/**
	 *	Make sure field keys are not referenced in the db
	 */
	private function deep_reset_field_key( $field ) {
		if ( ! is_array( $field ) ) {
			return $field;
		}
		foreach ( $field as $k => $v ) {
			if ( is_array( $v ) ) {
				$field[$k] = $this->deep_reset_field_key( $v );
			} else if ( in_array( $k, [ 'key', 'field', 'collapsed' ], true ) ) {
				$field[$k] = 'field_' . md5( $v );
			}
		}

		return $field;
	}


	/**
	 *	@param string $layout_key
	 *	@param array $args
	 */
	private function init_layout( $layout_key ) {


		$layouts = [];

		$field_groups = acf_get_field_groups( [ 'page_layouts' => $layout_key ] );
		$field_groups = array_map( [ $this, 'sanitize_field_group'], $field_groups );

		$args = $this->page_layouts[ $layout_key ];

		// usort( $field_groups, function( $a, $b ) {
		// 	return $b['menu_order'] - $b['menu_order'];
		// } );

		foreach ( $field_groups as $field_group ) {
			$field_group = wp_parse_args($field_group,[
				'layout_min' => '',
				'layout_max' => '',
				'row_layout' => '',
			]);

			if ( empty( $field_group[ 'row_layout' ] ) ) {
				continue;
			}

			$key = 'layout_' . $field_group[ 'row_layout' ];// str_replace( 'group_', 'layout_',  );

			$sub_fields = array_map( function( $field ) {
				// detach field from database
				foreach ( [ 'ID', 'id', 'prefix', 'parent', 'value', 'menu_order' ] as $k ) {
					if ( isset( $field[ $k ] ) ) {
						unset( $field[ $k ] );
					}
				}

				// key to be guaranteed not in DB
				$field['ID'] = 0;
				$field = $this->deep_reset_field_key( $field );

				return $field;

			}, acf_get_fields( $field_group ) );

			$layouts[ $key ] = [
				'key'			=> $key,
				'name'			=> $field_group[ 'row_layout' ],
				'label'			=> $field_group['title'],
				'display'		=> $field_group['label_placement'] === 'top' ? 'block' : 'row',
				'sub_fields'	=> $sub_fields,
				'min'			=> $field_group[ 'layout_min' ],
				'max'			=> $field_group[ 'layout_max' ],
			];

		}
		$local_field_group = [
			'key'		=> 'group_'.$layout_key,
			'title' 	=> $args['title'],
			'fields'	=> [
				[
					'key'				=> 'field_' . $args['name'],
					'label'				=> $args['title'],
					'name'				=> $args['name'],
					'type'				=> $args['type'],
					'instructions'		=> '',
					'required'			=> 0,
					'conditional_logic'	=> [],
					'wrapper'			=> [
						'width'	=> '',
						'class'	=> '',
						'id'	=> '',
					],
					'layouts'			=> $layouts,
					'button_label'		=> $args['button_label'],
					'min'				=> '',
					'max'				=> '',
				]
			],
			'location'				=> $args['location'],
			'menu_order'			=> $args['menu_order'],
			'position'				=> 'normal',
			'style'					=> $args['style'],
			'label_placement'		=> $args['label_placement'],
			'instruction_placement'	=> $args['instruction_placement'],
			'hide_on_screen'		=> $args['hide_on_screen'],
		];
		acf_add_local_field_group( $local_field_group );

		if ( false !== $args['save_post_content'] ) {
			add_filter( 'acf/update_value/key=field_' . $args['name'], [ $this, 'update_value' ], 10, 3 );
		}

	}

	/**
	 *	Whenever a page layout field is updated
	 *
	 *	@filter acf/update_value
	 */
	public function update_value( $value, $post_id, $field ) {

		/** @var layout name */
		$this->should_save_post_content = $field['name'];

		return $value;

	}

	/**
	 *	@param array $field_group
	 *	@return array
	 */
	private function sanitize_field_group( $field_group ) {
		if ( ! isset( $field_group['row_layout'] ) || empty( $field_group['row_layout'] ) ) {
			$field_group['row_layout'] = sanitize_title( $field_group['title'], sanitize_key( $field_group['title'] ), 'save' );
		}
		return $field_group;
	}
}
