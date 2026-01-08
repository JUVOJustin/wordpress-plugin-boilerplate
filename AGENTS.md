## AI Coding Agent Instructions for plugin-boilerplate

This repository is a modern WordPress plugin boilerplate with strict conventions and automated workflows. Follow these guidelines to be immediately productive:

### Architecture & Source Layout
- **All plugin logic lives in `src/`**. Organize by feature/context (e.g., `Admin/`, `Frontend/`, `CLI/`, `Integrations/`).
- **Main plugin file (`demo-plugin.php`) only bootstraps**. Never place business logic here.
- **Loader pattern**: Register all hooks, filters, and shortcodes via the `Loader` class in your main plugin class. Do not register hooks in constructors.
	- Example: See `Demo_Plugin.php` for `define_admin_hooks`, `define_public_hooks`, and `define_shortcodes` methods.

### Asset Management
- **Frontend/admin assets**: Place in `resources/admin/` and `resources/frontend/`.
- **Use "@wordpress/scripts" for asset compilation**. Entry points are defined in `webpack.config.js`.
	- Build: `npm run start` (watch) or `npm run build` (minify).

### Quality Assurance & Workflows
- **Static analysis**: Use PHPStan (`phpstan.neon`), PHPCS (`phpcs.xml`), and ESLint (`eslint.config.js`).
- **CI/CD**: GitHub Actions run tests, static analysis, and asset builds on push. See `.github/workflows/` for details.
- **PHP version**: Update in `composer.json` (`require` and `config.platform`), and in all workflow YAMLs for consistency.

### Project-Specific Patterns
- **Namespace everything**: All classes use namespaces, autoloaded via Composer.
- **Automatic namespace prefixing**: Strauss is used for dependency isolation.
- **Shortcodes/CLI**: Register via loader, not directly in classes.
- **Integrations**: Dedicated subfolders for BricksBuilder, Elementor, ACF, WooCommerce, etc.

### Key Files & Directories
- `src/` — All PHP source code
- `resources/` — All assets (JS, CSS, images, ACF JSON)
- `webpack.config.js` — Asset build config
- `.github/workflows/` — CI/CD pipelines
- `composer.json` — Dependency and PHP version management
- `phpstan.neon`, `phpcs.xml`, `eslint.config.js` — QA configs

### Example: Registering an Admin Script
```php
private function define_admin_hooks() {
		$admin = new Admin\Admin($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
}
```

### Block Generation
To generate a new Gutenberg block, simply run `npm run create-block` and enter the required information when prompted.
This will create a new block in the `src/Blocks/` folder. The block will be automatically registered and the assets enqueued.

### Interactivity
For "interactivity" use `@wordpress/interactivity`. It requires the view script to be a module. Register it with `"viewScriptModule": "file:./view.js"` in `block.json`.
The modules are enqueued automatically bu if you need to load manually e.g. in Elementor or Bricks use:

```php
$block = WP_Block_Type_Registry::get_instance()->get_registered( "demo-plugin/my-block" );
if ( $block && ! empty( $block->view_script_module_ids ) ) {
    foreach ( $block->view_script_module_ids as $script_module_id ) {
        wp_enqueue_script_module( $script_module_id );
    }
}
```

### i18n/Translations Support 
1. Run `npm run i18n:extract` to extract translatable strings into the `.pot` file located in the `languages/` directory. Strings in the PHP/JS need to use functions like `__()` or `_e()` with the plugin's text domain. Existing `.po` files will be updated automatically.
2. From the `.pot` file, translate by creating `.po` files for each desired language.
3. Run `npm run i18n:compile` to compile the `.po` files into `.mo`, `.json` and `.php` files for use by WordPress.