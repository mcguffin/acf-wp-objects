// extract strings from ACF field groups and save them to src/php/acf.php

const glob = require('glob');
const xt = require('./lib/json-extract.js');


let textdomain = process.argv[2] || 'mcguffin';
let json_dir   = process.argv[3] || './json/acf';
let php_path   = process.argv[4] || './src/php/json-strings.php';

let strings = [];

const common_mapping = {
	title:xt.map_string,
	description:xt.map_string,
	label:xt.map_string,
	labels:xt.map_values,
}

// acf
strings = xt.parse_files(
	glob.sync(`${json_dir}/*.json`),
	{
		title:xt.map_string,
		description:xt.map_string,
		label:xt.map_string,
		instructions:xt.map_string,
		prepend:xt.map_string,
		append:xt.map_string,
		message:xt.map_string,
		choices:xt.map_values
	},
	strings
);

xt.generate_php( php_path, strings, textdomain );
