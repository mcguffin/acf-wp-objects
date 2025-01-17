import $ from 'jquery';

$(document).on('click', '.acf-field-radio.acf-popup .flt label', e => {
	// prevent firing change events
	e.preventDefault()
	const cb = e.target.querySelector('[type="checkbox"]')
	cb.checked = ! cb.checked
})

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
	const filter = (() => {

		const selectors = [];

		const filter = document.createElement('div')
		const style  = document.createElement('style')

		const keywords = Array.from(acfInput.querySelectorAll('data[value]'))
			.map( el => el.getAttribute('value') )
			.filter( val => !! val )
			.filter( (value, index, array) => array.indexOf(value) === index )
			.map( keyword => {
				selectors.push( `.acf-popup-box .flt:has(:checked):has([value="${keyword}"]:checked) ~ .acf-input > .acf-radio-list > li:not(:has(data[value="${keyword}"]))`)
				return keyword;
			} )
			.map( keyword => {
				const label = document.createElement('label')
				const input = document.createElement('input')
				input.type  = 'checkbox'
				input.value = keyword
				label.textContent = keyword
				label.append(input)
				filter.append(label)
				return keyword
			} )

		if ( ! keywords.length ) {
			// empty div
			return filter
		}

		filter.classList.add('flt')
		// style.innerHTML = selectors.join(',') + '{ opacity: 0.125; filter: grayscale(1); pointer-events: none; }'
		style.innerHTML = selectors.join(',') + '{ display: none; }'
		filter.append(style)

		return filter;
	})()

	const updateFilter = () => {
		filter
			.querySelectorAll('[type="checkbox"]')
			.forEach( el => {
				if ( ! el.matches(':checked')) {
					const matchingElements = Array.from( acfInput.querySelectorAll(`label:has(data[value="${el.value}"])`) )
						.filter( el => getComputedStyle(el).opacity === "1" )

					el.disabled = matchingElements.length === 0
				}
			})
	}

	$('body').toggleClass( 'acf-popup-open', true );

	acfLabel && $(acfLabel).css('padding-bottom',$(acfInput).height()+'px')
// return console.log($(acfInput).wrap('<div class="inner" />').closest('.inner'))
	$(acfInput).wrap('<div class="inner" />')
		.closest('.inner')
		.prepend(filter)
		.wrap('<div class="acf-popup-box" />')
		.closest('.acf-popup-box').wrap('<div id="acf-popup" />')
		.prepend(`<div class="title"><h3>${label}</h3><a href="#" class="acf-icon -cancel grey" data-event="close"></a></div>`)
		.closest('#acf-popup').append('<div class="bg" data-event="close" />')
		.on('click','[data-event="close"]', e => {
			e.preventDefault()
			setTimeout(reset,50)
		})
		.on('click','li > label', e => {
			setTimeout(reset,50)
		})
		.on('change', '[type="radio"]', e => {
			reset()
		})
		.on('change', '.flt [type="checkbox"]', e => {
			updateFilter()
		})
	$(document).on('keyup',escReset)

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
