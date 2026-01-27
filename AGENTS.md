## AI Coding Agent Instructions for plugin-boilerplate

This plugin is a modern WordPress plugin with strict conventions and automated workflows. Follow these guidelines.

### Documentation

Detailed guides are available in `docs/`:

| File | Covers |
|------|--------|
| `abilities.md` | Abilities API: interfaces, category/ability creation, Loader registration |
| `bundeling.md` | wp-scripts bundling, entry points, asset enqueueing, localization |
| `i18n.md` | Translation workflow, extract/compile scripts, JSON translations |
| `create-blocks.md` | Block scaffolding, auto-registration, editor style sharing |
| `wp-env.md` | Docker dev environment, script structure, CI/CD usage |
| `acf-json-sync.md` | ACF field group JSON storage patterns |

Read docs for implementation details. This file provides high-level guidance only.

### Architecture & Source Layout

- **All plugin logic lives in `src/`**. Organize by feature/context (e.g., `Admin/`, `Frontend/`, `CLI/`, `Integrations/`).
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

- **Blocks**: Run `npm run create-block`. See `docs/create-blocks.md`.
- **Abilities API**: Implement interfaces in `src/Abilities/`, register via Loader. See `docs/abilities.md`.
- **i18n**: Extract with `composer run i18n:extract`, compile with `composer run i18n:compile`. See `docs/i18n.md`.
- **wp-env**: Start with `npm run env:start`. See `docs/wp-env.md`.

### Maintaining the Boilerplate

When adding new primitives, patterns, or documentation to this boilerplate:

1. **Update `docs/`** with detailed implementation guides
2. **Update this file** with high-level reference
<!-- BOILERPLATE-DOCS-START -->
3. **Update `.opencode/skill/boilerplate-update/SKILL.md`** so downstream plugins can adopt changes
<!-- BOILERPLATE-DOCS-END -->
