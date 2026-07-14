---
description: PHP architecture and WordPress registration rules provided by the plugin foundation.
applyTo: "**/*.php"
---

- Keep plugin business logic in `src/`, grouped by feature or context such as `Admin/`, `Frontend/`, or `Integrations/`.
- Keep the root plugin file limited to bootstrapping; do not place business logic there.
- Register actions, filters, shortcodes, WP-CLI commands, and abilities through the `Loader` class. Do not register hooks in constructors.
- Use `Loader::add_action()`, `Loader::add_filter()`, `Loader::add_shortcode()`, `Loader::add_cli()`, and `Loader::add_ability()` for their corresponding WordPress integrations.
- Implement Abilities API contracts in `src/Abilities/` and register them through the loader.
