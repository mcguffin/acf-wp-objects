const matchesRegEx = ( value, regEx ) => {
	const r = new RegExp(regEx)
	return r.test( value )
}

acf.registerConditionType(
	acf.Condition.extend({
		type: 'regex',
		operator: '==regex',
		label: 'Value matches RegExp',
		fieldTypes: ['text', 'textarea', 'number', 'email', 'url', 'password', 'wysiwyg', 'oembed', 'select'],
		match: function (rule, field) {
			return matchesRegEx(field.val(), rule.value);
		},
		choices: function (fieldObject) {
			return '<input type="text" />';
		}
	})
);
acf.registerConditionType(
	acf.Condition.extend({
		type: 'notregex',
		operator: '!=regex',
		label: 'Value does not match RegExp',
		fieldTypes: ['text', 'textarea', 'number', 'email', 'url', 'password', 'wysiwyg', 'oembed', 'select'],
		match: function (rule, field) {
			return ! matchesRegEx(field.val(), rule.value);
		},
		choices: function (fieldObject) {
			return '<input type="text" />';
		}
	})
);
