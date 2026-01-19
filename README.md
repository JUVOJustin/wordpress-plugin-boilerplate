# WordPress Plugin Boilerplate

[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-blue)](https://phpstan.org/)
[![PHPCS](https://img.shields.io/badge/PHPCS-WordPress-green)](https://github.com/WordPress/WordPress-Coding-Standards)
[![Test/Analyse](https://github.com/JUVOJustin/wordpress-plugin-boilerplate/actions/workflows/test-analyse.yml/badge.svg)](https://github.com/JUVOJustin/wordpress-plugin-boilerplate/actions/workflows/test-analyse.yml)

A modern, organized, and object-oriented foundation for building high-quality WordPress plugins. Fork of [WordPress Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate).

## Features
- Namespaces support using composer
- Automatic Namespace prefixing with [Strauss](https://github.com/BrianHenryIE/strauss)
- Easy Shortcode, CLI Command Registration through the loader
- PHPStan with ready-made Github actions
- PHPCS with ready-made Github actions
- [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) for simple bundling, linting and formatting of JS and CSS/SCSS files
- Ready-made Github actions for building and bundling
- Ready-made [opencode](https://opencode.ai) commands for common tasks (compatible with any LLM)
- Simple [Gutenberg Block generation](/docs/create-blocks.md) and automated loading

## Setup

### Step 1: Create Your Project

```bash
composer create-project juvo/wordpress-plugin-boilerplate
```

### Step 2: Configure Your Plugin
Upon project creation, you'll be guided through a series of prompts to configure your plugin:

- **Plugin Name**: Enter the name of your plugin.
- **Namespace (optional)**: Suggests a default namespace based on your plugin name but allows customization.
- **Plugin Slug (optional)**: Choose a slug for your plugin; a default based on your plugin name is suggested.

Your inputs will automatically tailor the boilerplate to match your plugin's identity.

### Step 3: Finalization

The setup replaces placeholders, renames files, and runs `composer update` and `npm install`.

## Project Structure

```
src/
├── Blocks/            # Gutenberg Blocks
├── API/               # REST API functionality
├── CLI/               # CLI commands
└── Integrations/      # Third-party integrations (Bricks, Elementor, ACF, WooCommerce)

resources/
├── admin/             # Admin assets (JS, SCSS)
├── frontend/          # Frontend assets (JS, SCSS)
└── acf-json/          # ACF field group JSON files
```

## Development

### Loader Pattern

Register hooks, filters, and shortcodes via the loader in the main class:

```php
private function define_admin_hooks() {
    $admin = new Admin\Admin($this->get_plugin_name(), $this->get_version());
    $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
}

private function define_shortcodes() {
    $example = new Shortcodes\ExampleShortcode();
    $this->loader->add_shortcode('example', $example, 'render');
}
```

### Asset Compilation

```bash
npm run start    # Watch mode for development
npm run build    # Production build
```

See [Bundling Documentation](/docs/bundeling.md) for details on entrypoints and configuration.

### Gutenberg Blocks

```bash
npm run create-block
```

Blocks are auto-registered and assets auto-enqueued. See [Block Development](/docs/create-blocks.md).

### Quality Assurance

- **PHPCS**: WordPress coding standards
- **PHPStan**: Static analysis (Level 6)
- **wp-scripts**: JS/CSS linting and formatting
- **GitHub Actions**: Automated testing and building

## Documentation

| Topic | Description |
|-------|-------------|
| [Bundling](/docs/bundeling.md) | Asset compilation and webpack configuration |
| [Block Development](/docs/create-blocks.md) | Gutenberg block creation and registration |
| [i18n](/docs/i18n.md) | Internationalization and translations |
| [ACF JSON Sync](/docs/acf-json-sync.md) | ACF field group version control |

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

2. Also update the PHP version in `.github/workflows/setup.yml`:

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

---

This plugin was created using the [wordpress-plugin-boilerplate](https://github.com/JUVOJustin/wordpress-plugin-boilerplate).
