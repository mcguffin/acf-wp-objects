import $ from 'jquery';

$(document).on('click', '.acf-field-radio.acf-popup > .acf-input label', e => {
	// open popup
	const field    = e.target.closest('.acf-field');
	const acfInput = field.querySelector('.acf-input')
	const acfLabel = field.querySelector('.acf-label label')

	let label, dataName
	if ( field.matches('td') ) { // acf-table
		dataName = field.getAttribute('data-name')
		label = field.closest('table').querySelector(`thead th[data-name="${dataName}"]`).textContent
	} else {
		label = field.querySelector('.acf-label label').textContent
	}
	const reset = () => {
		while ( ! $(acfInput).parent().is('.acf-field') ) {
			$(acfInput).siblings().remove()
			$(acfInput).unwrap()
		}
		acfLabel && $(acfLabel).css( { 'padding-bottom': '' } );
		$(document).off('keyup',escReset)
		$('body').toggleClass( 'acf-popup-open', false );
	}
	const escReset = e => {
		if (e.keyCode === 27 ) {
			reset()
		}
	}
	$('body').toggleClass( 'acf-popup-open', true );
	acfLabel && $(acfLabel).css('padding-bottom',$(acfInput).height()+'px')
	$(acfInput).wrap('<div class="inner" />')
		.closest('.inner').wrap('<div class="acf-popup-box" />')
		.closest('.acf-popup-box').wrap('<div id="acf-popup" />')
		.prepend(`<div class="title"><h3>${label}</h3><a href="#" class="acf-icon -cancel grey" data-event="close"></a></div>`)
		.closest('#acf-popup').append('<div class="bg" data-event="close" />')
		.on('click','[data-event="close"]', e => {
			e.preventDefault()
			setTimeout(reset,50)
		})
		.on('click','label', e => {
			setTimeout(reset,50)
		})
		.on('change', e => {
			// e.stopPropagation()
			reset()
		})
	$(document).on('keyup',escReset)
	//
	//
	//
	//
	// // popup.$('.inner:first').append( acfInput )
	// // popup.$('.inner:first').append( button )
	//
	// field.appendChild(popup.$el.get(0))

	//
	// popup.on( 'submit', 'form', e => {
	// 	e.preventDefault();
	// 	console.log(arguments)
	// 	field.appendChild(acfInput)
	// 	popup.close()
	// });
	// popup.on( 'close', e => {
	// 	field.appendChild(acfInput);
	// } );
	e.stopImmediatePropagation()
	e.preventDefault()

	return;
} )
/*
div#acf-popup
	div.acf-popup-box.acf-box
		div.title
			h3
			a.acf-icon.-cancel.grey[data-event="close"][href="#"]
		div.inner
			*
		div.loading
	div.bg[data-event="close"]
*/
