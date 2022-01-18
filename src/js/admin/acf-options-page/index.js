import $ from 'jquery';

$(document)
	.on('change', '#acf-wpo-import-file', e => {
		const fileInput = e.target;
		let fileReader;
		if ( !! fileInput.value ) {


			fileReader = new FileReader()
			fileReader.onload = () => {
				$('#acf-wpo-import-json').val( fileReader.result )
				$('#import').prop('disabled', false );
			}

			if ( !! fileInput.files[0] ) {
				fileReader.readAsText( fileInput.files[0] )
			}
		} else {
			$('#import').prop('disabled', true );
		}

	})
	.on('click', '.acf-wpo-export [name="options_page_action"]', e => {

		setTimeout( () => {
			const $form = $(e.target).closest('form');
			console.log($form)
			acf.unlockForm( $form )
			$form.submit()
		} );

	})
	.on('focus', '[name="options_page_action"]', e => {
		$( e.target ).prop( 'type', 'submit' )
	})
	.on('blur', '[name="options_page_action"]', e => {
		$( e.target ).prop( 'type', 'button' )
	})
