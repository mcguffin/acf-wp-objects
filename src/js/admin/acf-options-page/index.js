import $ from 'jquery';

$(document)
.on('click', '.acf-wpo-import-export [data-action="export"][data-ajax-request]', e => {

	e.preventDefault();

	const request_data = JSON.parse( $(e.target).attr('data-ajax-request') );
	const $textarea = $('.acf-wpo-import-export textarea')

	$.ajax( {
		url: wp.ajax.settings.url,
		data: request_data,
		method:'post',
		success: response => {
			$textarea.val(JSON.stringify(response));
		}
	} );

})
.on('click', '.acf-wpo-import-export [data-action="import"][data-ajax-request]', e => {

	e.preventDefault();

	const request_data = JSON.parse( $(e.target).attr('data-ajax-request') );
	const $textarea = $('.acf-wpo-import-export textarea')
	request_data.data = JSON.parse( $textarea.val() );
	if ( !! request_data.data )
	$.ajax( {
		url: wp.ajax.settings.url,
		method:'post',
		data: request_data,
		success: response => {
			if ( response.success ) {
				location.reload()
			} else {
				console.log(response.message)
			}
		}
	} );

})
