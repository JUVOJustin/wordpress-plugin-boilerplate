/**
 * Resolve host URLs for a running wp-env configuration.
 */
import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = fileURLToPath( new URL( '../../', import.meta.url ) );
const require = createRequire( import.meta.url );
const wpEnvPackagePath = require.resolve( '@wordpress/env/package.json' );
const wpEnvPackage = require( wpEnvPackagePath );
const wpEnvExecutable = resolve(
	dirname( wpEnvPackagePath ),
	wpEnvPackage.bin[ 'wp-env' ]
);

/**
 * Ask WordPress for its browser and REST API URLs.
 *
 * @param {string} configPath wp-env configuration path.
 * @return {{home: URL, rest: URL}} URLs reachable from the host.
 */
export function getWpEnvUrls( configPath = '.wp-env.test.json' ) {
	const urls = JSON.parse(
		execFileSync(
			process.execPath,
			[
				wpEnvExecutable,
				'run',
				'cli',
				`--config=${ configPath }`,
				'wp',
				'eval',
				'echo wp_json_encode( array( "home" => home_url( "/" ), "rest" => get_rest_url() ) );',
			],
			{
				cwd: projectRoot,
				encoding: 'utf8',
			}
		)
	);

	return {
		home: new URL( urls.home ),
		rest: new URL( urls.rest ),
	};
}
