<?php
/**
 * Tests for the companion-plugin handshake exposed by the bootstrap.
 *
 * Covers the `demo_plugin_loaded` action and the `DEMO_PLUGIN_VERSION` constant
 * defined in demo-plugin.php.
 *
 * @package Demo_Plugin
 */

use Demo_Plugin\Demo_Plugin;

/**
 * Verifies the load handshake companion plugins rely on.
 */
class CompanionHandshakeTest extends WP_UnitTestCase {

	/**
	 * The global version constant must mirror the single source of truth so
	 * companions can version-gate without autoloading the plugin's classes.
	 */
	public function test_version_constant_mirrors_class_constant(): void {
		$this->assertTrue( defined( 'DEMO_PLUGIN_VERSION' ), 'DEMO_PLUGIN_VERSION should be defined by the bootstrap.' );
		$this->assertSame( Demo_Plugin::PLUGIN_VERSION, DEMO_PLUGIN_VERSION );
	}

	/**
	 * The handshake must actually fire during a real WordPress load, after
	 * plugins are included (it is hooked to `plugins_loaded`).
	 */
	public function test_loaded_action_fires_on_bootstrap(): void {
		$this->assertGreaterThan( 0, did_action( 'demo_plugin_loaded' ) );
	}

	/**
	 * The action must pass the current plugin version to its listeners.
	 *
	 * Re-dispatching `plugins_loaded` runs the bootstrap's priority-0 callback
	 * again so the argument it forwards can be observed.
	 */
	public function test_loaded_action_passes_plugin_version(): void {
		$received = array();
		add_action(
			'demo_plugin_loaded',
			static function ( $version ) use ( &$received ): void {
				$received[] = $version;
			}
		);

		do_action( 'plugins_loaded' );

		$this->assertNotEmpty( $received, 'demo_plugin_loaded should fire when plugins_loaded runs.' );
		$this->assertSame( Demo_Plugin::PLUGIN_VERSION, end( $received ) );
	}
}
