import $ from 'jquery';
import { fillTemplate } from '../template';

const fieldMapping = (fieldEl,field) => {
	const mapping = {}

	Array.from(fieldEl.querySelectorAll('.acf-field')).map( el => {
		const subField = acf.getField($(el))
		const path  = []
		let up      = subField
		let i=10
		while ( up && up !== field && !! up.get('name') ) {
			path.unshift(up.get('name'))
			up = up.parent()
		}
		mapping[path.join('.')] = subField
	} )
	return mapping
}

const groupFunctions = {
	storeValue: function(e) {
		this._prevValue = {}
		this.fieldMapping(e).map( ([name,field]) => {
			if ( 'val' in field ) {
				this._prevValue[name] = field.val();
			}
		})
		console.log(this.fieldMapping(e),this._prevValue)
	},
	createPreview: function(e) {
		const previewNode  = document.createElement('div')
		const templateVars = {}
		this.fieldMapping(e).map( ([name,field]) => {
			templateVars[name] = field.val()
		})
		previewNode.innerHTML = fillTemplate(templateVars,this.previewTemplate())
		return Array.from(previewNode.childNodes)
	},
	resetValue: function(e) {
		this.fieldMapping(e).map( ([name,field]) => {
			field.val( this._prevValue[name] );
		})
	},
	subFields: function() {
		return Array.from(this.$el.get(0).querySelectorAll('.acf-dialog-body > .acf-fields > .acf-field')).map( el => acf.getField($(el)) )
	},
	previewTemplate: function(e) {
		return this.$('template').get(0).innerHTML;
	}
}
const previewFunctions = {
	repeater: Object.assign({
		editor: function(e) {
			return e.target.closest('.acf-row').querySelector('dialog');//  this.$('dialog').get(0);
		},
		fieldMapping: function(e) {
			return Object.entries(fieldMapping(e.target.closest('.acf-row'),this));
		},
		previewContainer: function(e) {
			return e.target.closest('.acf-row').querySelector('[data-name="preview-edit"] .acf-field-preview');
		},
	}, groupFunctions),
	group: Object.assign({
		fieldMapping: function(e) {
			return Object.entries(fieldMapping(this.$el.get(0),this));
		},
	},groupFunctions),
	radio: {
		events: {
			'click [type="radio"]': 'maybeCloseEditor'
		},
		maybeCloseEditor: function(e) {
			this.editor(e) && this.hideEditor(e,'true');
		},
		createPreview: function(e) {
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
		createPreview: function(e) {
			const preview = document.createElement('div')
			preview.innerHTML = wp.editor.autop(this.val())
			return preview.childNodes;
		},
		showEditor: function(e) {
			this.initObserver(e)
			this.editor(e).showModal();
			this.observe(e)
		},
		hideEditor: function(e,response='') {
			this.unobserve(e)
			this.editor(e).close(response);
		},
		initObserver: function(e) {
			if ( !! this.domObserver ) {
				return
			}

			this.elObserver = new MutationObserver( records => {
				const rect = this.editor(e).getBoundingClientRect()
				for (const record of records) {
					for (const addedNode of record.addedNodes) {
						if ( addedNode.matches('body > :where(.mce-floatpanel,mce-notification):not(.mce-window)')) {
							this.editor(e).append(addedNode)
						} else if ( addedNode.matches('body > .mce-tooltip')) {
							addedNode.style.transform = `translate(-${rect.x}px,-${rect.y}px)`
							this.editor(e).append(addedNode)
						}
					}
				}
			})
			this.mceDialogs = Array.from(document.querySelectorAll('body > :where(.mce-menu,.mce-toolbar-grp)'))
			return this
		},

		observe: function(e) {
			const rect = this.editor(e).getBoundingClientRect()
			this.elObserver.observe(document.querySelector('body'),{childList:true})
			this.mceDialogs.forEach( el => {
				// el.style.transform = `translate(-${rect.x}px,-${rect.y}px)`
				el.style.transform = `translateY(-${window.scrollY}px)`
				this.editor(e).append(el)
			})
		},
		unobserve: function(e) {
			this.elObserver.disconnect()
			this.mceDialogs.forEach( el => {
				el.style.transform = 'translateY(0px)'
				document.body.append(el)
			})
		}
	}
};

['group','radio','wysiwyg','repeater'].forEach( type => {
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
				editor: function(e) {
					return this.$('dialog').get(0);
				},
				showEditor: function(e) {
					this.editor(e).showModal();
				},
				hideEditor: function(e,response='') {
					this.editor(e).close(response);
				},
				openEditor: function(e) {
					this.storeValue(e)
					this.showEditor(e)
					this.editor(e).addEventListener('close', ee => {
						console.log()
						if ( 'true' === this.editor(e).returnValue ) {
							// update preview
							this.updatePreview(e)
						} else {
							this.resetValue(e)
						}
					})
				},
				resetEditor: function(e) {
					this.hideEditor(e);
				},
				closeEditor: function(e) {
					console.log(e)
					this.hideEditor(e,'true');
				},
				storeValue: function(e) {
					this._prevValue = this.val()
				},
				resetValue: function(e) {
					this.val(this._prevValue)
				},
				previewContainer: function(e) {
					return this.$el.get(0).querySelector('[data-name="preview-edit"] .acf-field-preview');
				},
				updatePreview: function(e) {
					const previewContainer = this.previewContainer(e)
					const preview = this.createPreview(e)
					previewContainer.innerHTML = '';
					preview.forEach( el => previewContainer.append(el) )
				}
			},
			previewFunctions[type],
			{ events }
		)
	))
})
