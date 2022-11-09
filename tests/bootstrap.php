<?php
namespace Demo_Plugin\Tests;

// Require composer dependencies.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';


$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Next, try the WP_PHPUNIT composer package.
if ( ! $_tests_dir ) {
    $_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
}

// See if we're installed inside an existing WP dev instance.
if ( ! $_tests_dir ) {
    $_try_tests_dir = __DIR__ . '/../../../../../tests/phpunit';
    if ( file_exists( $_try_tests_dir . '/includes/functions.php' ) ) {
        $_tests_dir = $_try_tests_dir;
    }
}
// Fallback.
if ( ! $_tests_dir ) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function manually_load_plugins() {
	require dirname( dirname( __FILE__ ) ) . '/demo-plugin.php';
}

tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\\manually_load_plugins' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
