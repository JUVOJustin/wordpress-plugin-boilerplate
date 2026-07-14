---
description: PHP architecture and WordPress registration rules for the active WordPress plugin package.
---

- Keep WordPress plugin business logic in `src/`, grouped by feature or context such as `Admin/`, `Frontend/`, or `Integrations/`.
- Keep the plugin package's root bootstrap file limited to bootstrapping; do not place business logic there.
- Register actions, filters, shortcodes, WP-CLI commands, and abilities through the plugin's `Loader` class. Do not register hooks in constructors.
- Use `Loader::add_action()`, `Loader::add_filter()`, `Loader::add_shortcode()`, `Loader::add_cli()`, and `Loader::add_ability()` for their corresponding WordPress integrations.
- Implement Abilities API contracts under `src/Abilities/` and keep registration consistent with `tests/php/AbilityRegistrationTest.php`.
