
const fillTemplate = (vars,template,fallback = '') => {
	let hasValues = false
	Object.entries(vars).forEach(([key,value]) => {
		hasValues |= !!value
		template = template.replaceAll(`{${key}}`,value)
	})
	return hasValues ? template : fallback;
}

export {
	fillTemplate
}
