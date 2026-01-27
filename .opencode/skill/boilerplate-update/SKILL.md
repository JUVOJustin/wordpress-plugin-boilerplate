---
name: boilerplate-update
description: |
  Guide for updating WordPress plugins to the latest plugin-boilerplate features.
  Use when: (1) Updating build tools or adopting @wordpress/scripts, (2) Adopting
  new features like Abilities API, improved Loader, or i18n workflows, (3) Syncing
  CI/CD pipelines, QA configs, or dependencies, (4) User asks to "update from
  boilerplate", "sync with boilerplate", or "migrate to latest boilerplate".
---

# Boilerplate Update Skill

Update WordPress plugins to the latest plugin-boilerplate features.

## User Confirmation Rules

**ASK for confirmation before:**
- Replacing or significantly restructuring files (Loader.php, main plugin class)
- Changing build tools or asset pipeline
- Modifying CI/CD workflows
- Adding new architectural patterns (Abilities API)

**Proceed without confirmation for:**
- Updating dependency versions
- Adding/renaming scripts with equivalent functionality
- Syncing QA config files
- Copying .opencode commands/skills

## Workflow

1. Clone fresh boilerplate for comparison:
   ```bash
   git clone --depth 1 https://github.com/JUVOJustin/wordpress-plugin-boilerplate.git ./tmp/boilerplate-ref
   ```

2. Compare key files using `diff` against `tmp/boilerplate-ref/`

3. Present findings to user, categorized by confirmation requirement

4. Apply changes incrementally

5. Cleanup: `rm -rf tmp/boilerplate-ref`

## Documentation Reference

The boilerplate includes detailed docs at `tmp/boilerplate-ref/docs/`:

| File | Covers |
|------|--------|
| `abilities.md` | Abilities API interfaces, category/ability creation, Loader registration |
| `i18n.md` | Translation workflow, extract/compile scripts, JSON translations, caveats |
| `create-blocks.md` | Block scaffolding, auto-registration via manifest, editor style sharing |
| `wp-env.md` | Docker-based dev environment, script structure, CI/CD usage |
| `acf-json-sync.md` | ACF field group JSON storage patterns |

Read these docs for implementation details. This skill only provides high-level guidance.

## Key Areas to Compare

### 1. PHP (composer.json & QA)

Compare: `diff composer.json tmp/boilerplate-ref/composer.json`

**New primitives:**
- `wp-cli/i18n-command` - Translation extraction/compilation
- Scripts: `i18n:extract`, `i18n:compile`, `phpstan`, `phpcs`, `phpcbf`
- Strauss config in `extra.strauss` for namespace prefixing

**QA config files:** `tmp/boilerplate-ref/phpcs.xml`, `tmp/boilerplate-ref/phpstan.neon`

### 2. JS (package.json & QA)

Compare: `diff package.json tmp/boilerplate-ref/package.json`

**New primitives:**
- `@wordpress/scripts` - Replaces other bundlers (webpack, bud.js, laravel-mix)
- `@wordpress/env` - Containerized WordPress for dev/CI
- Scripts: `start`, `build` (with `--blocks-manifest`), `lint:*`, `format`, `create-block`, `env:*`

**QA config files:** `tmp/boilerplate-ref/.eslintrc`

See `tmp/boilerplate-ref/docs/wp-env.md` for wp-env details.

### 3. webpack.config.js

Compare: `diff webpack.config.js tmp/boilerplate-ref/webpack.config.js`

Extends `@wordpress/scripts/config/webpack.config` with custom entry points.
Entry structure: `resources/{admin,frontend}/{js,scss}/app.{js,scss}`

### 4. GitHub Actions (.github/workflows/)

Compare: `diff -r .github/workflows tmp/boilerplate-ref/.github/workflows`

- `setup.yml` - Reusable workflow with dependency caching
- `test-analyse.yml` - PHPStan, PHPCS, JS linting on push
- `deploy.yml` - Release automation, translation compilation via wp-env

### 5. Loader.php

Compare: `diff src/Loader.php tmp/boilerplate-ref/src/Loader.php`

**New methods:**
- `add_shortcode($tag, $component, $callback)`
- `add_cli($name, $instance, $args)`
- `add_ability($ability_class)` - Categories auto-register

### 6. Main Plugin Class

Compare: `diff src/*.php tmp/boilerplate-ref/src/Demo_Plugin.php`

**New patterns:**
- `enqueue_entrypoint($entry)` - Asset loading with `.asset.php` metadata
- `register_blocks()` - Uses `wp_register_block_types_from_metadata_collection()` (WP 6.8+)

See `tmp/boilerplate-ref/docs/create-blocks.md` for block handling details.

### 7. i18n Workflow

Scripts: `i18n:extract` (creates .pot, updates .po) and `i18n:compile` (generates .mo, .json, .php)

See `tmp/boilerplate-ref/docs/i18n.md` for workflow, caveats, and AI command.

### 8. Abilities API (WordPress 6.9+)

Exposes plugin functionality via structured interface with input/output schemas.
Registration: `$this->loader->add_ability(Abilities\My_Ability::class)`

See `tmp/boilerplate-ref/docs/abilities.md` for interface reference and examples.

### 9. .opencode/ Configuration

Compare: `diff -r .opencode tmp/boilerplate-ref/.opencode`

**Commands and skills:**
- Add new items from upstream
- Update existing (ask user if diff is significant)
- Ask user before removing local-only items

After copying, adapt text domain and paths.

## String Replacement

| Placeholder | Replace With |
|-------------|--------------|
| `demo-plugin` | Plugin slug (lowercase, hyphens) |
| `Demo_Plugin` | Namespace (PascalCase, underscores) |
| `DEMO_PLUGIN` | Constant prefix (UPPERCASE, underscores) |

## Verification

1. `composer install && npm install`
2. `npm run build`
3. `composer phpstan && composer phpcs`
4. `npm run lint:js && npm run lint:style`
5. Validate `setup.php` as well as its cli and composer command are removed
6. Validate all content wrapped with `<!-- BOILERPLATE-DOCS-START -->` comments is deleted
7. Test plugin functionality
