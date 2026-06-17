const path = require( 'path' );
const webpack = require( 'webpack' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

/** Plugin-specific asset entry points, merged with wp-scripts block entries. */
const customEntries = {
	'demo-plugin-frontend': [
		path.resolve( __dirname, 'resources/frontend/js/app.js' ),
		path.resolve( __dirname, 'resources/frontend/scss/app.scss' ),
	],
	'demo-plugin-admin': [
		path.resolve( __dirname, 'resources/admin/js/app.js' ),
		path.resolve( __dirname, 'resources/admin/scss/app.scss' ),
	],
};

/**
 * Preserves wp-scripts' auto-discovered block entries while adding custom ones.
 *
 * With `--experimental-modules`, wp-scripts exports an array of two configs: the
 * classic script config and an ESM module config (`experiments.outputModule`) that
 * compiles Interactivity API `viewScriptModule`/`view.js` modules. The jQuery provide
 * shim and the custom script/style entries belong only to the classic script build, so
 * the ESM module config is passed through untouched. Injecting jQuery or the SCSS/admin
 * entries there would break the Interactivity API `view.js` module build.
 */
const extendConfig = ( config ) => {
	if ( config.experiments?.outputModule ) {
		return config;
	}

	return {
		...config,
		plugins: [
			...config.plugins,
			new webpack.ProvidePlugin( {
				$: 'jquery',
				jQuery: 'jquery',
			} ),
		],
		entry: {
			...( typeof config.entry === 'function'
				? config.entry()
				: config.entry ),
			...customEntries,
		},
	};
};

module.exports = Array.isArray( defaultConfig )
	? defaultConfig.map( extendConfig )
	: extendConfig( defaultConfig );
