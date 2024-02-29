<?php

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
 * @subpackage Demo_Plugin/includes
 * @author     Justin Vogt <mail@juvo-design.de>
 */
class Demo_Plugin
{

    const PLUGIN_NAME = 'demo-plugin';

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin
     *
     * @var Loader
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct(string $version)
    {
        $this->version = $version;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

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
    private function load_dependencies(): void
    {

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
    private function set_locale(): void
    {

        $plugin_i18n = new i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks(): void
    {

        add_action('admin_enqueue_scripts', function () {
            $this->enqueue_bud_entrypoint('admin');
        }, 100);

		// Add Setup Command
	    $this->loader->add_cli('setup', new Cli\Setup());

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks(): void
    {

        add_action('wp_enqueue_scripts', function () {
            $this->enqueue_bud_entrypoint('frontend');
        }, 100);

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run(): void
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name(): string
    {
        return self::PLUGIN_NAME;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader(): Loader
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     */
    public function get_version(): string
    {
        return $this->version;
    }

    /**
     * Enqueue a bud entrypoint
     *
     * @param string $entry
     * @param mixed[] $localize_data
     */
    private function enqueue_bud_entrypoint(string $entry, array $localize_data = []): void
    {
        $entrypoints_manifest = DEMO_PLUGIN_PATH . '/dist/entrypoints.json';

        // parse json file
        $entrypoints = json_decode(file_get_contents($entrypoints_manifest));

        // Iterate entrypoint groups
        foreach ($entrypoints as $key => $bundle) {

            // Only process the entrypoint that should be enqueued per call
            if ($key != $entry) {
                continue;
            }

            // Iterate js and css files
            foreach ($bundle as $type => $files) {
                foreach ($files as $file) {
                    if ($type == "js") {
                        wp_enqueue_script(
                            self::PLUGIN_NAME. "/$file",
                            DEMO_PLUGIN_URL . 'dist/' . $file,
                            $bundle->dependencies ?? [],
                            null,
                            true,
                        );

                        // Maybe localize js
                        if (!empty($localize_data)) {
                            wp_localize_script(self::PLUGIN_NAME. "/$file", str_replace('-', '_', self::PLUGIN_NAME), $localize_data);

                            // Unset after localize since we only need to localize one script per bundle so on next iteration will be skipped
                            unset($localize_data);
                        }
                    }

                    if ($type == "css") {
                        wp_enqueue_style(
                            self::PLUGIN_NAME. "/$file",
                            DEMO_PLUGIN_URL . 'dist/' . $file
                        );
                    }
                }
            }
        }
    }

    /**
	 * Generates a unique but deterministic key usable for object caching. The key is prefixed by the plugin name
	 *
	 * @param mixed[] $matching_data Pass any data that should be used to match the cache
	 *
	 * @return string
	 */
	public static function generate_cache_key(array $matching_data): string {
		foreach($matching_data as $key => $value) {
			$matching_data[ $key] = serialize($value);
		}

		$matching_data = implode('-', $matching_data);
		return self::PLUGIN_NAME. '-'. md5($matching_data);
	}

}
