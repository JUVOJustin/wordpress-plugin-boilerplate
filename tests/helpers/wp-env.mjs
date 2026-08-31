/**
 * Resolve the host URL of a running wp-env configuration.
 */
import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = fileURLToPath( new URL( '../../', import.meta.url ) );
const require = createRequire( import.meta.url );
const wpEnvPackagePath = require.resolve( '@wordpress/env/package.json', {
	paths: [ projectRoot ],
} );
const wpEnvPackage = require( wpEnvPackagePath );
const wpEnvExecutable = resolve(
	dirname( wpEnvPackagePath ),
	wpEnvPackage.bin[ 'wp-env' ]
);

/**
 * Read the runtime port instead of trusting the configured preferred port.
 *
 * @param {string} configPath wp-env configuration path.
 * @return {URL} URL reachable from the host.
 */
export function getWpEnvUrl( configPath = '.wp-env.test.json' ) {
	const status = JSON.parse(
		execFileSync(
			process.execPath,
			[ wpEnvExecutable, 'status', `--config=${ configPath }`, '--json' ],
			{
				cwd: projectRoot,
				encoding: 'utf8',
			}
		)
	);

	if ( status.status !== 'running' ) {
		throw new Error( `wp-env is ${ status.status }.` );
	}

	if ( ! status.urls?.development || ! status.ports?.development ) {
		throw new Error( 'wp-env did not report a development URL and port.' );
	}

	const url = new URL( status.urls.development );
	url.port = String( status.ports.development );

	return url;
}
