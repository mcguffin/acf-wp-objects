import $ from 'jquery';
import { fillTemplate } from '../template';

const previewFunctions = {
	group: {
		storeValue: function() {
			this._prevValue = {}
			this.subFields().forEach( field => {
				this._prevValue[field.get('name')] = field.val();
			})
		},
		createPreview: function() {
			const previewNode  = document.createElement('div')
			const templateVars = {}
			this.subFields().forEach( field => {
				templateVars[field.get('name')] = field.val()
			})
			previewNode.innerHTML = fillTemplate(templateVars,this.previewTemplate())
			return Array.from(previewNode.childNodes)
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
			'click [type="radio"]': 'maybeCloseEditor'
		},
		maybeCloseEditor: function(e) {
			this.editor() && this.hideEditor('true');
		},
		createPreview: function() {
			const value   = this.$el.get(0).querySelector('.acf-radio-list label.selected')
			return Array.from(value.childNodes).map( el => {
				const clone = el.cloneNode(true)
				if ( !! el.matches && el.matches('input')) {
					clone.removeAttribute('name')
					clone.classList.add('acf-hidden')
				}
				return clone;
			})
		}
	},
	wysiwyg: {
		createPreview: function() {
			const preview = document.createElement('div')
			preview.innerHTML = wp.editor.autop(this.val())
			return preview.childNodes;
		},
		showEditor: function() {
			this.initObserver()
			this.editor().showModal();
			this.observe()
		},
		hideEditor: function(response='') {
			this.unobserve()
			this.editor().close(response);
		},
		initObserver: function() {
			if ( !! this.domObserver ) {
				return
			}

			this.elObserver = new MutationObserver( records => {
				const rect = this.editor().getBoundingClientRect()
				for (const record of records) {
					for (const addedNode of record.addedNodes) {
						if ( addedNode.matches('body > :where(.mce-tooltip,.mce-floatpanel,mce-notification):not(.mce-window)')) {
							addedNode.style.transform = `translate(-${rect.x}px,-${rect.y}px)`
							this.editor().append(addedNode)
						} else if ( addedNode.matches('body > .mce-window')) {
							this.editor().append(addedNode)
						}
					}
				}
			})
			this.mceDialogs = Array.from(document.querySelectorAll('body > :where(.mce-menu,.mce-toolbar-grp)'))
			return this
		},

		observe: function() {
			const rect = this.editor().getBoundingClientRect()
			this.elObserver.observe(document.querySelector('body'),{childList:true})
			this.mceDialogs.forEach( el => {
				el.style.transform = `translate(-${rect.x}px,-${rect.y}px)`
				this.editor().append(el)
			})
		},
		unobserve: function() {
			this.elObserver.disconnect()
			this.mceDialogs.forEach( el => {
				el.style.transform = 'translate(0px)'
				document.body.append(el)
			})
		}
	}
};

['group','radio','wysiwyg'].forEach( type => {
	const fieldType = acf.getFieldType(type)||acf.Field.extend({type})

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
				showEditor: function() {
					this.editor().showModal();
				},
				hideEditor: function(response='') {
					this.editor().close(response);
				},
				openEditor: function(e) {
					this.storeValue()
					this.showEditor()
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
					this.hideEditor();
				},
				closeEditor: function(e) {
					this.hideEditor('true');
				},
				storeValue: function() {
					this._prevValue = this.val()
				},
				resetValue: function() {
					this.val(this._prevValue)
				},
				updatePreview: function() {
					const previewContainer = this.$el.get(0).querySelector('[data-name="preview-edit"] .acf-field-preview')
					const preview = this.createPreview()
					previewContainer.innerHTML = '';
					preview.forEach( el => previewContainer.append(el) )
				}
			},
			previewFunctions[type],
			{ events }
		)
	))
})
