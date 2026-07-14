# Blocks Command

Create or maintain Gutenberg blocks in this plugin.

## Workflow

1. Load `references/doc-create-blocks.mdx`.
2. Use the configured `npm run create-block` workflow for scaffolding.
3. Keep block source under the established resources or blocks layout.
4. Ensure block manifests are included in production builds.
5. Run `npm run build` after block changes.
6. Verify loading with `npm run test:php` — `tests/php/BlockRegistrationTest.php` checks every built block is registered (via `/wp/v2/block-types`) and its assets exist. No edits needed when adding blocks.
