<?php


namespace ACFWPObjects\Compat\ACF;

use ACFWPObjects\Core;

class RepeaterChoices extends Core\Singleton {

	private $allow_fields = array(
		'text'			=> 'text',
		'num'			=> 'text',
		'range'			=> 'text',
		'url'			=> 'text',
		'email'			=> 'text',
		'date'			=> 'text',
		'time'			=> 'text',
		'datetime'		=> 'text',
		'image'			=> 'image',
		'color_picker'	=> 'color',
		'select'		=> 'text'
	);
	private $repeater_fields = null;

	/**
	 *	@inheritdoc
	 */
	protected function __construct( ) {

		add_filter( 'acf/prepare_field/type=select', array( $this, 'prepare' ) );
		add_filter( 'acf/prepare_field/type=radio', array( $this, 'prepare' ) );
		add_filter( 'acf/prepare_field/type=button_group', array( $this, 'prepare' ) );
		add_filter( 'acf/prepare_field/type=checkbox', array( $this, 'prepare' ) );

		add_filter( 'acf/render_field_settings/type=select', array( $this, 'render_settings' ) );
		add_filter( 'acf/render_field_settings/type=radio', array( $this, 'render_settings' ) );
		add_filter( 'acf/render_field_settings/type=button_group', array( $this, 'render_settings' ) );
		add_filter( 'acf/render_field_settings/type=checkbox', array( $this, 'render_settings' ) );

		add_action( 'admin_print_scripts', array($this,'add_script'));
		add_action( 'acf/register_scripts', array($this,'add_styles'));
	}


	/**
	 *	@filter acf/render_field_settings/type=*
	 */
	public function render_settings( $field ) {

		$repeater_field_choices = $this->get_first_level_repeater_fields();
		$value_field_choices = $label_field_choices = $this->get_repeated_fields( $repeater_field_choices );

		// enable
		acf_render_field_setting( $field, array(
			'label'			=> __('Get choices from repeater','acf-wp-objects'),
			'instructions'	=> '',
			'name'			=> 'repeater_choices',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));

		// repeater field
		acf_render_field_setting( $field, array(
			'label'			=> __('Repeater Field','ternum-ds'),
			'instructions'	=> '',
			'name'			=> 'repeater_field',
			'type'			=> 'select',
			'ui'			=> 0,
			'allow_null'	=> 1,
			'choices'		=> $repeater_field_choices,
			'conditions'	=> array(
				'field'		=> 'repeater_choices',
				'operator'	=> '==',
				'value'		=> 1
			)
		));

		// label field
		acf_render_field_setting( $field, array(
			'label'			=> __('Label Field','ternum-ds'),
			'instructions'	=> '',
			'name'			=> 'repeater_label_field',
			'type'			=> 'select',
			'ui'			=> 0,
			'allow_null'	=> 1,
			'choices'		=> $label_field_choices,
			'conditions'	=> array(
				array(
					'field'		=> 'repeater_choices',
					'operator'	=> '==',
					'value'		=> 1
				),
				array(
					'field'		=> 'repeater_field',
					'operator'	=> '!=empty',
				),
			)
		));

		// value field
		acf_render_field_setting( $field, array(
			'label'			=> __('Value Field','ternum-ds'),
			'instructions'	=> '',
			'name'			=> 'repeater_value_field',
			'type'			=> 'select',
			'ui'			=> 0,
			'choices'		=> $value_field_choices,
			'conditions'	=> array(
				array(
					'field'		=> 'repeater_choices',
					'operator'	=> '==',
					'value'		=> 1
				),
				array(
					'field'		=> 'return_format',
					'operator'	=> '!=',
					'value'		=> 'label'
				),
				array(
					'field'		=> 'repeater_field',
					'operator'	=> '!=empty',
				),
			)
		));

		// post id
		acf_render_field_setting( $field, array(
			'label'			=> __('Post ID','ternum-ds'),
			'instructions'	=> __('Leave empty for current Post ID.','ternum-ds'),
			'name'			=> 'repeater_post_id',
			'type'			=> 'text',
			'placeholder'	=> __('Current Post','ternum-ds'),
			'class'			=> 'code',
			'conditions'	=> array(
				'field'		=> 'repeater_choices',
				'operator'	=> '==',
				'value'		=> 1
			)
		));
		if ( $field['type'] !== 'select' ) {
			// enable
			acf_render_field_setting( $field, array(
				'label'			=> __('Visualize value','ternum-ds'),
				'instructions'	=> '',
				'name'			=> 'repeater_display_value',
				'type'			=> 'true_false',
				'ui'			=> 1,
				'conditions'	=> array(
					'field'		=> 'repeater_choices',
					'operator'	=> '==',
					'value'		=> 1
				),
			));
		}

	}

	/**
	 *	@filter acf/prepare_field/type=*
	 */
	public function prepare( $field ) {

		$field = wp_parse_args( $field, array(
			'repeater_choices'			=> false,
			'repeater_field'			=> '',
			'repeater_label_field'		=> '', // text, num, range, url, email, date, time, datetime, image, color
			'repeater_value_field'		=> '', // text, num, range, url, email, date, time, datetime, image, color
			'repeater_post_id'			=> 0,
			'repeater_display_value'	=> 0, // media | string + media
		));

		if ( $field['repeater_choices'] && have_rows( $field['repeater_field'], $field['repeater_post_id'] ) ) {
			$choices = array();
			while ( have_rows( $field['repeater_field'], $field['repeater_post_id'] ) ) {
				the_row();
				$label = get_sub_field( $field['repeater_label_field'] );
				$value = get_sub_field( $field['repeater_value_field'] );
				if ( $field['repeater_display_value'] ) {
					$label = $this->get_value_display( $field['repeater_value_field'], $label, $value );
				}
				$choices[ $value ] = $label;
			}
			$field['choices'] = $choices;
		}
		return $field;
	}

	private function get_value_display( $field_key, $label, $value ) {

		$field = acf_get_field( $field_key );
		$html = '';

		$allow_fields = apply_filters('acf_wp_objects_repeater_choices_allow_fields', $this->allow_fields );
		if ( ! isset( $allow_fields[ $field['type'] ] ) ) {
			$allow_fields[ $field['type'] ] = 'text';
		}

		$treat_as = $allow_fields[ $field['type'] ];

		switch ( $allow_fields[ $field['type'] ] ) {
			case 'image';
				$html = sprintf('<span class="acf-image-choice">
						%s
						<span class="image-label">%s</span>
					</span>',
					wp_get_attachment_image($value,'thumbnail', null, array( 'title' => $label ) ),
					$label
				);
				break;
			case 'color':
				if ( ! empty( $value ) ) {

					$html = sprintf('
						<span class="white"></span>
						<span class="color-label" style="color:%s;background:%s;">
							%s
						</span>',
						$this->get_matching_color($value),
						$value,
						$label
					);
				}
				break;
			case 'text':
				$html = $label;
				break;
			default:
				$html = apply_filters('acf_wp_objects_repeater_choice_label', $label, $value, $field );
		}
		return apply_filters('acf_value_display_html', $html, $value, $field );
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
	 *	@return array
	 */
	private function get_first_level_repeater_fields() {
		if ( is_null( $this->repeater_fields ) ) {
			$groups = array();
			$acf_groups = acf_get_field_groups();
			foreach ( $acf_groups as $group ) {
				$fields = acf_get_fields( $group );
				foreach ( $fields as $field ) {
					if ( $field['type'] !== 'repeater' ) {
						continue;
					}
					if ( ! isset( $groups[ $group['title'] ] ) ) {
						$groups[ $group['title'] ] = array();
					}
					$groups[ $group['title'] ][ $field['key'] ] = $field['label'];
				}
			}
			$this->repeater_fields = $groups;
		}

		return $this->repeater_fields;
	}

	/**
	 *	@return array
	 */
	private function get_repeated_fields(  ) {
		$repeater_groups = $this->get_first_level_repeater_fields();
		$repeated = array();
		$allow_fields = apply_filters('acf_wp_objects_repeater_choices_allow_fields', $this->allow_fields );
		$allow_fields = array_keys( $allow_fields );
		foreach ( $repeater_groups as $repeaters ) {
			foreach ( array_keys( $repeaters ) as $repeater_key ) {

				if ( ! $parent_field = acf_get_field( $repeater_key ) ) {
					continue;
				}

				$results = array();

				foreach ( $parent_field['sub_fields'] as $field ) {
					if ( ! in_array( $field['type'], $allow_fields ) ) {
						continue;
					}
					if ( ! isset( $repeated[ $parent_field['key'] ] ) ) {
						$repeated[ $parent_field['key'] ] = array();
					}
					$repeated[ $parent_field['key'] ][ $field['key'] ] = $field['label'];
				}

			}
		}
		return $repeated;
	}


	/**
	 *	@action admin_print_scripts
	 */
	public function add_script() {
		$repeated_fields = $this->get_repeated_fields();
		$repeated_fields_json = json_encode( $repeated_fields );
		$js = <<<EOD

(function($){

	var repeated_fields = {$repeated_fields_json},
		selector = '[data-key="repeater_field"] select';

	// reduce value & label field choices when repeater field changes
	$(document).on( 'change', selector, function(e){
		if ( !! repeated_fields[ $(this).val() ] ) {
			var html = '',
				repeater = $(this).val();
			/*
			$(this).closest('.acf-field-settings')
				.find('[data-key="repeater_label_field"] select,[data-key="repeater_value_field"] select')
				.each(function(i,el){
					$(this).find('optgroup').each(function(i,el){
						if ( $(this).is('[label="'+repeater+'"]') ) {

						}

					})
				});
			/*/
			$.each(repeated_fields[ repeater ],function( val, label ){
				html += '<option value="'+val+'">' + label + '</option>';
			});
			$(this).closest('.acf-field-settings')
				.find('[data-key="repeater_label_field"] select,[data-key="repeater_value_field"] select')
				.each(function(i,el){
					var val = $(this).val(),
						choiceNull = $(this).find('option[value=""]'),
						field = acf.getField($(this).closest('.acf-field'));

					$(this).html(html);
					if ( choiceNull.length ) {
						$(this).prepend( choiceNull );
					}
					$(this).val( val );

				});
			//*/

		}
	}).ready(function(){
		$(selector).trigger('change');
	});
})(jQuery);
EOD;
		wp_add_inline_script( 'acf-field-group', $js );
	}
	/**
	 *	@action acf/register_scripts
	 */
	public function add_styles() {
		$css = <<<EOD
.color-label {
	display:inline-block;
	padding:0 0.25em;
	background:#fff;
	position:relative;
}
.acf-button-group .color-label {
	padding:4px 9px;
	margin:-4px -9px;
	width:100%;
	text-align:left;
}

.acf-button-group .color-label::before {
	content: "\\f460";
	font-family: dashicons;
	font-size:20px;
	display:inline-block;
	margin:-5px 5px;
	vertical-align:middle;
}
.acf-button-group .selected {
	position:relative;
}
.acf-button-group .white  {
	position:absolute;
	display:block;
	left:2px;
	top:2px;
	bottom:2px;
	right:2px;
	background:#fff;
	background-image:	linear-gradient(45deg, #ededed 25%, transparent 25%),
						linear-gradient(-45deg, #ededed 25%, transparent 25%),
						linear-gradient(45deg, transparent 75%, #ededed 75%),
						linear-gradient(-45deg, transparent 75%, #ededed 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;

}
.acf-button-group .selected .color-label {
	opacity:1;
}
.acf-button-group .selected .color-label::before {
	content: "\\f147";
	opacity:1;
}


.acf-button-group .size-thumbnail {
	height: 100%;
	object-fit: none;
}
.acf-image-choice {
	position:relative;
	display:inline-block;
}
.acf-image-choice .image-label {
	display: flex;
	position: absolute;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	padding: 15px;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	text-shadow: 1px 1px 4px #333,
		-1px -1px 4px #333;
	font-size: 16px;
	color: #fff;
}
EOD;
		wp_add_inline_style( 'acf-input', $css );
	}


}
