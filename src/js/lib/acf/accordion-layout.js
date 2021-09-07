import $ from 'jquery';


const doAccordion = $layout => {
	let $field, field;
	if ( ! $layout.is('.layout') ) {
		return;
	}

	$field = $layout.closest('.acf-field-flexible-content')

	if ( ! $field.is('.acf-accordion-layout') ) {
		return;
	}
	field = acf.getField($field)

	field.$layouts().each( (i,el) => {
		if ( ! $(el).is($layout) ) {
			field.closeLayout($(el))
		}
	} );
}

acf.addAction('show', doAccordion )

acf.addAction( 'after_duplicate', ($original,$duplicate) => {
	setTimeout( () => doAccordion($duplicate), 1 )
});
