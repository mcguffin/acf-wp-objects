import $ from 'jquery';
const contextSelector = '.acf-row,.layout,.acf-postbox';

const findClosestField = (contextField,fieldKey) => {
	let contextEl = contextField.$el.get(0)
	let fallbackEl = false;
	while ( contextEl && ! fallbackEl ) {
		contextEl = contextEl.parentNode?.closest( contextSelector )
		if ( ! contextEl ) {
			break;
		}
		fallbackEl = contextEl.querySelector(`[data-key="${fieldKey}"]`)
	}
	return acf.getField($(fallbackEl));
}

export {
	findClosestField
}
