import $ from 'jquery';

acf.addAction(`after_duplicate`, ($cloneRow,$newRow) => {
	// reset post id field after duplicate
	const repeaterField  = acf.getField($cloneRow.closest('.acf-field-repeater'))
	const postIdFieldKey = repeaterField?.get('post_id_field')
	if ( ! postIdFieldKey ) {
		return;
	}
	const $postIdEl = $newRow.find(`[data-key="${postIdFieldKey}"]`);
	acf.getField($postIdEl)?.$input().val('null');
} )
