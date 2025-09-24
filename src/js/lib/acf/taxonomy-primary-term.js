const fieldType = acf.getFieldType('taxonomy')
const originalAppendTermCheckbox = fieldType.prototype.appendTermCheckbox

acf.registerFieldType(fieldType.extend({
	appendTermCheckbox: function() {
		setTimeout(() => {
			if ( this.get('primary_term')) {
				const template = this.$('template').html();
				console.log(this.$('ul').get(0).querySelectorAll('li:not(:has(.primary-term))'))
				this.$('ul').get(0).querySelectorAll('li:not(:has(.primary-term))').forEach( el => {
					el.innerHTML += template.replace('{term_id}',el.querySelector('[type="checkbox"]').value)
				})
			}
		},10);
		return originalAppendTermCheckbox.apply(this,arguments)
	}
}))
