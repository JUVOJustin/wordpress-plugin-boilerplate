<?php
/**
 * PHPUnit bootstrap for WordPress application tests.
 *
 * Requires the wp-env test container to be running so that WP_TESTS_DIR
 * is available. Run tests via: npm run test:php
 *
 * @package Demo_Plugin
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$tests_dir = getenv( 'WP_TESTS_DIR' );

if ( false === $tests_dir || '' === $tests_dir ) {
	fwrite( STDERR, "WP_TESTS_DIR is not set. Start the test environment first: npm run env:start\n" );
	exit( 1 );
}

// Required by the WordPress test bootstrap.
require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

require_once $tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__ ) . '/demo-plugin.php';
	}
);

require $tests_dir . '/includes/bootstrap.php';
