<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Demo_Plugin
 */

namespace Demo_Plugin;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Demo_Plugin
 * @author     Justin Vogt <mail@juvo-design.de>
 */
class Demo_Plugin {


	const PLUGIN_NAME    = 'demo-plugin';
	const PLUGIN_VERSION = '1.0.0';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin
	 *
	 * @var Loader
	 */
	protected Loader $loader;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->loader->add_action( 'init', $this, 'register_blocks' );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies(): void {

		$this->loader = new Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale(): void {
		load_plugin_textdomain(
			'demo-plugin',
			false,
			dirname( plugin_basename( __FILE__ ), 2 ) . '/languages/'
		);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks(): void {

		add_action(
			'admin_enqueue_scripts',
			function () {
				$this->enqueue_entrypoint( 'demo-plugin-admin' );
			},
			100
		);

		// <setup-cli-start>
		$this->loader->add_cli( 'setup', new Cli\Setup() );
		// </setup-cli-end>
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks(): void {

		add_action(
			'wp_enqueue_scripts',
			function () {
				$this->enqueue_entrypoint( 'demo-plugin-frontend' );
			},
			100
		);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run(): void {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader(): Loader {
		return $this->loader;
	}

	/**
	 * Enqueue a webpack entrypoint
	 *
	 * @param string              $entry Name of the entrypoint defined in webpack.config.js.
	 * @param array<string,mixed> $localize_data Array of associated data. See https://developer.wordpress.org/reference/functions/wp_localize_script/ .
	 */
	private function enqueue_entrypoint( string $entry, array $localize_data = array() ): void {

		// Try to get WordPress filesystem. If not possible load it.
		global $wp_filesystem;
		if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php'; // @phpstan-ignore requireOnce.fileNotFound
			WP_Filesystem();
		}

		$filesystem = new \WP_Filesystem_Direct( false );

		$asset_file = DEMO_PLUGIN_PATH . "/build/{$entry}.asset.php";
		if ( ! $filesystem->exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;
		if ( ! isset( $asset['dependencies'], $asset['version'] ) ) {
			return;
		}

		if ( $filesystem->exists( DEMO_PLUGIN_PATH . "build/{$entry}.js" ) ) {
			wp_enqueue_script(
				self::PLUGIN_NAME . "/{$entry}",
				DEMO_PLUGIN_URL . "build/{$entry}.js",
				$asset['dependencies'],
				$asset['version'],
				true
			);

			// Potentially add localize data
			if ( ! empty( $localize_data ) ) {
				wp_localize_script(
					self::PLUGIN_NAME . "/{$entry}",
					str_replace( '-', '_', self::PLUGIN_NAME ),
					$localize_data
				);
			}
		}

		if ( $filesystem->exists( DEMO_PLUGIN_PATH . "build/{$entry}.css" ) ) {
			wp_enqueue_style(
				self::PLUGIN_NAME . "/{$entry}",
				DEMO_PLUGIN_URL . "build/{$entry}.css",
				array(),
				$asset['version']
			);
		}
	}

	/**
	 * Register Gutenberg blocks.
	 *
	 * Registers all Gutenberg blocks from the Blocks directory.
	 * Block assets are loaded from the build/Blocks directory using a manifest file.
	 * Uses the metadata collection API (WP 6.8+).
	 *
	 * JSON Translations are loaded automatically. Use `npm run i18n:compile` to generate translation files from .po files.
	 *
	 * To localize scripts you need to use `wp_localize_script`.
	 * The handle can be generated with `generate_block_asset_handle('demo-plugin/block-name', 'editorScript')`.
	 *
	 * @link https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 * @link https://developer.wordpress.org/reference/functions/generate_block_asset_handle/
	 *
	 * @return void
	 */
	public function register_blocks(): void {

		$manifest_file = DEMO_PLUGIN_PATH . 'build/blocks-manifest.php';
		$blocks_folder = DEMO_PLUGIN_PATH . 'build/Blocks';

		if ( ! is_readable( $manifest_file ) || ! is_dir( $blocks_folder ) ) {
			return;
		}

		wp_register_block_types_from_metadata_collection( $blocks_folder, $manifest_file );
	}
}
