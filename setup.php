<?php

// Function to ask for user input in the command line
function ask($question) {
    echo $question . "\n";
    return trim(fgets(STDIN)); // Get input from user
}

function toPascalSnakeCase($string): string {
    // Split the string into words based on spaces or underscores
    $words = preg_split('/[\s_]+/', $string);

    // Capitalize the first letter of each word and then join them with an underscore
    return implode('_', array_map('ucfirst', $words));
}

$pluginName = ask("Enter the name of the plugin: ");
echo "-> Using plugin name: $pluginName\n\n";

$calculatedNamespace = toPascalSnakeCase($pluginName);
$namespace = ask("Enter the namespace in Camel_Snake Case (e.g., 'Demo_Plugin'). Leave empty for default '$calculatedNamespace': ");
if (empty($namespace)) {
    $namespace = $calculatedNamespace;
}
echo "-> Using namespace: $namespace\n\n";

$calculatedSlug = str_replace('_', '-', strtolower($pluginName));
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

// Replace in files function
function replaceInFiles($find, $replace, $filePattern): bool {
    foreach (glob($filePattern) as $filename) {
        $fileContents = file_get_contents($filename);
        $fileContents = str_replace($find, $replace, $fileContents);
        if (!file_put_contents($filename, $fileContents)) {
            echo "Error replacing in file: $filename\n";
            return false;
        }
    }

    return true;
}

// Rename files (demonstration purpose, expand as needed)
if (
    !rename('src/Demo_Plugin.php', "src/{$namespace}.php")
    || !rename('demo-plugin.php', "{$pluginSlug}.php")
) {
    echo "Error renaming files.\n";
    exit;
}
echo "---\n";
echo "Renaming files done.\n";
echo "---\n";

// Replace strings in specific files
if (
    !replaceInFiles('demo-plugin', $pluginSlug, '*.{php,js}')
    || !replaceInFiles('demo_plugin', str_replace('-', '_', $pluginSlug), '*.php')
    || !replaceInFiles('Demo_Plugin', $namespace, '*.php')
    || !replaceInFiles('DEMO_PLUGIN', strtoupper($namespace), '*.php')
) {
    echo "Error replacing in files.\n";
    exit;
}
echo "---\n";
echo "Replacements done.\n";
echo "---\n";

// Replace strings in composer
if(!replaceInFiles('Demo_Plugin', $namespace, 'composer.json')) {
    echo "Error replacing in composer.json\n";
    exit;
}
echo "Renaming and replacements done.\n";

// Further operations like composer update, npm install, etc.
system('composer update');
system('npm install');
system('npm run development');

echo "Setup completed.\n";

// Rename plugin-boilerplate folder to the plugin slug
if(!rename(__DIR__, __DIR__ . '/../' . $pluginSlug)) {
    echo "Error renaming plugin-boilerplate folder\n";
}

// Remove setup.php file
if(!unlink(__FILE__)) {
    echo "Error removing setup.php\n";
}

