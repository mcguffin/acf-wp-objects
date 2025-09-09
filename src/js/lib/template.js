
const fillTemplate = (vars,template) => {
	console.log(vars)
	Object.entries(vars).forEach(([key,value]) => {
		template = template.replaceAll(`{${key}}`,value)
	})
	return template;
}

export {
	fillTemplate
}
