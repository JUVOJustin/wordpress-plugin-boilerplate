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

### OpenCode Commands

This repository includes OpenCode commands for common development tasks. These commands can be executed by any LLM agent:

- `/qa-run` — Run quality assurance tools (PHPStan, PHPCS, ESLint)
- `/qa-upsert` — Update QA tools from the boilerplate
- `/rules-upsert` — Sync WordPress development rules
- `/commands-upsert` — Update OpenCode commands from the boilerplate
- `/strauss-upsert` — Update Strauss namespace prefixing package
- `/readme-update` — Update or create the README.md file
- `/agent-skills-sync` — Sync WordPress agent skills from Automattic/agent-skills

### WordPress Agent Skills

This project can integrate specialized WordPress agent skills from [Automattic/agent-skills](https://github.com/Automattic/agent-skills). Skills provide deep knowledge about specific WordPress development patterns.

To install agent skills, use the `/agent-skills-sync` command. Once installed, skills are available at `.opencode/skill/<skill-name>/`.

**Common skills:**
- **wp-interactivity-api** — Use when building or debugging WordPress Interactivity API features (data-wp-* directives, @wordpress/interactivity store/state/actions)
  - Read when working with: Interactivity API, directives, hydration, viewScriptModule
  - Skill documentation: @.opencode/skill/wp-interactivity-api/SKILL.md
- **wp-project-triage** — Use for deterministic inspection of WordPress repositories (plugin/theme/core)
  - Read when: Starting work in a new WordPress repository, need to understand project structure
  - Skill documentation: @.opencode/skill/wp-project-triage/SKILL.md
- **wp-block-development** — Use when developing or debugging Gutenberg blocks
  - Read when: Creating blocks, working with block.json, attributes, dynamic rendering
  - Skill documentation: @.opencode/skill/wp-block-development/SKILL.md

**To sync/update skills:** Run `/agent-skills-sync` command.

**Additional available skills:**
- `wp-block-themes` — Block theme development
- `wp-performance` — WordPress performance optimization
- `wp-plugin-development` — Plugin development best practices
- `wp-wpcli-and-ops` — WP-CLI commands and operations

Visit [Automattic/agent-skills](https://github.com/Automattic/agent-skills) for the complete list.