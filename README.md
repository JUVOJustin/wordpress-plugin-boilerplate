# WordPress Plugin Boilerplate

This boilerplate is a fork of [WordPress Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate) with additional features and improvements. It provides a modern, organized, and object-oriented foundation for building high-quality WordPress plugins.

## Features of this boilerplate
- Namespaces support using composer
- Automatic Namespace prefixing with [Strauss](https://github.com/BrianHenryIE/strauss)
- Easy Shortcode, CLI Command Registration through the loader
- PHPStan with ready-made Github actions
- PHPCS with ready-made Github actions
- [Bud.js](https://bud.js.org/) for simple bundling and build of assets
- ESLint built in
- Ready-made Github actions for building and bundling
- Ready-made prompts as Claude commands for common tasks (compatible with any LLM)

# Setup

## Step 1: Create Your Project
Run the following command to create your project in the current folder. This will download the boilerplate and automatically run the script for initial configuration:

```bash
composer create-project juvo/wordpress-plugin-boilerplate
```

The boilerplate will be set up in the current directory, and the setup script will run automatically.

## Step 2: Configure Your Plugin (Automatic Prompt)
Upon project creation, you'll be guided through a series of prompts to configure your plugin:

- **Plugin Name**: Enter the name of your plugin.
- **Namespace (optional)**: Suggests a default namespace based on your plugin name but allows customization.
- **Plugin Slug (optional)**: Choose a slug for your plugin; a default based on your plugin name is suggested.

Your inputs will automatically tailor the boilerplate to match your plugin's identity.

## Step 3: Finalization (Optional)
After configuration, the setup will finalize by updating files, renaming relevant items, and performing cleanup actions, including:
- Replacing placeholders with your specified details.
- Renaming files to match your plugin's namespace and slug.
- Running `composer update` and `npm install` to install dependencies.
- Cleaning up by removing the `setup.php` file.

At this point, the plugin is set up and good to go. Now it's your time to adjust plugin and readme headers according to your needs.

# Development Guide

## Project Structure

### Source Code Organization

All plugin logic should go into the `src` folder. This separation helps maintain a clean structure and follows modern PHP development practices. Example: 

```
src/
├── API/           # Rest API-specific functionality
├── CLI/           # CLI commands
└── Integrations/  # Core plugin functions and utilities
    └── BricksBuilder/ # Bricks Builder integration
    └── Elementor/ # Elementor integration
    └── ACF/ # ACF integration
    └── WC/ # WooCommerce integration
```

Avoid placing logic directly in the plugin's root files. The main plugin file should primarily be used for bootstrapping your plugin.

## Loader and Hooks Registration

### Using the Loader Correctly

The plugin uses a loader pattern to centralize the registration of hooks, filters, and shortcodes. Instead of registering hooks in class constructors, always use the loader in the root class:

```php
// In your Plugin.php main class
public function __construct() {
    $this->loader = new Loader();
    $this->define_admin_hooks();
    $this->define_public_hooks();
    $this->define_shortcodes();
}

private function define_admin_hooks() {
    $admin = new Admin\Admin($this->get_plugin_name(), $this->get_version());
    $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
}

private function define_shortcodes() {
    $example_shortcode = new Shortcodes\ExampleShortcode();
    $this->loader->add_shortcode('example', $example_shortcode, 'render');
}
```

This approach provides several benefits:
1. Centralized hook management
2. Easier debugging
3. Better testability
4. Cleaner class implementations

## Frontend Assets Management

### Resources Organization

All frontend-facing assets (scripts, styles, images, etc.) should be placed in the `resources` directory, organized by context:

```
resources/
├── admin/            # Admin-specific assets
│   ├── css/
│   ├── js/
│   └── images/
├── frontend/         # Frontend-specific assets
│   ├── css/
│   ├── js/
│   └── images/
├── acf-json/         # ACF field group JSON files
└── bud.config.js     # Bud.js configuration
```

### Asset Compilation

The boilerplate uses [Bud.js](https://bud.js.org/) for asset compilation and bundling. The configuration is in `bud.config.js`.

To compile assets:
```
npm run development        # Watch for changes during development and compile assets
npm run production         # For production. Compiles assets and minifies them
```

### Adding New Entry Points

To add a new entry point for scripts or styles, modify the `bud.config.js` file:

```js
// Example of adding a new entry point
app.entry({
  admin: ['resources/admin/js/admin.js', 'resources/admin/css/admin.scss'],
  frontend: ['resources/frontend/js/frontend.js', 'resources/frontend/css/frontend.scss'],
  customFeature: ['resources/frontend/js/custom-feature.js', 'resources/frontend/css/custom-feature.scss'],
});
```

Then ensure your plugin loads these assets appropriately using the loader pattern.

## Quality Assurance and Workflows

### Default Checks and Quality Pipelines

The boilerplate comes with the following quality assurance tools configured:

1. **PHP CodeSniffer (PHPCS)**: Enforces WordPress coding standards
2. **PHPStan**: Provides static analysis to catch potential bugs
3. **ESLint**: Ensures JavaScript code quality
4. **GitHub Actions**: Automates testing and building processes

These checks run automatically in GitHub pipelines when you push code to your repository.

### GitHub Workflows

The boilerplate includes the following GitHub Actions workflows:

- **Quality Checks**: Runs PHPCS and PHPStan to ensure code quality
- **Asset Building**: Compiles and bundles frontend assets
- **Release Management**: Helps with versioning and releases

## PHP Version Management

Changing the PHP version for your plugin requires updates in multiple places to ensure consistency across development, testing, and deployment environments.

### Composer Configuration

The plugin's PHP version requirements must be updated in `composer.json` in two places:

1. The `require` section specifies the minimum PHP version:

```json
"require": {
    "php": ">=8.0"
}
```

2. The `config.platform` section which controls what PHP version Composer uses for compatibility checks:

```json
"config": {
    "platform": {
        "php": "8.0"
    }
}
```

After updating these values, run `composer update` to update your dependencies based on the new PHP version constraints.

### GitHub Pipeline Configuration

The GitHub Actions workflows are configured to use PHP 8.0. When changing the PHP version, you'll need to update it in these workflow files:

1. Edit `.github/workflows/test-analyse.yml` and update the PHP version:

```yaml
- name: Setup php
  uses: shivammathur/setup-php@v2
  with:
    php-version: '8.0'
    tools: cs2pr
```

2. Also update the PHP version in `.github/workflows/install-deps.yml`:

```yaml
- name: Setup php
  uses: shivammathur/setup-php@v2
  with:
    php-version: '8.0'
```

3. Check for PHP version constraints in static analysis configurations:
   - `phpstan.neon.dist` might have PHP version specific settings
   - `phpcs.xml.dist` might have PHP version specific ruleset configurations

Keeping PHP versions consistent across all configurations ensures that your development, testing, and deployment environments all work with the same PHP constraints.

## Advanced Features

### ACF JSON Sync

The boilerplate supports Advanced Custom Fields (ACF) JSON synchronization. All ACF field group JSON files should be stored in `resources/acf-json/`.

To enable ACF JSON sync in your plugin:

1. Create the `resources/acf-json` directory
2. Add the following code to your `load_dependencies()` method in the main Demo_Plugin class:

#### Option 1: Direct approach with anonymous functions

```php
/**
 * Load the required dependencies for this plugin.
 *
 * @since    1.0.0
 * @access   private
 */
private function load_dependencies(): void {
    $this->loader = new Loader();
    
    // ACF JSON Support
    add_filter(
        'acf/settings/save_json',
        function () {
            if ( wp_get_environment_type() !== 'production' ) {
                return DEMO_PLUGIN_PATH . 'resources/acf-json';
            }
            return '';
        }
    );
    add_filter(
        'acf/settings/load_json',
        function ( $paths ) {
            $paths[] = DEMO_PLUGIN_PATH . 'resources/acf-json';
            return $paths;
        }
    );
}
```

#### Option 2: Separate class for managing multiple field groups

For more complex setups with multiple field groups, create a dedicated class:

```php
<?php
/**
 * ACF Configuration.
 *
 * @package Demo_Plugin
 */

namespace Demo_Plugin\Integrations\ACF;

/**
 * ACF Configuration class.
 */
class Config {

    /**
     * Register all needed hooks.
     */
    public function __construct() {
        // Whitelist specific field groups to save
        add_filter( 'acf/settings/save_json/key=group_example1', array( $this, 'save_path' ) );
        add_filter( 'acf/settings/save_json/key=group_example2', array( $this, 'save_path' ) );
        add_filter( 'acf/settings/save_json/key=group_example3', array( $this, 'save_path' ) );
        
        // Add load path
        add_filter( 'acf/settings/load_json', array( $this, 'load_path' ) );
    }

    /**
     * Register the save path
     *
     * @return string The save path.
     */
    public function save_path(): string {
        if ( wp_get_environment_type() !== 'production' ) {
            return DEMO_PLUGIN_PATH . 'resources/acf-json';
        }
        return '';
    }

    /**
     * Register the load path.
     *
     * @param array<string> $paths The current paths.
     * @return array<string> The manipulated paths.
     */
    public function load_path( array $paths ): array {
        // Optional: Remove default path
        // unset( $paths[0] );
        
        $paths[] = DEMO_PLUGIN_PATH . 'resources/acf-json';
        return $paths;
    }
}
```

Then initialize it in your main plugin class:

```php
private function load_dependencies(): void {
    $this->loader = new Loader();
    
    // Initialize ACF configuration
    new \Demo_Plugin\Integrations\ACF\Config();
}
```

### Wrapping Up

That's it! Your plugin is now ready for development. Dive into creating your next remarkable WordPress plugin with ease and efficiency.

---
This plugin was created using the [wordpress-plugin-boilerplate](https://github.com/JUVOJustin/wordpress-plugin-boilerplate). Consult the upstream repository for changes, updates or IDE setups. LLM instructions can be found here: https://github.com/JUVOJustin/wordpress-plugin-boilerplate/wiki/LLM-AI. Keep this reference, to allow later updates.
