---
description: Scope boundary for WordPress plugin foundation rules in standalone repositories and monorepos.
---

- Apply these foundation rules only inside a WordPress plugin package based on this project. If the current task does not involve such a package, ignore the remaining rules; do not apply them to sibling applications, themes, libraries, or services in a monorepo.
- Identify the active WordPress plugin package root before resolving paths or running commands. Use the package directory containing the WordPress plugin bootstrap file and its project configuration, not automatically the repository root.
- Resolve paths such as `src/`, `resources/`, and `tests/php/` relative to that WordPress plugin package root. Respect repository-level files such as `.github/workflows/` when a monorepo owns them outside the plugin package.
