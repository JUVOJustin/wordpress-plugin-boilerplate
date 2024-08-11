<?php
/**
 * Setup file that adds a cli comment to register the setup command before the plugin is built.
 * After the plugin is fully set up the command will be registered in the main class and this file will be deleted.
 *
 * @package Demo_Plugin
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	$setup = new Demo_Plugin\Cli\Setup();
	WP_CLI::add_command( 'setup', $setup );
}

return;
