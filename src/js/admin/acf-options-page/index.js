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
		}

	})
	.on('click', '.acf-wpo-export [type="submit"]', e => {

		setTimeout( () => acf.unlockForm( $(e.target).closest('form') ) );

	})
