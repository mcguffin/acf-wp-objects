import $ from 'jquery';

const idFields = {};

const sanitizeID = val => val.toLowerCase().replace( /\s/g, '-' ).replace(/[^0-9a-z_\-]/g,'-');

const hasVal = ( key, v, own ) => {
	const val = sanitizeID(v)

	try {
		$(`[data-key="${key}"] [type="text"]`).not(own).each((i,el)=>{
			if ( sanitizeID($(el).val()) === val ) {
				throw 'val exists'
			}
		})
	} catch(err) {
		return true;
	}
	return false;

}

// gather ID fields
//
$(document).on('change','.acf-field.acf-id-field [type="text"]', e => {
	const key = $(e.target).closest('.acf-field').attr('data-key');
	const is_once = $(e.target).closest('.acf-field').hasClass('acf-id-once');
	const val = $(e.target).val();
	let new_val = val
	if ( is_once ) {
		new_val = new_val.toLowerCase().normalize("NFD").replace(/(\s+)/g,'-').replace(/[\u0000-\u0020\u007F-\uffff]/g, "")
	}
	let i = 0;
	while ( hasVal( key, new_val, e.target ) ) {
		new_val = is_once ? `${val}-${++i}` : `${val} ${++i}`
	}
	$(e.target).val( new_val )

})
