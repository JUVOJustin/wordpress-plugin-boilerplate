# Upgrade Command

Sync this WordPress plugin with the latest upstream project conventions. Upgrade work is fragile, so follow this workflow in order.

## Confirmation Rules

Ask for confirmation before:

- Replacing or significantly restructuring files such as `Loader.php` or the main plugin class
- Changing build tools or the asset pipeline
- Modifying CI/CD workflows
- Adding new architectural patterns such as Abilities API or blocks
- Deleting resources belonging to plugin features

Proceed without confirmation for:

- Updating dependency versions
- Adding or renaming scripts with equivalent functionality
- Syncing QA config files
- Installing or updating `.agents/skills` with `npx skills add`

## Identity Replacement Script

Use `scripts/plugin-replace.php` from this skill for deterministic identity replacement. This avoids manual search-and-replace across copied upstream files.

### Determine the target plugin identity

Read these sources from the target plugin:

- **plugin-name**: `Plugin Name:` header in the root plugin PHP file containing `@wordpress-plugin`
- **plugin-text-domain**: `Text Domain:` header in the same file
- **plugin-namespace**: the PSR-4 namespace mapped to `src/` in `composer.json`, or the namespace declaration in `src/*.php`

### Apply replacements on the cloned reference

Run the script against the cloned reference directory before comparing anything:

```bash
php tmp/plugin-ref/.agents/skills/wp-plugin-bp/scripts/plugin-replace.php \
  --path tmp/plugin-ref \
  --plugin-name "My Awesome Plugin" \
  --plugin-namespace "My_Awesome_Plugin" \
  --plugin-text-domain "my-awesome-plugin" \
  --cleanup-setup
```

This pass replaces placeholders in plugin files, strips setup-only docs sections, empties `docs/`, removes setup-only files, and deletes `.agents/`. It does not rewrite skill files; initialized plugins can reinstall current skills from upstream with `npx skills add`. Later diffs should compare the target plugin against `tmp/plugin-ref/`, not against the untouched upstream clone.

### Rename an already-customized plugin

Pass the current identity as `--source-plugin-*` options and the desired identity as the regular `--plugin-*` options:

```bash
php .agents/skills/wp-plugin-bp/scripts/plugin-replace.php \
  --path /path/to/plugin \
  --source-plugin-name "My Awesome Plugin" \
  --source-plugin-namespace "My_Awesome_Plugin" \
  --source-plugin-text-domain "my-awesome-plugin" \
  --plugin-name "Better Plugin" \
  --plugin-namespace "Better_Plugin" \
  --plugin-text-domain "better-plugin"
```

## Main Upgrade Workflow

1. Clone a fresh upstream reference. Prefer the upstream URL documented in the plugin repository; if no upstream is documented, ask the user for it:
   ```bash
   git clone --depth 1 <upstream-url> tmp/plugin-ref
   ```
2. Determine the target plugin identity from the plugin being updated.
3. Run the replacement script against the cloned reference before comparing anything.
4. Detemine parts to upgrade. If user did not specifiy upgrade all.
5. Apply upgrade. Per part use one subagent.
6. Verify and clean up:
 - run `composer install` and `npm install` if dependency files changed
 - run `npm run build`
 - run `composer run phpstan` and `composer run phpcs`
 - run `npm run lint:js` and `npm run lint:style` when JS or styles changed
 - run `npm run test:php` after starting wp-env when PHPUnit behavior changed
 - remove `tmp/plugin-ref`

## Upgradable parts
### PHP and QA:
   - `composer.json`
   - `phpcs.xml`
   - `phpstan.neon`
   - reference docs to consult only: `references/doc-i18n.mdx`, `references/doc-bundling.mdx`
### JS and bundling:
   - `package.json`
   - `webpack.config.js`
   - reference docs to consult only: `references/doc-bundling.mdx`, `references/doc-wp-env.mdx`, `references/doc-create-blocks.mdx`
### GitHub Actions:
   - `.github/workflows`
   - key workflows: setup, analysis, tests, deploy, release, translation compilation
   - reference docs to consult only: `references/doc-github-actions.mdx`
### `src/Loader.php`:
   - shortcode registration
   - WP-CLI registration
   - Abilities API registration
### Main plugin class:
   - asset enqueueing via entry points
   - block registration
   - loader registration patterns
### i18n workflow:
   - `i18n:extract`
   - `i18n:compile`
   - generated language file expectations
### Abilities API:
    - interfaces under `src/Abilities/`
    - loader `add_ability()` usage
    - reference docs to consult only: `references/doc-abilities.mdx`
### Agent configuration:
    - `.agents/skills`
    - prefer `npx skills add https://github.com/JUVOJustin/wordpress-plugin-boilerplate --skill=*` for the boilerplate skill
    - add new upstream items, update existing items, ask before removing local-only items
### File-control files:
    - `.distignore`
    - `.gitignore`
### Present findings and apply changes incrementally:
    - categorize changes by whether confirmation is required
    - avoid replacing whole files when a scoped patch is enough
    - adapt text domain, namespace, paths, and plugin-specific behavior after copying

## Reference Documentation Map

Load only the docs needed for the current comparison. These files explain upstream conventions; they are not files to copy or sync into the target plugin's `docs/` directory.

| Reference | Covers |
| --- | --- |
| `references/doc-abilities.mdx` | Abilities API |
| `references/doc-bundling.mdx` | wp-scripts bundling, entry points, enqueueing |
| `references/doc-i18n.mdx` | Translation workflow |
| `references/doc-create-blocks.mdx` | Block scaffolding and registration |
| `references/doc-wp-env.mdx` | wp-env |
| `references/doc-testing.mdx` | PHPUnit application testing |
| `references/doc-github-actions.mdx` | CI/CD workflows |
| `references/doc-acf.mdx` | ACF integration |
| `references/doc-action-scheduler.mdx` | Action Scheduler |
| `references/doc-sentry.mdx` | Sentry integration |
