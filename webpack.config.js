const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const extraEntries = {
	'demo-plugin-frontend': [
		path.resolve( __dirname, 'resources/frontend/js/app.js' ),
		path.resolve( __dirname, 'resources/frontend/scss/app.scss' ),
	],
	'demo-plugin-admin': [
		path.resolve( __dirname, 'resources/admin/js/app.js' ),
		path.resolve( __dirname, 'resources/admin/scss/app.scss' ),
	],
};

const extendConfig = ( config ) => ( {
	...config,

	// Merge entries instead of overwriting wp-scripts’ block entries
	entry: {
		...( typeof config.entry === 'function'
			? config.entry()
			: config.entry ),
		...extraEntries,
	},
} );

module.exports = Array.isArray( defaultConfig )
	? defaultConfig.map( extendConfig )
	: extendConfig( defaultConfig );
