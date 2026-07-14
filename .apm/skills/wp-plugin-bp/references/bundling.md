# Bundling Command

Work on wp-scripts bundling, entry points, assets, and enqueueing.

## Workflow

1. Load `references/doc-bundling.mdx`.
2. Put admin assets in `resources/admin/`.
3. Put frontend assets in `resources/frontend/`.
4. Add or adjust entry points in `webpack.config.js`. Only augment the classic
   script config &mdash; skip configs where `config.experiments?.outputModule` is set,
   since that is the ESM module build for Interactivity API `viewScriptModule`
   (`view.js`) modules and must stay untouched.
5. Enqueue compiled entry points through the plugin's established enqueue helper.
6. Run `npm run build` before finishing. `start`/`build` use `--experimental-modules`
   so the Interactivity API module build is produced.
