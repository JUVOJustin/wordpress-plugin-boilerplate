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
- Adding new architectural patterns (Abilities API, Blocks)
- Deleting resources belonging to plugin features

**Proceed without confirmation for:**
- Updating dependency versions
- Adding/renaming scripts with equivalent functionality
- Syncing QA config files
- Copying .opencode commands and .agents/skills

## Setup-Only Tooling (Exclude from Updates)

`setup.php`, `src/Cli/Setup.php`, and the `wp setup` CLI command exist only for
initial project creation. When updating an existing plugin, do **not** upsert or
reintroduce these items.

**Ignore diffs for:**
- `setup.php`
- `src/Cli/Setup.php`
- `composer.json` entries: `autoload.files` for `setup.php`,
  `scripts.post-create-project-cmd` for `composer exec -- wp setup`
- Any Loader registration for the `wp setup` CLI command

## Workflow

1. Clone fresh boilerplate for comparison:
   ```bash
   git clone --depth 1 https://github.com/JUVOJustin/wordpress-plugin-boilerplate.git ./tmp/boilerplate-ref
   ```
2. Use one **subtask/subagent** per key area. Delegate comparison using `diff` against `tmp/boilerplate-ref/`

3. Present findings to user, categorized by confirmation requirement

4. Apply changes incrementally

5. Cleanup: `rm -rf tmp/boilerplate-ref`

## Documentation Reference

The boilerplate includes detailed docs at `tmp/boilerplate-ref/docs/`:

| File | Covers |
|------|--------|
| `abilities.mdx` | Abilities API interfaces, category/ability creation, Loader registration |
| `bundeling.mdx` | wp-scripts bundling, entry points, asset enqueueing, localization |
| `i18n.mdx` | Translation workflow, extract/compile scripts, JSON translations, caveats |
| `create-blocks.mdx` | Block scaffolding, auto-registration via manifest, editor style sharing |
| `wp-env.mdx` | Docker-based dev environment, script structure, CI/CD usage |
| `acf-json-sync.mdx` | ACF field group JSON storage patterns |
| `work-with-ai.mdx` | AI integration: commands, skills, AGENTS.md, WordPress agent skills |
| `documentation.mdx` | Documentation structure, front matter metadata, heading rules |

Read these docs for implementation details. This skill only provides high-level guidance.

## Key Areas to Compare

### 1. PHP (composer.json & QA)

Compare: `diff composer.json tmp/boilerplate-ref/composer.json`

See docs for details: `i18n.mdx`, `bundeling.mdx`

**Key items:**
- `wp-cli/i18n-command` - Translation extraction/compilation
- Scripts: `i18n:extract`, `i18n:compile`, `phpstan`, `phpcs`, `phpcbf`
- Strauss config in `extra.strauss` for namespace prefixing
- QA config files: `phpcs.xml`, `phpstan.neon`
- Skip setup-only entries called out above

### 2. JS & Bundling (package.json, webpack.config.js)

Compare:
- `diff package.json tmp/boilerplate-ref/package.json`
- `diff webpack.config.js tmp/boilerplate-ref/webpack.config.js`

See docs for details: `bundeling.mdx`, `wp-env.mdx`, `create-blocks.mdx`

**Key items:**
- `@wordpress/scripts` - Bundling, linting, formatting
- `@wordpress/env` - Containerized WordPress for dev/CI
- Scripts: `start`, `build` (with `--blocks-manifest`), `lint:*`, `format`, `create-block`, `env:*`
- QA config: `.eslintrc`

### 3. GitHub Actions (.github/workflows/)

Compare: `diff -r .github/workflows tmp/boilerplate-ref/.github/workflows`

- `setup.yml` - Reusable workflow with dependency caching
- `test-analyse.yml` - PHPStan, PHPCS, JS linting on push
- `deploy.yml` - Release automation, translation compilation via wp-env

### 4. Loader.php

Compare: `diff src/Loader.php tmp/boilerplate-ref/src/Loader.php`

**New methods:**
- `add_shortcode($tag, $component, $callback)`
- `add_cli($name, $instance, $args)`
- `add_ability($ability_class)` - Categories auto-register

### 5. Main Plugin Class

Compare: `diff src/*.php tmp/boilerplate-ref/src/Demo_Plugin.php`

**New patterns:**
- `enqueue_entrypoint($entry)` - See `tmp/boilerplate-ref/docs/bundeling.mdx`
- `register_blocks()` - See `tmp/boilerplate-ref/docs/create-blocks.mdx`

### 6. i18n Workflow

Scripts: `i18n:extract` (creates .pot, updates .po) and `i18n:compile` (generates .mo, .json, .php)

See `tmp/boilerplate-ref/docs/i18n.mdx` for workflow, caveats, and AI command.

### 7. Abilities API (WordPress 6.9+)

See `tmp/boilerplate-ref/docs/abilities.mdx` for interface reference and examples.

**Key pattern:** `$this->loader->add_ability(Abilities\My_Ability::class)`

### 8. Agent Configuration

Compare:
- `diff -r .opencode/command tmp/boilerplate-ref/.opencode/command`
- `diff -r .agents/skills tmp/boilerplate-ref/.agents/skills`

See `tmp/boilerplate-ref/docs/work-with-ai.mdx` for AI integration details.

**Sync strategy:**
- Add new items from upstream
- Update existing (ask user if diff is significant)
- Ask user before removing local-only items
- Adapt text domain and paths after copying
- Remove redundant skills/commands if upstream now includes them or they are possibly renamed

### 9. File Control files
Compare:
- `diff .distignore tmp/boilerplate-ref/.disignore`
- `diff .gitignore tmp/boilerplate-ref/.gitignore`

## String Replacement

| Placeholder | Replace With |
|-------------|--------------|
| `demo-plugin` | Plugin slug (lowercase, hyphens) |
| `Demo_Plugin` | Namespace (PascalCase, underscores) |
| `DEMO_PLUGIN` | Constant prefix (UPPERCASE, underscores) |

## Verification

1. Ran `composer install && npm install` after changes
2. Ran `npm run build` after changes
3. Ran `composer phpstan && composer phpcs` after changes
4. Ran `npm run lint:js && npm run lint:style` after changes
5. Confirm setup-only tooling stays excluded (`setup.php`, setup CLI, composer hook)
6. Validate all content wrapped with `<!-- BOILERPLATE-DOCS-START -->` comments is deleted
7. All `demo-plugin`, `Demo_Plugin`, and `DEMO_PLUGIN` strings are replaced appropriately
8. Test plugin functionality
