---
description: Quality and test guardrails for the active WordPress plugin package.
---

- Run the plugin's configured checks from the WordPress plugin package root; do not apply its tooling or paths to sibling monorepo packages.
- Run PHPStan with `composer run phpstan` and WordPress Coding Standards with `composer run phpcs`; use `composer run phpcbf` only for safe automatic fixes.
- Run JavaScript and stylesheet checks with `npm run lint:js` and `npm run lint:style`.
- Run application tests with `npm run test:php`; start the WordPress test environment with `npm run env:start` when required.
- Extract translations with `composer run i18n:extract` and compile them with `composer run i18n:compile`.
- Keep the applicable GitHub Actions workflows aligned with the plugin's configured local quality commands, including workflows owned at a monorepo root.
