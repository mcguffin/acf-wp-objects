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
			html += '<option value="'+val+'">' + label + '</option>';
		});

		// setup options
		$(this).closest('.acf-field-settings')
			.find('[data-key="repeater_label_field"] select,[data-key="repeater_value_field"] select')
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
