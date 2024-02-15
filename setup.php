<?php

// Function to ask for user input in the command line
function ask($question) {
    echo $question . "\n";
    return trim(fgets(STDIN)); // Get input from user
}

// Prompt user for necessary details
$filename = ask("Enter the filename in snake_case (e.g., 'demo_plugin'): ");
$namespace = ask("Enter the namespace in PascalCase (e.g., 'DemoPlugin'): ");
$pluginName = str_replace('_', ' ', $namespace); // Convert namespace to plugin name by replacing underscores with spaces

// Validate inputs
if (empty($filename) || empty($namespace)) {
    echo "You need to provide both filename and namespace.\n";
    exit(1);
}

// Replace in files function
function replaceInFiles($find, $replace, $filePattern) {
    foreach (glob($filePattern) as $filename) {
        $fileContents = file_get_contents($filename);
        $fileContents = str_replace($find, $replace, $fileContents);
        file_put_contents($filename, $fileContents);
    }
}

// Example of renaming operations
$filenameMinus = str_replace('_', '-', $filename);
$namespaceUpper = strtoupper($namespace);
$namespaceLower = strtolower($namespace);

// Replace strings in specific files
replaceInFiles('demo-plugin', $filenameMinus, '*.{php,js}');
replaceInFiles('Demo_Plugin', $namespace, '*.php');
replaceInFiles('DEMO_PLUGIN', $namespaceUpper, '*.php');

// Rename files (demonstration purpose, expand as needed)
rename('src/Demo_Plugin.php', "src/{$namespace}.php");

echo "Renaming and replacements done.\n";

// Further operations like composer update, npm install, etc.
system('composer update');
system('npm install');
system('npm run development');

echo "Setup completed.\n";
