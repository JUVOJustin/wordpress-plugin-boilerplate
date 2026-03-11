<?php
/**
 * PHPUnit bootstrap for WordPress application tests.
 *
 * @package Demo_Plugin
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Prefer wp-env's bundled test framework, fall back to Composer package.
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! is_string( $_tests_dir ) || '' === $_tests_dir ) {
	$_tests_dir = dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit';
}

if ( ! is_dir( $_tests_dir . '/includes' ) ) {
	fwrite( STDERR, "WordPress test framework not found. Start wp-env or install wp-phpunit/wp-phpunit.\n" );
	exit( 1 );
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__ ) . '/demo-plugin.php';
	}
);

require $_tests_dir . '/includes/bootstrap.php';
