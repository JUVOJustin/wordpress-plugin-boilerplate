<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://justin-vogt.com
 * @since             1.0.0
 * @package           Demo_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Demo Plugin
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Requires PHP:      8.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       demo-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
use Demo_Plugin\Activator;
use Demo_Plugin\Deactivator;
use Demo_Plugin\Demo_Plugin;
use Demo_Plugin\Uninstallor;

if (! defined('WPINC')) {
    die;
}

/**
 * Plugin absolute path
 */
define('DEMO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DEMO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Use Composer PSR-4 Autoloading
 */
require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 */
function demo_plugin_activate(): void
{
    Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function demo_plugin_deactivate(): void
{
    Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstallation.
 */
function demo_plugin_uninstall(): void
{
    Uninstallor::uninstall();
}

register_activation_hook(__FILE__, 'demo_plugin_activate');
register_deactivation_hook(__FILE__, 'demo_plugin_deactivate');
register_uninstall_hook(__FILE__, 'demo_plugin_uninstall');
add_action('activated_plugin', array( Activator::class, 'network_activation' ), 10, 2);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function demo_plugin_run(): void
{
    $plugin = new Demo_Plugin();
    $plugin->run();
}
demo_plugin_run();
