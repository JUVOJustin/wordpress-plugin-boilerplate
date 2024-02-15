<?php

// Function to ask for user input in the command line
function ask($question): string
{
    echo $question . "\n";
    return trim(fgets(STDIN)); // Get input from user
}

function toPascalSnakeCase($string): string
{
    // Split the string into words based on spaces or underscores
    $words = preg_split('/[\s_]+/', $string);

    // Capitalize the first letter of each word and then join them with an underscore
    return implode('_', array_map('ucfirst', $words));
}

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

// Define PHP paths since glob in php is not recursive
$phpPaths = ['*.php', '**/*.php', 'tests/**/*.php'];

// Replace strings in files according to specified patterns
if (
    !replaceInFiles('demo-plugin', $pluginSlug, array_merge($phpPaths, ['*.js', '*.json']))
    || !replaceInFiles('demo_plugin', str_replace('-', '_', $pluginSlug), $phpPaths)
    || !replaceInFiles('Demo_Plugin', $namespace, array_merge($phpPaths, ['*.json']))
    || !replaceInFiles('DEMO_PLUGIN', strtoupper($namespace), $phpPaths)
    || !replaceInFiles('Demo Plugin', strtoupper($pluginName), ['demo-plugin.php', 'README.txt'])
) {
    echo "Error replacing in files.\n";
    exit;
}
echo "\n-> Replacements done.\n\n";

// Rename files (demonstration purpose, expand as needed)
if (
    !rename('src/Demo_Plugin.php', "src/$namespace.php")
    || !rename('demo-plugin.php', "$pluginSlug.php")
) {
    echo "Error renaming files.\n";
    exit;
}
echo "\n-> Renaming files done.\n\n";

// Further operations like composer update, npm install, etc.
system('composer update');
system('npm install');
system('npm run production');

echo "\n-> Setup completed\n\n";

// Remove setup.php file
if(!unlink(__FILE__)) {
    echo "Error removing setup.php\n";
}
echo "\n-> Removing setup.php done\n\n";


function replaceInFiles(string $find, string $replace, array $filePattern): bool
{
    foreach ($filePattern as $pattern) {
        foreach (glob($pattern,GLOB_BRACE) as $filename) {
            // Exclude setup.php
            if (basename($filename) === 'setup.php') {
                continue;
            }
            $fileContents = file_get_contents($filename);
            $fileContents = str_replace($find, $replace, $fileContents);
            if (!file_put_contents($filename, $fileContents)) {
                echo "Error replacing in file: $filename\n";
                return false;
            }
        }
    }
    return true;
}