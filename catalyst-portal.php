<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://juvo-design.de
 * @since             1.0.0
 * @package           Catalyst_Portal
 *
 * @wordpress-plugin
 * Plugin Name:       Demo Plugin
 * Plugin URI:        https://juvo-design.de
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Justin Vogt
 * Author URI:        https://juvo-design.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       catalyst-portal
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
use Catalyst_Portal\Activator;
use Catalyst_Portal\Deactivator;
use Catalyst_Portal\Catalyst_Portal;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin absolute path
 */
define( 'CATALYST_PORTAL_PATH', plugin_dir_path( __FILE__ ) );
define( 'CATALYST_PORTAL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Use Composer PSR-4 Autoloading
 */
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 */
function activate_catalyst_portal() {
    Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_catalyst_portal() {
    Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_catalyst_portal' );
register_deactivation_hook( __FILE__, 'deactivate_catalyst_portal' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_catalyst_portal() {

	$version = "1.0.0";
	$plugin = new Catalyst_Portal($version);
	$plugin->run();

}
run_catalyst_portal();
