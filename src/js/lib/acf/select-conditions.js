const selectField = acf.getFieldType('select');
// make select2 work
[
	'image_size_select',
	'nav_menu_select_select',
	'nav_menu_location_select',
	'post_type_select',
	'role_select',
	'taxonomy_select'
].forEach(
	type => {

		const t = selectField.extend( {
			type,
		} );
		acf.registerFieldType( t );

		acf.registerConditionForFieldType( 'hasValue', type );
		acf.registerConditionForFieldType( 'hasNoValue', type );
		acf.registerConditionForFieldType( 'contains', type );
		acf.registerConditionForFieldType( 'selectEqualTo', type );
		acf.registerConditionForFieldType( 'selectNotEqualTo', type );
		acf.registerConditionForFieldType( 'selectionLessThan', type );
		acf.registerConditionForFieldType( 'selectionGreaterThan', type );

	}
);
