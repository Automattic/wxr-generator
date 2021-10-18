<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WXR_Generator
 */


// Require composer dependencies.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';


// If we're running in WP's build directory, ensure that WP knows that, too.
if ( 'build' === getenv( 'LOCAL_DIR' ) ) {
	define( 'WP_RUN_CORE_TESTS', true );
}

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
function _manually_load_plugin() {
	require_once dirname( __DIR__ ) . '/wxr-generator.php';
	require_once dirname( __DIR__ ) . '/lib/class-buffer-writer.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );



// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';


