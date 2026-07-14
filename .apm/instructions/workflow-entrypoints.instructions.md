---
description: WordPress plugin workflow entry points provided by the wp-plugin-bp skill.
---

- For multi-step work on a WordPress plugin package based on this project, load the `wp-plugin-bp` agent skill before implementing the change; the skill owns task selection, procedures, and verification.
- Ask the skill for the matching task to load details only when needed: `upgrade`, `translation`, `testing`, `qa`, `action-scheduler`, `readme`, `wp-skills`, `blocks`, `abilities`, `bundling`, `acf`, or `sentry`.
- Natural-language requests are supported; do not require the user to know or provide a task name.
