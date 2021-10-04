import $ from 'jquery';

$(document).on('click', '.acf-field-radio.acf-popup > .acf-input label', e => {
	const field = e.target.closest('.acf-field');
	const acfLabel = field.querySelector('.acf-label label')
	const acfInput = field.querySelector('.acf-input')

	const reset = () => {
		while ( ! $(acfInput).parent().is('.acf-field') ) {
			$(acfInput).siblings().remove()
			$(acfInput).unwrap()
		}
		$(document).off('keyup',escReset)
	}
	const escReset = e => {
		if (e.keyCode === 27 ) {
			reset()
		}
	}

	$(acfInput).wrap('<div class="inner" />')
		.closest('.inner').wrap('<div class="acf-popup-box acf-box" />')
		.closest('.acf-box').wrap('<div id="acf-popup" />')
		.prepend(`<div class="title"><h3>${acfLabel.textContent}</h3><a href="#" class="acf-icon -cancel grey" data-event="close"></a></div>`)
		.closest('#acf-popup').append('<div class="bg" data-event="close" />')
		.on('click','[data-event="close"],label', e => {
			setTimeout(reset,50)
		})
		.on('change', e => {
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
