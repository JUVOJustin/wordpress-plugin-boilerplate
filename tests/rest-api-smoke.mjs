/**
 * Verify that host-side tests can reach wp-env on its runtime-selected port.
 */
import { getWpEnvUrl } from './helpers/wp-env.mjs';

const restApiUrl = new URL( '/wp-json/', getWpEnvUrl() );
const response = await fetch( restApiUrl );

if ( ! response.ok ) {
	throw new Error(
		`WordPress REST API returned ${ response.status } at ${ restApiUrl }.`
	);
}

const index = await response.json();

if ( ! index.namespaces?.includes( 'wp/v2' ) ) {
	throw new Error(
		`WordPress REST API index is invalid at ${ restApiUrl }.`
	);
}
