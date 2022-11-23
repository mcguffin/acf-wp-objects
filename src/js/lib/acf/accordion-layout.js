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

// acf.addAction('ready', doAccordion )

acf.addAction( 'after_duplicate', ($original,$duplicate) => {
	setTimeout( () => doAccordion($duplicate), 1 )
});

const FlexField =

acf.registerFieldType(

	acf.getFieldType('flexible_content').extend({
		addCollapsed: function() {
			const pref = acf.getPreference('this.collapsedLayouts')
			let indices = [],
				open = false,
				key = this.get('key');

			if ( !! pref && !! pref[key] ) {
				indices = pref[key]
			}

			this.$layouts().each(function (i) {
				if ( open || ( indices.indexOf(i) > -1 ) ) {
					$(this).addClass('-collapsed');
				} else {
					open = true
				}
			});
		},
	})
);
