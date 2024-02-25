<?php

if (defined('WP_CLI') && WP_CLI) {
	$setup = new Demo_Plugin\Cli\Setup();
	WP_CLI::add_command('setup', $setup);
}

return;

$pluginName = ask("Enter the name of the plugin: ");
if (empty($pluginName)) {
    echo "-> You need to provide a name for the plugin.\n";
    exit(1);
}
echo "-> Using plugin name: $pluginName\n\n";

$calculatedNamespace = toPascalSnakeCase($pluginName);
$namespace = ask("Enter the namespace in Camel_Snake Case (e.g., 'Demo_Plugin'). Leave empty for default '$calculatedNamespace': ");
if (empty($namespace)) {
    $namespace = $calculatedNamespace;
}
echo "-> Using namespace: $namespace\n\n";

$calculatedSlug = str_replace('_', '-', str_replace(' ', '-', strtolower($pluginName)));
$pluginSlug = ask("Enter the slug you want to use for the plugin as kebab-case (e.g., 'demo-plugin'). Leave empty for default '$calculatedSlug': ");
if (empty($pluginSlug)) {
    $pluginSlug = $calculatedSlug;
}
echo "-> Using slug: $pluginSlug\n\n";

// Validate inputs
if (empty($pluginSlug) || empty($namespace)) {
    echo "You need to provide both filename and namespace.\n";
    exit(1);
}

echo "\n-> Setup completed\n\n";

// Remove setup.php file
if(!unlink(__FILE__)) {
    echo "Error removing setup.php\n";
}
echo "\n-> Removing setup.php done\n\n";