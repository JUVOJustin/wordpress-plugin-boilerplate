# Upgrade Command

Sync this WordPress plugin with the latest upstream project conventions. Upgrade work is fragile, so follow this workflow in order.

## Confirmation Rules

The upgrade report flags which areas require confirmation; these rules are the policy behind that flag.

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
4. Run the automated upgrade check to generate the comparison report:
   ```bash
   node tmp/plugin-ref/.agents/skills/wp-plugin-bp/scripts/upgrade-check.js \
     --target . --ref tmp/plugin-ref
   ```
   Pass `--ref` the path where you actually cloned the reference; it accepts any
   absolute or relative path, so the clone does not have to live in `tmp/plugin-ref`.
   The script compares every upgradable part against the rewritten reference and prints a
   Markdown report to stdout: dependency/version mismatch tables, script mismatches, and
   per-file diffs, with each area marked `clean`, `review`, `not-applicable`, or `skipped`.
   Old plugins missing most areas are handled gracefully (absent files are classified, not
   errors). Use `--max-diff-lines` to cap diff size. Read the report's "How to act on this
   report" section: it lists exactly which areas need review and gives a ready-to-use task
   for each subagent.
5. Determine parts to upgrade. Default to every area the report marked `review`; if the
   user named a scope, restrict to those areas.
6. Apply upgrade. Per part marked `review`, use one subagent, seeded with that area's
   diffs/mismatches from the report and the reference docs the report lists for it.
7. Verify and clean up:
 - run `composer install` and `npm install` if dependency files changed
 - run `npm run build`
 - run `composer run phpstan` and `composer run phpcs`
 - run `npm run lint:js` and `npm run lint:style` when JS or styles changed
 - run `npm run test:php` after starting wp-env when PHPUnit behavior changed
 - remove `tmp/plugin-ref`

## Per-area guidance

`upgrade-check.js` is the source of truth for the upgradable parts. For every area it marks
`review`, the report inlines the files and diffs, the reference docs to consult, whether
confirmation is required, and a ready-to-use subagent task. Follow that task — there is no
separate area checklist to keep in sync here. Areas marked `clean`, `not-applicable`, or
`skipped` need no work.

When applying any area:

- Prefer scoped patches over replacing whole files.
- After copying upstream code, adapt namespace, text domain, paths, and plugin-specific behavior.
- Never diff or copy `.agents/skills` as boilerplate source; refresh with `npx skills update -p` and ask before removing local-only skills.

The reference docs the report names live under `references/` (e.g. `references/doc-bundling.mdx`).
Load only the ones the report points to for the areas you are changing.
