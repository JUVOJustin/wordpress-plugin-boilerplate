/**
 * Build configuration for bud.js
 *
 * @package Demo_Plugin
 * @param {import('@roots/bud').Bud} bud
 */

export default async bud => {

	/**
	 * Configuring the output settings of the webpack compilation.
	 * The 'chunkLoadingGlobal' option customizes the global variable used for managing webpack's chunk loading.
	 * Setting this to a unique value ('demo-plugin') ensures that multiple plugins can operate without
	 * interfering with each other's chunk loading mechanisms, thereby avoiding runtime conflicts.
	 */
	bud.config(
		{
			output: {
				chunkLoadingGlobal: 'demo-plugin',
			},
		}
	)

	/**
	 * Sets the base directory for the source files of the project.
	 * This configuration helps bud.js to resolve the relative paths for entry points and other assets
	 * by defining 'resources' as the directory where source files are located.
	 */
	bud.setPath( `@src`, `resources` )

	/**
	 * Defines the entry points for the application.
	 * Entry points are relative to the '@src' path. Multiple entry points allow for separate bundles
	 * for different parts of the application, such as front-end and admin sections. Each entry
	 * comprises JavaScript and Sass files that will be compiled into CSS.
	 */
	bud.entry(
		{
			"demo-plugin-frontend": [` / frontend / js / app.js`, ` / frontend / scss / app.scss`],
			"demo-plugin-admin": [` / admin / js / app.js`, ` / admin / scss / app.scss`]
		}
	)

	/**
	 * Provides an automatic mapping of global variables (like jQuery) to imported modules.
	 * This configuration ensures that whenever '$' or 'jQuery' are referenced, they are automatically
	 * resolved to the jQuery module, simplifying the management of common dependencies and avoiding
	 * the need to import them in every file where they are used.
	 */
	bud.provide(
		{
			jquery: ['$', 'jQuery'],
		}
	)

	/**
	 * Automates the copying of static assets from a specified source folder to a distribution folder.
	 * The 'from' path is defined as the 'static' directory under the '@src' path, and the 'to' path is
	 * the 'static' directory under the '@dist' path. 'noErrorOnMissing' set to true prevents the build
	 * from failing if the source directory is missing, providing robustness in asset handling.
	 */
	bud.assets(
		{
			from: bud.path( `@src / static` ),
			to: bud.path( `@dist / static` ),
			noErrorOnMissing: true,
		}
	)

	/**
	 * Enables the splitting of code into different chunks based on various criteria to optimize loading times.
	 * Splitting common dependencies into separate chunks can improve cacheability and reduce the amount of
	 * code downloaded on initial page loads.
	 */
	bud.splitChunks()


	/**
	 * Enables minification of the output files when the build is run in production mode.
	 * Minification reduces the size of the output files, which decreases loading time and improves performance.
	 */
	bud.minimize( bud.isProduction )

	/**
	 * Compatibility for shadcn components and alias ootb
	 */
	bud.alias(
		{
			'@': bud.path( '@src' ),
		}
	)
}
