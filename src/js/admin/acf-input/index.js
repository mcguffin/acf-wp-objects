import 'acf/field-sweet-spot';
import 'acf/select-conditions';
import 'acf/id-field';
import 'acf/radio-popup';
import 'acf/select-nav-menu';
import scrollIntoView from 'scroll-into-view-if-needed'
import $ from 'jquery';
import 'acf/accordion-layout';
import 'acf/tab-url-navigation';

// repeater field deny sorting
const RepeaterField = acf.getFieldType('repeater')
if ( RepeaterField ) {
	// wrap around original addSortable function
	const origSortable = RepeaterField.prototype.addSortable
	RepeaterField.prototype.addSortable = function() {
		if ( this.$el.hasClass( 'deny-sort') || this.$el.hasClass( 'no-sort') ) {
			return;
		}
		return origSortable.apply( this, arguments );
	}

}

// function maybeCloseOtherLayouts($layout) {
// 	const $field = $layout.closest('.acf-field-flexible-content');
// 	if ( ! $field.is('.accordion') ) {
// 		return;
// 	}
// 	const rowId = $layout.attr('data-id')
// 	const field = acf.getField( $field.attr('data-key'))
//
// 	field.$layouts().each((i,el) => {
// 		const $layout = $(el);
// 		if ( rowId !== $layout.attr('data-id') ) {
// 			if ( ! field.isLayoutClosed( $layout ) ) {
// 				field.closeLayout( $layout );
// 			}
// 		} else {
// 			if ( field.isLayoutClosed( $layout ) ) {
// 				field.openLayout( $layout );
// 			}
// 		}
// 	});
// 	setTimeout(()=>{
// 		scrollIntoView($layout.get(0), {
// 			behavior: 'smooth',
// 			block: 'start',
// 			scrollMode: 'if-needed',
// 		} );
// 	},25);
// }
//
// // accordion layout field
// acf.addAction('show', function($layout) {
// 	maybeCloseOtherLayouts($layout)
// })
//
// const prevAdd = acf.models.FlexibleContentField.prototype.add;
// const prevDuplicateLayout = acf.models.FlexibleContentField.prototype.duplicateLayout;
// acf.models.FlexibleContentField.prototype.add = function() {
// 	const $el = prevAdd.apply( this, arguments );
// 	maybeCloseOtherLayouts($el)
// 	return $el;
// }
// acf.models.FlexibleContentField.prototype.duplicateLayout = function() {
// 	const $el = prevDuplicateLayout.apply( this, arguments );
// 	console.log($el)
// 	setTimeout(function(){
// 		maybeCloseOtherLayouts($el)
// 	},50)
//
// 	return $el;
// }

//
// $(document).on('change','.acf-field-flexible-content.accordion > .acf-input > .acf-flexible-content > input',e => {
// 	const $layout = $(e.target).nextSibling('.values').find('> .layout.');
// 	// setTimeout( function() {
// 	// 	maybeCloseOtherLayouts($layout)
// 	// }, 10 );
// });
