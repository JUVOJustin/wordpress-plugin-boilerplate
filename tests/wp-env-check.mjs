/**
 * Verify that host-side tools can reach the running wp-env test environment.
 */
import { getWpEnvUrls } from './helpers/wp-env.mjs';

const { rest: restApiUrl } = getWpEnvUrls();
const response = await fetch( restApiUrl );

if ( ! response.ok ) {
	throw new Error(
		`WordPress REST API returned ${ response.status } at ${ restApiUrl }.`
	);
}
