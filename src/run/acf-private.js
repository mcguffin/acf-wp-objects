const glob	= require('glob');
const fs	= require('fs');

const FS_OPT = { encoding: 'utf-8' };
const priv = parseInt( process.argv[2] ) || 0;
const dir = process.argv[3] || './acf-json'

glob.sync( dir + '/group_*.json').forEach( file => {
	const json = JSON.parse( fs.readFileSync( file, FS_OPT ) );
	json.private = priv;
	json.modified = parseInt( Date.now() / 1000 );
	fs.writeFileSync( file, JSON.stringify( json, null, 4 ), FS_OPT );
});
