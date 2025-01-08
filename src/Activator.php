<?php
/**
 * Fired during plugin activation
 *
 * @package    Demo_Plugin
 */

namespace Demo_Plugin;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/
 * @package    Demo_Plugin
 */
class Activator {

	/**
	 * This is the general callback run during the 'register_activation_hook' hook.
	 *
	 * @return void
	 */
	public static function activate(): void {
	}

	/**
	 * Add logic to the activation on a network site.
	 *
	 * @param string $plugin Plugin file loaded.
	 * @param bool $network_wide Indicates if loaded network wide.
	 * @return void
	 */
	public static function network_activation( string $plugin, bool $network_wide ): void {

		if ( !str_contains($plugin, Demo_Plugin::PLUGIN_NAME) || !$network_wide) {
			return;
		}

		//Network deactivate
		//deactivate_plugins( $plugin,false, true );

		//Activate on single site
		//activate_plugins( $plugin );

	}
}