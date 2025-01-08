<?php


namespace Demo_Plugin;


class Activator {

    public static function activate() {
    }

	/**
	 * Add logic to the activation on a network site.
	 *
	 * @param string $plugin
	 * @param bool $network_wide
	 * @return void
	 */
	public static function network_activation( string $plugin, bool $network_wide ) {

		if ( !str_contains($plugin, Demo_Plugin::PLUGIN_NAME) || !$network_wide) {
			return;
		}

		//Network deactivate
		//deactivate_plugins( $plugin,false, true );

		//Activate on single site
		//activate_plugins( $plugin );

	}

}