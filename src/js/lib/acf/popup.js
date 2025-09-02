import $ from 'jquery';

const previewFunctions = {
	group: {
		storeValue: function() {
			this._prevValue = {}
			this.subFields().forEach( field => {
				this._prevValue[field.get('name')] = field.val();
			})
		},
		createPreview: function() {
			const previewNode = document.createElement('div')
			let preview = this.previewTemplate();
			this.subFields().forEach( field => {
				preview = preview.replaceAll(`{${field.get('name')}}`,field.val());
			})
			previewNode.innerHTML = preview
			return previewNode
		},
		resetValue: function() {
			this.subFields().forEach( field => {
				field.val( this._prevValue[field.get('name')] );
			})
		},
		subFields: function() {
			return Array.from(this.$el.get(0).querySelectorAll('.acf-dialog-body > .acf-fields > .acf-field')).map( el => acf.getField($(el)) )
		},
		previewTemplate: function() {
			return this.$('template').get(0).innerHTML;
		}
	},
	radio: {
		events: {
			'click [type="radio"]': 'closeEditor'
		},
		createPreview: function() {
			const preview = document.createElement('div')
			const value   = this.$el.get(0).querySelector('.acf-radio-list label.selected')
			Array.from(value.childNodes).forEach( el => {
				const clone = el.cloneNode(true)
				if ( !! el.matches && el.matches('input')) {
					clone.removeAttribute('name')
					clone.classList.add('acf-hidden')
				}
				preview.append(clone)
			})
			return preview;
		}
	},
	wysiwyg: {
		createPreview: function() {
			const preview = document.createElement('div')
			preview.innerHTML = wp.autop.autop(this.val())
			return preview
		}
	}
};

['group','radio','wysiwyg'].forEach( type => {
	const fieldType = acf.getFieldType(type)||acf.Field.extend({type})

	// const events = Object.assign({}, fieldType.prototype.events, {
	// 	'click [data-name="preview-edit"]': 'openEditor',
	// 	'click [data-name="preview-edit-finish"]': 'closeEditor',
	// 	'click [data-name="preview-edit-reset"]': 'resetEditor',
	// }, previewFunctions[type].events??{} );

	const events = Object.assign(
		{},
		fieldType.prototype.events,
		{
			'click [data-name="preview-edit"]': 'openEditor',
			'click [data-name="preview-edit-finish"]': 'closeEditor',
			'click [data-name="preview-edit-reset"]': 'resetEditor',
		},
		previewFunctions[type].events||{}
	);

	acf.registerFieldType(fieldType.extend(
		Object.assign(
			{
				editor: function() {
					return this.$('dialog').get(0);
				},
				openEditor: function(e) {
					this.storeValue()
					if ( 'wysiwyg' === this.get('type') ) {
						this.editor().show(); // cant show modal with rte. hides tinymce dialogs.
					} else {
						this.editor().showModal();
					}
					this.editor().addEventListener('close', e => {
						if ( 'true' === this.editor().returnValue ) {
							// update preview
							this.updatePreview()
						} else {
							this.resetValue()
						}
					})
				},
				resetEditor: function(e) {
					this.editor().close('');
				},
				closeEditor: function(e) {
					this.editor().close('true');
				},
				storeValue: function() {
					this._prevValue = this.val()
				},
				resetValue: function() {
					this.val(this._prevValue)
				},
				updatePreview: function() {
					const previewContainer = this.$el.get(0).querySelector('[data-name="preview-edit"]')
					const preview = this.createPreview()
					previewContainer.innerHTML = '';
					previewContainer.appendChild(preview)
				}
			},
			previewFunctions[type],
			{ events }
		)
	))
})
