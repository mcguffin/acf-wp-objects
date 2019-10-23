import $ from 'jquery';


[
	'image_size_select',
	'post_type_select',
	'taxonomy_select'
].forEach(
	type => {
		const t = acf.getFieldType('select').extend( {
			type,
		} );
		console.log('ADD TYPES');
		acf.registerFieldType( t );
	}
);

acf.addFilter('select2_args',( options, $select, data, field, self ) => {
	console.log(field.get('type'))
	return options;
});
