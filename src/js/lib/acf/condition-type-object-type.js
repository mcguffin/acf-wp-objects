

const objectTypeIs = ( rule, field ) => {
	if ( ! field.getValue() ) {
		return false;
	}
	const types = field.type === 'taxonomy_select'
		? acf_wp_objects.taxonomies
		: acf_wp_objects.post_types

	return !! field.getValue() && !! types[field.getValue()] && types[field.getValue()][rule.value]
}

const choices = () => {
	let ret = '<select>';
	Object.keys(acf_wp_objects.object_type_props).forEach(val=>{
		let label = acf_wp_objects.object_type_props[val]
		ret += `<option value="${val}">${label}</option>`
	})
	ret += '</select>'
	return ret;
}

acf.registerConditionType(
	acf.Condition.extend({
		type: 'objecttypeis',
		operator: '==objecttypeis',
		label: 'Object Type is',
		fieldTypes: ['post_type_select', 'taxonomy_select'],
		match: function (rule, field) {
			return objectTypeIs( rule, field )
		},
		choices: function (fieldObject) {
			console.log(fieldObject)
			return choices()
		}
	})
);
acf.registerConditionType(
	acf.Condition.extend({
		type: 'notobjecttypeis',
		operator: '!=objecttypeis',
		label: 'Object Type is not',
		fieldTypes: ['post_type_select', 'taxonomy_select'],
		match: function (rule, field) {
			if ( ! field.getValue() ) {
				return true;
			}
			const types = field.type === 'taxonomy_select'
				? acf_wp_objects.taxonomies
				: acf_wp_objects.post_types
			return ! objectTypeIs( types[field.getValue()], rule.value )
		},
		choices: function (fieldObject) {
			return choices()
		}
	})
);
