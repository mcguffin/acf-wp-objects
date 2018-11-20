(function($){

	var repeated_fields = acf_wp_objects.repeated_fields,
		selector = '[data-key="repeater_field"] select';

	// reduce value & label field choices when repeater field changes
	function setupRepeaterChoices( ) {
		if ( !! repeated_fields[ $(this).val() ] ) {
			var html = '',
				repeater = $(this).val();

			// generate options
			$.each(repeated_fields[ repeater ],function( val, label ){
				html += '<option value="'+val+'">' + label + '</option>';
			});

			// setup options
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
		}
	}
	$(document)
		.on( 'change', selector, setupRepeaterChoices )
		.ready(function(){
			$(selector).each( setupRepeaterChoices );
		});
})(jQuery);
