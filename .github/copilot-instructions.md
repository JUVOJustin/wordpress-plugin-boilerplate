## AI Coding Agent Instructions for plugin-boilerplate

This repository is a modern WordPress plugin boilerplate with strict conventions and automated workflows. Follow these guidelines to be immediately productive:

### Architecture & Source Layout
- **All plugin logic lives in `src/`**. Organize by feature/context (e.g., `Admin/`, `Frontend/`, `CLI/`, `Integrations/`).
- **Main plugin file (`demo-plugin.php`) only bootstraps**. Never place business logic here.
- **Loader pattern**: Register all hooks, filters, and shortcodes via the `Loader` class in your main plugin class. Do not register hooks in constructors.
	- Example: See `Demo_Plugin.php` for `define_admin_hooks`, `define_public_hooks`, and `define_shortcodes` methods.

### Asset Management
- **Frontend/admin assets**: Place in `resources/admin/` and `resources/frontend/`.
- **Use Bud.js for asset compilation**. Entry points are defined in `bud.config.js`.
	- Build: `npm run development` (watch) or `npm run production` (minify).
- **ACF JSON sync**: Store field group JSON in `resources/acf-json/`. Enable via filters in your main class or a dedicated config class (see `Integrations/ACF/Config.php`).

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
- `bud.config.js` — Asset build config
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

### Example: Adding a Bud.js Entry Point
```js
app.entry({
	admin: ['resources/admin/js/admin.js', 'resources/admin/css/admin.scss'],
	frontend: ['resources/frontend/js/frontend.js', 'resources/frontend/css/frontend.scss'],
});
```

## WordPress Instructions
* When working on wordpress code like plugins or themes read and follow: @https://raw.githubusercontent.com/JUVOJustin/wordpress-llm-rules/main/base.md
* When building queries read and follow: @https://raw.githubusercontent.com/JUVOJustin/wordpress-llm-rules/main/queries.md