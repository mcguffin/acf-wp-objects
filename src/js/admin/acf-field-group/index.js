import $ from 'jquery';


const repeated_fields = acf_wp_objects.repeated_fields;
const selector = '[data-key="repeater_field"] select';

// reduce value & label field choices when repeater field changes
const setupRepeaterChoices = function() {

	if ( !! repeated_fields[ $(this).val() ] ) {

		const repeater = $(this).val();

		let html = ''

		// generate options
		$.each( repeated_fields[ repeater ], ( val, label ) => {
			html += `<option value="${val}">${label}</option>`;
		});

		// setup options
		$(this).closest('.acf-field-settings')
			.find('[data-key="repeater_label_field"] select,[data-key="repeater_value_field"] select,[data-key="repeater_display_field"] select')
			.each( ( i, el ) => {
				const $el = $(el);
				const val = $el.val();
				const choiceNull = $el.find('option[value=""]');
				const field = acf.getField( $el.closest('.acf-field') );

				$el.html(html);
				if ( choiceNull.length ) {
					$el.prepend( choiceNull );
				}
				$el.val( val );
			});
	}
}

$(document)
	.on( 'change', selector, setupRepeaterChoices )
	.ready(function(){
		$(selector).each( setupRepeaterChoices );
	});





//acf.FieldObject.prototype.$setting
acf.addAction('new_field_object',field => {

	const type = field.get('type');
	const wp_objects_fields = {
		'image_size_select' : {
			'pick_input_name':'image_sizes',
			'filter_props'	: [ '_builtin', 'crop', 'named' ],
		},
		'post_type_select' : {
			'pick_input_name':'post_types',
			'filter_props'	: [ '_builtin', 'public', 'show_in_menu', 'show_in_nav_menus', 'show_ui' ],
		},
		'taxonomy_select' : {
			'pick_input_name':'taxonomies',
			'filter_props'	: [ '_builtin', 'public', 'show_in_menu', 'show_in_nav_menus', 'show_ui' ],
		},
	};


	if ( !! wp_objects_fields[ type ] ) {

		const typedef = wp_objects_fields[ type ];
		const orig_setting = field.$setting;

		/**
		 *	Override field.$setting method
		 *
		 *	@return jquery object  with val() returning an ACF choices string
		 */
		field.$setting = function( name ) {
			if ( 'choices textarea' === name ) {
				const $inp = $('<textarea />');
				const choices = [];
				if ( this.$input('pick').prop( 'checked' ) ) {
					this.$input( typedef.pick_input_name ).find(':selected').each( (i,el) => {
						choices.push( [ $(el).val(), $(el).text() ] );
					});
				} else {
					// very type specific...
					choices.push( ... Object.values(acf_wp_objects[ typedef.pick_input_name ]).filter( el => {
							try {
								typedef.filter_props.forEach( prop => {
									// get val
									let val = this.$( `[data-name="${prop}"] [type="radio"]:checked` ).val();
									// don't care
									if ( '' === val ) {
										return;
									}
									// break loop if condition fails!
									if ( el[prop] !== ( val === '1' ) ) {
										throw('');
									}
								});
								return true;
							} catch (err) {
								return false;
							}
						}).map( entry => [ entry.name, entry.label ] )
					);
				}

				$inp.val( choices.map( entry => entry.join(' : ') ).join("\n") )
				return $inp;
			}
			return orig_setting.apply( this, arguments );
		}
	}
});
