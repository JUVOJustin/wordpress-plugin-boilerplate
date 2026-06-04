# Bundling Command

Work on wp-scripts bundling, entry points, assets, and enqueueing.

## Workflow

1. Load `references/doc-bundling.mdx`.
2. Put admin assets in `resources/admin/`.
3. Put frontend assets in `resources/frontend/`.
4. Add or adjust entry points in `webpack.config.js`.
5. Enqueue compiled entry points through the plugin's established enqueue helper.
6. Run `npm run build` before finishing.
