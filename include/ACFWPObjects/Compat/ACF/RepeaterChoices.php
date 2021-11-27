<?php


namespace ACFWPObjects\Compat\ACF;

use ACFWPObjects\Core;

class RepeaterChoices extends Core\Singleton {

	/**
	 *	@var array field types that can serve as value or label fields mapped to their rendering function
	 */
	private $allow_fields = [
		'text'				=> 'text',
		'number'			=> 'text',
		'range'				=> 'text',
		'url'				=> 'text',
		'email'				=> 'text',
		'date_picker'		=> 'text',
		'time_picker'		=> 'text',
		'date_time_picker'	=> 'text',
		'image'				=> 'image',
		'color_picker'		=> 'color',
		'select'			=> 'text',
		'radio'				=> 'text',
		// post object
	];

	private $display_templates = [
		'text' => '[value:value]',
		'image' => [
			'label' =>'<span class="acf-image-choice">
				[label:value]
				<span class="image-label">[display:value]</span>
			</span>',
			'no-label' =>'<span class="acf-image-choice">
				[display:value:label]
				<span class="image-label">[display:value:image]</span>
			</span>',
		],
		'color' => [
			'no-label' => '<span class="white"></span>
				<span class="color-label" style="color:[display:value:contrast];background:[display:value];">
					<span class="screen-reader-text">[value:value]</span>
				</span>',

			'label' => '<span class="white"></span>
				<span class="acf-js-tooltip color-label" style="color:[display:value:contrast];background:[display:value];" title="[label:value]">
				</span>',
		],
	];

	/**
	 *	Choices for select repeater field dropdown
	 *
	 *	@var Array [
	 *		<field_group_title> => [
	 *			<field_key> => <field_label>,
	 *			...
	 *		],
	 *		...
	 *	]
	 */
	private $repeater_fields = null;

	/**
	 *	@var Array [
	 *		<field_key> => [
	 *			<subfield_key> => <subfield_value>
	 *			...
	 *		],
	 *		...
	 *	]
	 */
	private $repeater_values = null;


	private $value_cache = [];

	/**
	 *	@inheritdoc
	 */
	protected function __construct( ) {

		add_filter( 'acf/prepare_field/type=select', [ $this, 'prepare' ] );
		add_filter( 'acf/prepare_field/type=radio', [ $this, 'prepare' ] );
		add_filter( 'acf/prepare_field/type=button_group', [ $this, 'prepare' ] );
		add_filter( 'acf/prepare_field/type=checkbox', [ $this, 'prepare' ] );

		add_filter( 'acf/load_field/type=select', [ $this, 'migrate_field' ], 5 );
		add_filter( 'acf/load_field/type=radio', [ $this, 'migrate_field' ], 5 );
		add_filter( 'acf/load_field/type=button_group', [ $this, 'migrate_field' ], 5 );
		add_filter( 'acf/load_field/type=checkbox', [ $this, 'migrate_field' ], 5 );

		add_action( 'acf/render_field_settings/type=select', [ $this, 'add_choice_filter' ], 9 );
		add_action( 'acf/render_field_settings/type=radio', [ $this, 'add_choice_filter' ], 9 );
		add_action( 'acf/render_field_settings/type=button_group', [ $this, 'add_choice_filter' ], 9 );
		add_action( 'acf/render_field_settings/type=checkbox', [ $this, 'add_choice_filter' ], 9 );

		add_action( 'acf/render_field_settings/type=select', [ $this, 'render_settings' ] );
		add_action( 'acf/render_field_settings/type=radio', [ $this, 'render_settings' ] );
		add_action( 'acf/render_field_settings/type=button_group', [ $this, 'render_settings' ] );
		add_action( 'acf/render_field_settings/type=checkbox', [ $this, 'render_settings' ] );

		add_action( 'acf/render_field_settings/type=select', [ $this, 'remove_choice_filter' ], 11 );
		add_action( 'acf/render_field_settings/type=radio', [ $this, 'remove_choice_filter' ], 11 );
		add_action( 'acf/render_field_settings/type=button_group', [ $this, 'remove_choice_filter' ], 11 );
		add_action( 'acf/render_field_settings/type=checkbox', [ $this, 'remove_choice_filter' ], 11 );

		add_filter( 'acf/format_value/type=select', [ $this, 'format_value' ], 15, 3 );
		add_filter( 'acf/format_value/type=radio', [ $this, 'format_value' ], 15, 3 );
		add_filter( 'acf/format_value/type=button_group', [ $this, 'format_value' ], 15, 3 );
		add_filter( 'acf/format_value/type=checkbox', [ $this, 'format_value' ], 15, 3 );

	}

	/**
	 *	@action acf/render_field_settings/type=*
	 */
	public function add_choice_filter( $field ) {

		add_filter( 'acf/validate_field', [ $this, 'validate_return_format_field' ] );

	}

	/**
	 *	@action acf/render_field_settings/type=*
	 */
	public function remove_choice_filter( $field ) {

		remove_filter( 'acf/validate_field', [ $this, 'validate_return_format_field' ] );

	}

	/**
	 *	@filter acf/format_value/?type=*
	 */
	public function format_value( $value, $post_id, $field ) {

		if ( ! isset( $field['repeater_choices'] ) || ! $field['repeater_choices'] || $field['return_format'] !== 'row' ) {
			return $value;
		}

		$cache_key = sprintf( '%s-%s-%s', $field['repeater_field'], $field['repeater_post_id'], $value );

		if ( isset( $this->value_cache[ $cache_key ] ) ) {
			return $this->value_cache[ $cache_key ];
		}

		// generate value cache
		while ( have_rows( $field['repeater_field'], $field['repeater_post_id'] ) ) {
			the_row();
			$raw_row = get_row( false );

			$key = sprintf( '%s-%s-%s', $field['repeater_field'], $field['repeater_post_id'], sanitize_key( $raw_row[ $field['repeater_value_field'] ] ) );
			$this->value_cache[ $key ] = get_row( true );

			if ( $cache_key === $key ) {
				$value = $this->value_cache[ $key ];
			}
		}
		return $value;
	}

	/**
	 *	@filter acf/validate_field
	 */
	public function validate_return_format_field( $field ) {

		if ( $field['name'] === 'return_format' && isset( $field['choices'] ) ) {
			$field['choices']['row'] = __('Repeater Row', 'acf-wp-objects' );
		}
		return $field;
	}

	/**
	 *	@action acf/render_field_settings/type=*
	 */
	public function render_settings( $field ) {

		$repeater_field_choices = $this->get_first_level_repeater_fields();
		$value_field_choices = $label_field_choices = $this->get_repeated_fields( $repeater_field_choices );

		// enable
		acf_render_field_setting( $field, [
			'label'			=> __('Get choices from repeater','acf-wp-objects'),
			'instructions'	=> '',
			'name'			=> 'repeater_choices',
			'type'			=> 'true_false',
			'ui'			=> 1,
		]);

		// repeater field
		acf_render_field_setting( $field, [
			'label'			=> __('Repeater Field','acf-wp-objects'),
			'instructions'	=> '',
			'name'			=> 'repeater_field',
			'type'			=> 'select',
			'ui'			=> 0,
			'allow_null'	=> 1,
			'choices'		=> $repeater_field_choices,
			'conditions'	=> [
				'field'		=> 'repeater_choices',
				'operator'	=> '==',
				'value'		=> 1
			]
		]);


		// value field
		acf_render_field_setting( $field, [
			'label'			=> __('Value Field','acf-wp-objects'),
			'instructions'	=> '',
			'name'			=> 'repeater_value_field',
			'type'			=> 'select',
			'ui'			=> 0,
			'allow_null'	=> 0,
			'choices'		=> $value_field_choices,
			'conditions'	=> [
				[
					'field'		=> 'repeater_choices',
					'operator'	=> '==',
					'value'		=> 1
				],
				[
					'field'		=> 'repeater_field',
					'operator'	=> '!=empty',
				],
			]
		]);
		// label field
		acf_render_field_setting( $field, [
			'label'			=> __('Label Field','acf-wp-objects'),
			'instructions'	=> '',
			'name'			=> 'repeater_label_field',
			'type'			=> 'select',
			'ui'			=> 0,
			'allow_null'	=> 1,
			'choices'		=> $label_field_choices,
			'conditions'	=> [
				[
					'field'		=> 'repeater_choices',
					'operator'	=> '==',
					'value'		=> 1
				],
				[
					'field'		=> 'repeater_field',
					'operator'	=> '!=empty',
				],
			]
		]);
		if ( $field['type'] !== 'select' ) {
			// enable
			acf_render_field_setting( $field, [
				'label'			=> __('Visualize field','acf-wp-objects'),
				'instructions'	=> '',
				'name'			=> 'repeater_display_field',
				'type'			=> 'select',
				'ui'			=> 0,
				'allow_null'	=> 1,
				'choices'		=> $label_field_choices + [ '__custom__' => __('Custom','acf-wp-objects') ],
				'conditions'	=> [
					[
						'field'		=> 'repeater_choices',
						'operator'	=> '==',
						'value'		=> 1
					],
					[
						'field'		=> 'repeater_field',
						'operator'	=> '!=empty',
					],
				]
			]);

			$instructions = __( 'Leave empty for default.','acf-wp-objects')
				. '<br />'
				. __( 'Placeholder format: <code>[index]</code> (the repeater row index) or <code>[&lt;field&gt;:&lt;property&gt;]</code>.', 'acf-wp-objects' )
				. '<br />'
				. __( '<code>&lt;field&gt;</code> can be <code>value</code>, <code>label</code> or <code>display</code> to refer to either the label-, value- or display field selected above.', 'acf-wp-objects' )
				. '<br />'
				. __( '<code>&lt;property&gt;</code> can be <code>name</code>, <code>value</code>, <code>label</code> or <code>key</code> to refert to the field‘s name, value, label or key', 'acf-wp-objects' );

			// enable
			acf_render_field_setting( $field, [
				'label'			=> __('Visualize Template','acf-wp-objects'),
				'instructions'	=> $instructions,
				'name'			=> 'repeater_choice_template',
				'type'			=> 'textarea',
				'conditions'	=> [
					[
						'field'		=> 'repeater_choices',
						'operator'	=> '==',
						'value'		=> 1
					],
					[
						'field'		=> 'repeater_field',
						'operator'	=> '!=empty',
					]
				]
			]);
		}

		// post id
		acf_render_field_setting( $field, [
			'label'			=> __('Post ID','acf-wp-objects'),
			'instructions'	=> __('Leave empty for current Post ID.','acf-wp-objects'),
			'name'			=> 'repeater_post_id',
			'type'			=> 'text',
			'placeholder'	=> __('Current Post','acf-wp-objects'),
			'class'			=> 'code',
			'conditions'	=> [
				'field'		=> 'repeater_choices',
				'operator'	=> '==',
				'value'		=> 1
			]
		]);

	}

	/**
	 *	@filter acf/load_field/type=*
	 */
	public function migrate_field( $field ) {

		$field = wp_parse_args( $field, [
			'repeater_choices'			=> false,
			'repeater_field'			=> '',
			'repeater_label_field'		=> '', // text, num, range, url, email, date, time, datetime, image, color
			'repeater_value_field'		=> '', // text, num, range, url, email, date, time, datetime, image, color
			'repeater_post_id'			=> 0,
			'repeater_display_value'	=> 0, // media | string + media
			'repeater_choice_template'	=> '',
		]);

		// migrate from boolean display value to display field
		if ( $field['repeater_choices'] ) {
			if ( $field['repeater_display_value'] ) {
				unset( $field['repeater_display_value'] );
				$field['repeater_display_field'] = $field['repeater_value_field'];
			}
		}
		return $field;
	}


	/**
	 *	@filter acf/prepare_field/type=*
	 */
	public function prepare( $field ) {

		$field = wp_parse_args( $field, [
			'repeater_choices'			=> false,
			'repeater_field'			=> '',
			'repeater_label_field'		=> '', // text, num, range, url, email, date, time, datetime, image, color
			'repeater_value_field'		=> '', // text, num, range, url, email, date, time, datetime, image, color
			'repeater_post_id'			=> 0,
			'repeater_display_field'	=> '', // media | string + media
			'repeater_choice_template'	=> '',
		]);

		$post_id = $field['repeater_post_id'];

		if ( ! $post_id ) {
			global $plugin_page;
			// get current plugins page post id
			if ( isset( $plugin_page ) && ( $opt_page = acf_get_options_page( $plugin_page ) ) ) {
				$post_id = $opt_page['post_id'];
			}
		}

		if ( $field['repeater_choices'] ) {

			$raw_choices = [];
			$visuals = [];
			$classname = '-repeater-choice';
			if ( '__idx__' === $field['repeater_value_field'] ) {
				$repeater_value_field = [
					'type' => 'index',
					'is_id' => false,
				];
			} else {
				$repeater_value_field = get_field_object( $field['repeater_value_field'] );
				$repeater_value_field = wp_parse_args( $repeater_value_field, [ 'is_id' => false ] );
			}

			if ( $field['repeater_label_field'] ) {
				$repeater_label_field = get_field_object( $field['repeater_label_field'] );
				$repeater_label_field = wp_parse_args( $repeater_label_field, [ 'is_id' => false ] );
			} else {
				$repeater_label_field = false;
			}
			if ( $field['repeater_display_field'] ) {
				$repeater_display_field = get_field_object( $field['repeater_display_field'] );
				$repeater_display_field = wp_parse_args( $repeater_display_field, [ 'is_id' => false ] );
			} else {
				$repeater_display_field = false;
			}

			if ( have_rows( $field['repeater_field'], $post_id ) ) {
				if ( ! isset( $this->repeater_values[ $field['repeater_field'] ] ) ) {
					$this->repeater_values[ $field['repeater_field'] ] = [];
				}
				while ( have_rows( $field['repeater_field'], $post_id ) ) {
					the_row();
					$index = get_row_index() + 1;
					if ( '__idx__' ===  $field['repeater_label_field'] ) {
						$label = $index;
					} else {
						$label = get_sub_field( $field['repeater_label_field'] );
					}

					if ( '__idx__' === $field['repeater_value_field'] ) {
						$value = $label = get_row_index();
					} else {
						$value = get_sub_field( $field['repeater_value_field'] );
					}

					$visual = get_sub_field( $field['repeater_display_field'] );

					$raw_choices[ $value ] = [ 'label' => $label, 'visual' => $visual, 'index' => $index ];
				}
			}

			$raw_choices = apply_filters( 'acf_wpo_repeater_choices', $raw_choices, $field );
			$raw_choices = apply_filters( 'acf_wpo_repeater_choices/key=' . $field['repeater_field'], $raw_choices, $field );
			$raw_choices = apply_filters( 'acf_wpo_repeater_choices/type=' . $field['type'], $raw_choices, $field );

			$choices = [];

			$display_field = acf_get_field( $field['repeater_display_field'] );

			$field_classes = explode( ' ', $field['wrapper']['class'] );
			$field_classes[] = 'repeater-choice-visualize-' . $display_field['type'];
			$field['wrapper']['class'] = implode( ' ', array_unique( $field_classes ) );

			// format choices
			foreach ( $raw_choices as $value => $choice ) {
				/* @vars $label, $visual */
				extract( $choice );
				if ( $field['repeater_display_field'] ) {
					$label = $this->visualize_value(
						$repeater_value_field, $repeater_label_field, $repeater_display_field,
						$value, $choice['label'], $choice['visual'],
						$field,
						$choice['index']
					);
				}
				$key = $value;

				// value might be image object, term object, ...
				if ( is_array( $key ) ) {
					if ( isset($key['ID']) ) {
						$key = $key['ID'];
					}
				}
				// handle text fields with ID option
				if ( 'text' === $repeater_value_field['type'] && $repeater_value_field['is_id'] ) {
					$key = sanitize_title( $key );
					$key = sanitize_key( $key );
				}
				$choices[ $key ] = $label;
			}

			$field['choices'] = $choices;

			// hide and disable field if empty choices
			if ( ! count( $choices ) ) {
				$field['disabled'] = 1; // wont work with select fields, js is interfering
				$field['readonly'] = 1;
				$field['conditional_logic'] = [
					[ 'field' => $field['key'], 'operator' => '!=empty' ],
				]; // this condition is never true
			}
		}
		return $field;
	}


	/**
	 *	Display Value Visualization
	 *
	 *	@param Array $value_field Value field from field settings
	 *	@param Array|Boolean $label_field Label field from field settings
	 *	@param Array|Boolean $display_field Display field from field settings
	 *	@param mixed $value Value of value field
	 *	@param String|Boolean $label Value of label field
	 *	@param mixed $display Value of display field
	 *	@param Array $field This field
	 */
	private function visualize_value( $value_field, $label_field, $display_field, $value, $label, $display, $field, $index ) {

		$html = '';
		$template = '';

		$template_replace = [
			'[index]'			=> $index,
			'[value:value]'		=> $value,
			'[value:value:key]'	=> sanitize_key( sanitize_title( $value ) ),
			'[value:name]'		=> $value_field['name'],
			'[value:label]'		=> $value_field['label'],
			'[value:key]'		=> $value_field['key'],
		];

		if ( is_array( $label_field ) ) {
			$template_replace['[label:value]'] = $label;
			$template_replace['[label:value:key]'] = sanitize_key( sanitize_title( $label ) );
			$template_replace['[label:name]'] = $label_field['name'];
			$template_replace['[label:label]'] = $label_field['label'];
			$template_replace['[label:key]'] = $label_field['key'];

		} else {
			$template_replace['[label:value]'] = '';
			$template_replace['[label:value:key]'] = '';
			$template_replace['[label:name]'] = '';
			$template_replace['[label:label]'] = '';
			$template_replace['[label:key]'] = '';
		}

		if ( is_array( $display_field ) ) {
			$template_replace['[display:value]'] = $display;
			$template_replace['[label:value:key]'] = sanitize_key( sanitize_title( $display ) );
			$template_replace['[display:name]'] = $display_field['name'];
			$template_replace['[display:label]'] = $display_field['label'];
			$template_replace['[display:key]'] = $display_field['key'];
		} else {
			$template_replace['[display:value]'] = '';
			$template_replace['[display:value:key]'] = '';
			$template_replace['[display:name]'] = '';
			$template_replace['[display:label]'] = '';
			$template_replace['[display:key]'] = '';
		}

		$allow_fields = apply_filters('acf_wp_objects_repeater_choices_allow_fields', $this->allow_fields );
		$display_templates = apply_filters('acf_wp_objects_repeater_choices_display_templates', $this->display_templates );

		if ( empty( $field['repeater_choice_template'] ) ) {
			// default behaviour according to $display_field type
			$display_field_type = $allow_fields[ $display_field['type'] ];
			$template = $display_templates[ $display_field_type ];

			if ( 'image' === $display_field_type ) {

				$template_replace['[display:value:image]'] = wp_get_attachment_image( $value, 'thumbnail', null, [ 'title' => $label ] );

			} else if ( 'color' === $display_field_type ) {
				if ( empty( $template_replace['[display:value]'] ) ) {
					$template_replace['[display:value]'] = 'rgba(0,0,0,0)';
				}
				$color = $match_color = $display;
				if ( empty( $value ) ) {
					$color = 'rgba(0,0,0,0)';
					$match_color = '#ffffff';
				}
				$template_replace['[display:value:contrast]'] = $this->get_matching_color( $match_color );

			}

		} else {
			$template = $field['repeater_choice_template'];
		}
		if ( is_array( $template ) ) {
			if ( empty( $label ) ) {
				$template = $template['no-label'];
			} else {
				$template = $template['label'];
			}
		}

		$html = str_replace(
			array_keys( $template_replace ),
			array_values( $template_replace ),
			$template
		);

		// // legacy behaviour
		if ( ! in_array( $display_field['type'], [ 'image', 'text', 'color' ] ) ) {
			$html = apply_filters('acf_wp_objects_repeater_choice_label', $html, $value, $field, $field );
		}

		$html = apply_filters('acf_value_display_html', $html, $value, $field, $field );
		$html = apply_filters('acf_value_display_html/name='.$field['_name'], $html, $value, $field, $field );

		$html = apply_filters('acf_value_display_html/type='.$field['type'], $html, $value, $field, $field );
		$html = apply_filters('acf_value_display_html/key='.$field['key'], $html, $value, $field, $field );

		return $html;

	}

	/**
	 *	@param string $color css color string
	 *	@return string color string
	 */
	private function get_matching_color( $color ) {
		$threshold = 0.33;
		$rgba = $this->parse_color( $color );
		$a = array_pop($rgba);
		$opacity = 1 - ( array_sum($rgba) / 3 ); // 0-3
		$opacity *= $a;
		if ( $opacity > $threshold ) {
			return '#ffffff';
		}
		return '#333333';
	}

	/**
	 *	@param string $color css color string
	 *	@return array rgba
	 */
	private function parse_color( $color ) {
		$do_int = function($i) {
			return floatval($i) / 255;
		};
		$do_percent = function($i) {
			return floatval($i) / 100;
		};
		$do_hex1 = function($i) {
			return intval('0x'.$i,16) / 15;
		};
		$do_hex2 = function($i) {
			return intval('0x'.$i,16) / 255;
		};

		$a = 1;
		if ( preg_match( '/^#([0-9a-f]{1})([0-9a-f]{1})([0-9a-f]{1})$/i', $color, $matches ) ) {
			$rgb = array_map( $do_hex1, $matches );
		} else if ( preg_match( '/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $matches ) ) {
			$rgb = array_map( $do_hex2, $matches );
		} else if ( preg_match( '/^rgb\(([\d]+),([\d]+),([\d]+)\)$/', $color, $matches ) ) {
			$rgb = array_map( $do_int, $matches );
		} else if ( preg_match( '/^rgba\(([\d]+),([\d]+),([\d]+),([\d\.]+)\)$/', $color, $matches ) ) {
			$a = array_pop( $matches );
			$rgb = array_map( $do_int, $matches );
		} else if ( preg_match( '/^rgb\(([\d%]+),([\d%]+),([\d%]+)\)$/', $color, $matches ) ) {
			$rgb = array_map( $do_percent, $matches );
		} else if ( preg_match( '/^rgba\(([\d%]+),([\d%]+),([\d\%]+),([\d\.]+)\)$/', $color, $matches ) ) {
			$a = array_pop($matches);
			$rgb = array_map( $do_percent, $matches );
		}
		$a = floatval( $a );
		$rgba = $rgb;
		$rgba[] = $a;
		return $rgba;
	}

	/**
	 *	@return Array [
	 *		<field_group_title> => [
	 *			<field_key> => <field_label>,
	 *			...
	 *		],
	 *		...
	 *	]
	 */
	private function get_first_level_repeater_fields() {
		if ( is_null( $this->repeater_fields ) ) {
			$groups = [];
			$local_enabled = acf_is_local_enabled();
			if ( ! $local_enabled ) {
				acf_enable_local();
			}
			$acf_groups = acf_get_field_groups();

			foreach ( $acf_groups as $group ) {
				$fields = acf_get_fields( $group );

				if ( ! $fields ) {
					continue;
				}
				foreach ( $fields as $field ) {
					if ( $field['type'] !== 'repeater' ) {
						continue;
					}
					if ( ! isset( $groups[ $group['title'] ] ) ) {
						$groups[ $group['title'] ] = [];
					}
					$groups[ $group['title'] ][ $field['key'] ] = $field['label'];
				}

			}

			if ( ! $local_enabled ) {
				acf_disable_local();
			}
			$this->repeater_fields = $groups;
		}

		return $this->repeater_fields;
	}

	/**
	 *	@return array
	 */
	public function get_repeated_fields() {
		$repeater_groups = $this->get_first_level_repeater_fields();
		$repeated = [];
		$allow_fields = apply_filters( 'acf_wp_objects_repeater_choices_allow_fields', $this->allow_fields );
		$allow_fields = array_keys( $allow_fields );
		foreach ( $repeater_groups as $repeaters ) {
			foreach ( array_keys( $repeaters ) as $repeater_key ) {

				if ( ! $parent_field = acf_get_field( $repeater_key ) ) {
					continue;
				}

				$results = [];
				$repeated[ $parent_field['key'] ][ '__idx__' ] = __( '(Index)', 'acf-wp-objets' );
				foreach ( $parent_field['sub_fields'] as $field ) {
					if ( ! in_array( $field['type'], $allow_fields ) ) {
						continue;
					}
					if ( ! isset( $repeated[ $parent_field['key'] ] ) ) {
						$repeated[ $parent_field['key'] ] = [];
					}
					$repeated[ $parent_field['key'] ][ $field['key'] ] = $field['label'];
				}

			}
		}
		return $repeated;
	}

}
