import $ from 'jquery';

$(document).on('change','.acf-field.acf-field-nav-menu-select select', e => {
	const $select = $(e.target),
		$editLink = $select.closest('.acf-field').find('.edit-menu-link'),
		$createLink = $select.closest('.acf-field').find('.create-menu-link'),
		val = $select.val();
	$editLink.toggleClass( 'acf-hidden', !val )
	$createLink.toggleClass( 'acf-hidden', !!val )
	if ( !! val ) {
		$editLink.attr(
			'href',
			$editLink.attr( 'href').replace(
				/menu=(\d+)/,
				'menu=' + val
			)
		)
	}
})
