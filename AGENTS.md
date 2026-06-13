## AI Coding Agent Instructions for Demo Plugin

<!-- BOILERPLATE-DOCS-START -->
This project is a boilerplate of a WordPress plugin for other developers to be used as a Quick Start. Code and comments wrapped in `<!-- BOILERPLATE-DOCS-START --><!-- BOILERPLATE-DOCS-END -->` will be removed after the @cli/Setup.php runs.

Instead of calling this a boilerplate, reference the boilerplate by "Demo Plugin" since this will be replaced with the plugin name during setup.
<!-- BOILERPLATE-DOCS-END -->

This plugin is a modern WordPress plugin with strict conventions and automated workflows. Follow these guidelines.

### Architecture & Source Layout

- **All plugin logic lives in `src/`**. Organize by feature/context (e.g., `Admin/`, `Frontend/`, `Integrations/`).
- **Main plugin file (`demo-plugin.php`) only bootstraps**. Never place business logic here.
- **Loader pattern**: Register hooks, filters, shortcodes, CLI commands, and abilities via the `Loader` class. Do not register hooks in constructors.

### Asset Management

- **Assets**: Place in `resources/admin/` and `resources/frontend/`.
- **Build**: `@wordpress/scripts` handles compilation. Entry points in `webpack.config.js`.
- **Scripts**: `npm run start` (watch), `npm run build` (production).

### Quality Assurance

- **PHP**: PHPStan (`phpstan.neon`), PHPCS (`phpcs.xml`)
- **JS**: ESLint (`.eslintrc`)
- **CI/CD**: GitHub Actions in `.github/workflows/`

### Key Primitives

**Loader methods:**
- `add_action()`, `add_filter()` - WordPress hooks
- `add_shortcode()` - Shortcode registration
- `add_cli()` - WP-CLI commands
- `add_ability()` - Abilities API (WP 6.9+)

**Composer scripts:** `phpstan`, `phpcs`, `phpcbf`, `i18n:extract`, `i18n:compile`

**NPM scripts:** `start`, `build`, `lint:js`, `lint:style`, `format`, `create-block`, `env:*`

### Feature Quick Reference

- **Blocks**: Run `npm run create-block`. Registration is automatic; `tests/php/BlockRegistrationTest.php` generically guards that every built block is loaded (via `/wp/v2/block-types`) and its assets exist. Use the `wp-plugin-bp` skill for block guidance.
- **Abilities API**: Implement interfaces in `src/Abilities/`, register via Loader. `tests/php/AbilityRegistrationTest.php` generically guards that every `Ability_Interface` implementation (and its category) is registered. Use the `wp-plugin-bp` skill for ability guidance.
- **i18n**: Extract with `composer run i18n:extract`, compile with `composer run i18n:compile`. Use the `wp-plugin-bp` skill for translation work.
- **wp-env**: Start with `npm run env:start`. Use the `wp-plugin-bp` skill when tests are involved.
- **Testing**: Run application tests with `npm run test:php`. Use the `wp-plugin-bp` skill for testing guidance.
- **Plugin upgrades**: Use the `wp-plugin-bp` skill or ask naturally to sync with upstream project conventions.
- **Official WordPress skills**: `.agents/skills/wp-*/` contains focused skills for block development, Interactivity API, PHPStan, project triage, and REST API work. Use the `wp-plugin-bp` skill `wp-skills` workflow to refresh or add official WordPress skills.
- **Composer setup**: `.agents/` ships in the initial Composer package so setup can run `wp-plugin-bp/scripts/plugin-replace.php`; replacement cleanup removes `.agents/`, then setup asks whether to install agent skills for ongoing work.
- **Missing skills**: If `wp-plugin-bp` is unavailable in an initialized plugin, install it with `npx skills add https://github.com/JUVOJustin/wordpress-plugin-boilerplate --skill=*`.

### Maintaining the plugin

When adding new primitives, patterns, or documentation to this plugin:

1. Update `docs/` with detailed implementation guides
2. Update @AGENTS.md with high-level reference
<!-- BOILERPLATE-DOCS-START -->
3. Update @.agents/skills/wp-plugin-bp so downstream plugins can adopt changes
<!-- BOILERPLATE-DOCS-END -->
