# ACF Command

Add or maintain Advanced Custom Fields integration.

## Workflow

1. Load `references/doc-acf.mdx`.
2. Keep ACF-specific code under an integration-focused namespace.
3. Use JSON sync for field groups when the plugin owns those fields.
4. Register hooks through the Loader.
5. Document new ACF conventions in the source docs when adding reusable patterns.
