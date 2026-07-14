---
description: AI workflow routing for capabilities included in the plugin foundation.
---

- Use the `wp-plugin-bp` skill for plugin upgrades, translations, application tests, quality checks, blocks, abilities, bundling, ACF, Sentry, README work, and wp-env guidance.
- Load the matching skill workflow before manually adding foundation files or configuration; it owns the expected scaffolding, registration, and verification sequence.
- For Abilities API work, implement the contracts under `src/Abilities/`, register them through the loader, and keep them consistent with `tests/php/AbilityRegistrationTest.php`.
- Use the skill's `wp-skills` workflow to install or refresh focused official WordPress skills.
- Treat the skill's task references as the workflow source of truth; detailed guides are resolved from the installed APM package.
