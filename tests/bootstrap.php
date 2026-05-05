<?php
/**
 * PHPUnit bootstrap for WordPress application tests.
 *
 * Requires the wp-env test container to be running so that WP_TESTS_DIR
 * is available. Run tests via: npm run test:php
 *
 * @package Demo_Plugin
 */

/**
 * IMPORTANT: Do not add WordPress config constants here.
 *
 * The WordPress PHPUnit test environment is included and set up automatically
 * by wp-env — there is no need to install or load it manually.
 *
 * All wp-config.php / wp-tests-config.php constants should be configured
 * via the wp-env "config" key in .wp-env.json.
 *
 * Example:
 * {
 *     "config": {
 *         "WP_DEBUG": true,
 *         "WP_DEBUG_LOG": true
 *     }
 * }
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/#using-included-wordpress-phpunit-test-files
 * @link https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/#customizing-the-wp-tests-config-php-file
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
