const fs = require('fs');
const pck = JSON.parse( fs.readFileSync('./package.json') );
let slug = process.argv[2] || 'new_field_group';

let path = './acf-json/group_' + pck.wpPlugin.prefix + '_' + slug + '.json';
fs.mkdirSync( './acf-json', { recursive: true } );

let data = {
    "key": "group_" + pck.wpPlugin.prefix + "_" + slug,
    "title": slug,
    "fields": [],
    "location": [
        [
            {
                "param": "post_type",
                "operator": "==",
                "value": "post"
            }
        ]
    ],
    "menu_order": 0,
    "position": "normal",
    "style": "default",
    "label_placement": "top",
    "instruction_placement": "label",
    "hide_on_screen": "",
    "active": 1,
    "description": "",
    "modified": Math.floor(Date.now() / 1000)
}

if ( fs.existsSync(path) && process.argv.indexOf('force') === -1 ) {
	throw 'Field group exists!';
}
fs.writeFile(path,JSON.stringify(data,null,"\t"),()=>{});
