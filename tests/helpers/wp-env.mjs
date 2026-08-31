/**
 * Resolve the host URL of a running wp-env configuration.
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
 * Ask WordPress for the home URL configured by wp-env.
 *
 * @param {string} configPath wp-env configuration path.
 * @return {URL} URL reachable from the host.
 */
export function getWpEnvUrl( configPath = '.wp-env.test.json' ) {
	const homeUrl = JSON.parse(
		execFileSync(
			process.execPath,
			[
				wpEnvExecutable,
				'run',
				'cli',
				`--config=${ configPath }`,
				'wp',
				'eval',
				'echo wp_json_encode( home_url( "/" ) );',
			],
			{
				cwd: projectRoot,
				encoding: 'utf8',
			}
		)
	);

	return new URL( homeUrl );
}
