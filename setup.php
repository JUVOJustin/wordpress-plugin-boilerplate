<?php

// Function to ask for user input in the command line
function ask($question) {
    echo $question . "\n";
    return trim(fgets(STDIN)); // Get input from user
}

// Prompt user for necessary details
$pluginSlug = ask("Enter the slug you want to use for the plugin as snake_case (e.g., 'demo_plugin'): ");
$namespace = ask("Enter the namespace in PascalCase (e.g., 'DemoPlugin'): ");
$pluginName = str_replace('_', ' ', $namespace); // Convert namespace to plugin name by replacing underscores with spaces

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

// Example of renaming operations
$filenameMinus = str_replace('_', '-', $pluginSlug);
$constants = strtoupper($namespace);

// Replace strings in specific files
if (
    !replaceInFiles('demo-plugin', $filenameMinus, '*.{php,js}')
    || !replaceInFiles('Demo_Plugin', $namespace, '*.php')
    || !replaceInFiles('DEMO_PLUGIN', $constants, '*.php')
) {
    echo "Error replacing in files.\n";
    exit;
}
echo "---\n";
echo "Replacements done.\n";
echo "---\n";

// Rename files (demonstration purpose, expand as needed)
if (
    !rename('src/Demo_Plugin.php', "src/{$namespace}.php")
    || !rename('src/demo-plugin.php', "src/{$filenameMinus}.php")
) {
    echo "Error renaming files.\n";
    exit;
}
echo "---\n";
echo "Renaming files done.\n";
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

// Remove setup.php file
if(!unlink(__FILE__)) {
    echo "Error removing setup.php\n";
}

