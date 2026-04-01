---
name: boilerplate-update
description: |
   Guide for updating WordPress plugins to the latest plugin-boilerplate features.
   Use when: (1) Updating build tools or adopting @wordpress/scripts, (2) Adopting
   new features like Abilities API, improved Loader, or i18n workflows, (3) Syncing
   CI/CD pipelines, QA configs, or dependencies, (4) User asks to "update from
   boilerplate", "sync with boilerplate", or "migrate to latest boilerplate".
compatibility: Requires PHP CLI and a WordPress plugin repository with standard plugin headers. This skill is intended for WordPress plugins only.
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

## Identity Replacement Script

Use `scripts/boilerplate-replace.php` (bundled with this skill) for deterministic
placeholder replacement. This avoids manual AI search-and-replace across copied
upstream files.

### Determine the target plugin identity

Before running the script, read these sources from the **target** plugin to determine
the three required parameters:

- **plugin-name** — `Plugin Name:` header in the main plugin PHP file (the root `.php` file containing `@wordpress-plugin` headers)
- **plugin-text-domain** — `Text Domain:` header in the same file
- **plugin-namespace** — the PSR-4 namespace mapped to `src/` in `composer.json` (`autoload.psr-4`), or the `namespace` declaration in `src/*.php` files

### Apply replacements on the cloned boilerplate

Run the script against the **cloned boilerplate directory** so every file in the
reference copy already carries the target plugin's identity before you compare or
copy anything:

```bash
php tmp/boilerplate-ref/.agents/skills/boilerplate-update/scripts/boilerplate-replace.php \
  --path tmp/boilerplate-ref \
  --plugin-name "My Awesome Plugin" \
  --plugin-namespace "My_Awesome_Plugin" \
  --plugin-text-domain "my-awesome-plugin" \
  --cleanup-setup
```

This does three things in one pass:
1. Replaces all boilerplate placeholders (`demo-plugin`, `Demo_Plugin`, etc.)
2. Strips all `BOILERPLATE-DOCS` marker sections
3. Removes setup-only artifacts (`setup.php`, `src/Cli/Setup.php`, composer autoload/hook entries)

After this step every `diff` between `tmp/boilerplate-ref/` and the target plugin
shows only real upstream changes -- no placeholder noise and no setup-only files.

### Rename an already-customised plugin

The script also supports renaming a plugin that has already been set up (i.e. the
boilerplate defaults have already been replaced). Pass the current identity as
`--source-plugin-*` options and the desired new identity as the regular
`--plugin-*` options:

```bash
php boilerplate-replace.php \
  --path /path/to/plugin \
  --source-plugin-name "My Awesome Plugin" \
  --source-plugin-namespace "My_Awesome_Plugin" \
  --source-plugin-text-domain "my-awesome-plugin" \
  --plugin-name "Better Plugin" \
  --plugin-namespace "Better_Plugin" \
  --plugin-text-domain "better-plugin"
```

When `--source-plugin-*` options are omitted, the script falls back to the
boilerplate defaults (`Demo Plugin` / `Demo_Plugin` / `demo-plugin`), which is
the correct behaviour for a fresh clone.

## Main Update Workflow

Follow this workflow in order. Do not start raw `diff` comparisons before step 3. The replacement script is what turns the cloned boilerplate into a meaningful reference copy.

1. Clone a fresh boilerplate reference:
   ```bash
   git clone --depth 1 https://github.com/JUVOJustin/wordpress-plugin-boilerplate.git tmp/boilerplate-ref
   ```
2. Determine the target plugin identity from the plugin being updated:
   - **plugin-name** — `Plugin Name:` header in the main plugin PHP file
   - **plugin-text-domain** — `Text Domain:` header in the same file
   - **plugin-namespace** — the PSR-4 namespace mapped to `src/` in `composer.json`, or the namespace used in `src/*.php`
3. Run the replacement script against the cloned boilerplate before comparing anything:
   ```bash
   php tmp/boilerplate-ref/.agents/skills/boilerplate-update/scripts/boilerplate-replace.php \
     --path tmp/boilerplate-ref \
     --plugin-name "My Awesome Plugin" \
     --plugin-namespace "My_Awesome_Plugin" \
     --plugin-text-domain "my-awesome-plugin" \
     --cleanup-setup
   ```
   This step replaces boilerplate placeholders, strips `BOILERPLATE-DOCS` sections, and removes setup-only files. All later `diff`s should compare the target plugin against `tmp/boilerplate-ref/`, not against the untouched upstream clone.
4. Compare PHP and QA.
   - Compare: `diff composer.json tmp/boilerplate-ref/composer.json`
   - Docs to read: `i18n.mdx`, `bundeling.mdx`
   - Key items:
     - Scripts: `i18n:extract`, `i18n:compile`, `phpstan`, `phpcs`, `phpcbf`
     - Strauss config in `extra.strauss` for namespace prefixing
     - QA config files: `phpcs.xml`, `phpstan.neon`
5. Compare JS and bundling.
   - Compare:
     - `diff package.json tmp/boilerplate-ref/package.json`
     - `diff webpack.config.js tmp/boilerplate-ref/webpack.config.js`
   - Docs to read: `bundeling.mdx`, `wp-env.mdx`, `create-blocks.mdx`
   - Key items:
     - `@wordpress/scripts` - Bundling, linting, formatting
     - `@wordpress/env` - Containerized WordPress for dev/CI
     - Scripts: `start`, `build` (with `--blocks-manifest`), `lint:*`, `format`, `create-block`, `env:*`, `test:php`
     - QA config: `.eslintrc`
6. Compare GitHub Actions.
   - Compare: `diff -r .github/workflows tmp/boilerplate-ref/.github/workflows`
   - Key items:
     - `setup.yml` - Reusable workflow with dependency caching
     - `test-analyse.yml` - PHPStan, PHPCS, JS linting, and PHPUnit application tests on push
     - `deploy.yml` - Release automation, translation compilation via wp-env
7. Compare `Loader.php`.
   - Compare: `diff src/Loader.php tmp/boilerplate-ref/src/Loader.php`
   - New methods:
     - `add_shortcode($tag, $component, $callback)`
     - `add_cli($name, $instance, $args)`
     - `add_ability($ability_class)` - Categories auto-register
8. Compare the main plugin class.
   - Compare: `diff src/*.php tmp/boilerplate-ref/src/Demo_Plugin.php`
   - New patterns:
     - `enqueue_entrypoint($entry)` - See `tmp/boilerplate-ref/docs/bundeling.mdx`
     - `register_blocks()` - See `tmp/boilerplate-ref/docs/create-blocks.mdx`
9. Compare the i18n workflow.
   - Key scripts: `i18n:extract` (creates `.pot`, updates `.po`) and `i18n:compile` (generates `.mo`, `.json`, `.php`)
   - Docs to read: `tmp/boilerplate-ref/docs/i18n.mdx`
10. Compare the Abilities API.
    - Docs to read: `tmp/boilerplate-ref/docs/abilities.mdx`
    - Key pattern: `$this->loader->add_ability(Abilities\My_Ability::class)`
11. Compare agent configuration.
    - Compare:
      - `diff -r .opencode/command tmp/boilerplate-ref/.opencode/command`
      - `diff -r .agents/skills tmp/boilerplate-ref/.agents/skills`
    - Docs to read: `tmp/boilerplate-ref/docs/work-with-ai.mdx`
    - Sync strategy:
      - Add new items from upstream
      - Update existing items; ask the user if the diff is significant
      - Ask before removing local-only items
      - Adapt text domain and paths after copying
      - Remove redundant skills/commands if upstream now includes them or if they were renamed
12. Compare file-control files.
    - Compare:
      - `diff .distignore tmp/boilerplate-ref/.distignore`
      - `diff .gitignore tmp/boilerplate-ref/.gitignore`
13. Present findings and apply changes incrementally.
    - Use one **subtask/subagent** per comparison area from steps 4-12, but only after step 3 is complete.
    - Categorize findings by confirmation requirement.
    - Apply changes incrementally instead of replacing everything wholesale.
14. Verify the result and clean up.
    - Run `composer install && npm install` after changes
    - Run `npm run build` after changes
    - Run `composer phpstan && composer phpcs` after changes
    - Run `npm run lint:js && npm run lint:style` after changes
    - Run `npm run test:php` after starting `wp-env` when PHPUnit-related files changed
    - Test plugin functionality
    - Clean up: `rm -rf tmp/boilerplate-ref`

## Documentation Reference

The boilerplate includes detailed docs at `tmp/boilerplate-ref/docs/`:

| File | Covers |
|------|--------|
| `index.mdx` | Documentation landing page that routes users to the right guide |
| `abilities.mdx` | Abilities API interfaces, category/ability creation, Loader registration |
| `bundeling.mdx` | wp-scripts bundling, entry points, asset enqueueing, localization |
| `i18n.mdx` | Translation workflow, extract/compile scripts, JSON translations, caveats |
| `create-blocks.mdx` | Block scaffolding, auto-registration via manifest, editor style sharing |
| `wp-env.mdx` | Docker-based dev environment, script structure, CI/CD usage |
| `testing.mdx` | PHPUnit application testing with wp-env, `tests-cli`, and common patterns |
| `github-actions.mdx` | CI/CD workflows, release process, and PHP version configuration |
| `integrations/acf.mdx` | ACF integration patterns, including field group JSON sync and configuration |
| `integrations/sentry.mdx` | Sentry bootstrap ordering and error monitoring integration |
| `work-with-ai.mdx` | AI integration: commands, skills, AGENTS.md, WordPress agent skills |
| `documentation.mdx` | Documentation structure, front matter metadata, heading rules |

Read these docs for implementation details. This skill only provides high-level guidance.
