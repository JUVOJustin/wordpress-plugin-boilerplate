## AI Coding Agent Instructions for Demo Plugin

<!-- BOILERPLATE-DOCS-START -->
This project is a boilerplate of a WordPress plugin for other developers to be used as a Quick Start. Code and comments wrapped in `<!-- BOILERPLATE-DOCS-START --><!-- BOILERPLATE-DOCS-END -->` will be removed after the @cli/Setup.php runs.

Instead of calling this a boilerplate, reference the boilerplate by "Demo Plugin" since this will be replaced with the plugin name during setup.
<!-- BOILERPLATE-DOCS-END -->

This plugin is a modern WordPress plugin with strict conventions and automated workflows. Follow these guidelines.

### Documentation

| File               | Covers |
|--------------------|--------|
| @docs/index.mdx | Documentation landing page and guide selection |
| @docs/abilities.mdx | Abilities API: interfaces, category/ability creation, Loader registration |
| @docs/acf-json-sync.mdx | Root-level ACF JSON sync workflow and environment-aware save/load behavior |
| @docs/bundeling.mdx      | wp-scripts bundling, entry points, asset enqueueing, localization |
| @docs/i18n.mdx           | Translation workflow, extract/compile scripts, JSON translations |
| @docs/create-blocks.mdx  | Block scaffolding, auto-registration, editor style sharing |
| @docs/wp-env.mdx         | Docker dev environment, script structure, CI/CD usage |
| @docs/testing.mdx        | PHPUnit application testing with wp-env bootstrap and scripts |
| @docs/github-actions.mdx | CI/CD workflows, release process, PHP version configuration |
| @docs/integrations/acf.mdx | ACF integration patterns and field group JSON storage |
| @docs/integrations/sentry.mdx | Sentry SDK bootstrap and early error capture patterns |
| @docs/work-with-ai.mdx   | AI integration: commands, skills, AGENTS.md, WordPress agent skills |
| @docs/documentation.mdx  | Documentation structure, front matter metadata, heading rules |

- Read docs for implementation details.
- Keep the docs updated
- Suggest to implement new docs when adding new features or patterns.

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

- **Blocks**: Run `npm run create-block`. See `docs/create-blocks.mdx`.
- **Abilities API**: Implement interfaces in `src/Abilities/`, register via Loader. See `docs/abilities.mdx`.
- **i18n**: Extract with `composer run i18n:extract`, compile with `composer run i18n:compile`. See `docs/i18n.mdx`.
- **wp-env**: Start with `npm run env:start`. See `docs/wp-env.mdx`.
- **Testing**: Run application tests with `npm run test:php`. See `docs/testing.mdx`. Use the `application-testing` skill when writing tests.

### Maintaining the plugin

When adding new primitives, patterns, or documentation to this plugin:

1. Update `docs/` with detailed implementation guides
2. Update @AGENTS.md with high-level reference
<!-- BOILERPLATE-DOCS-START -->
3. Update @.agents/skills/boilerplate-update so downstream plugins can adopt changes
<!-- BOILERPLATE-DOCS-END -->
