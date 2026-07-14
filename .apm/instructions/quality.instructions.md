---
description: Quality and test commands provided by the plugin foundation.
---

- Run PHPStan with `composer run phpstan` and WordPress Coding Standards with `composer run phpcs`; use `composer run phpcbf` only for safe automatic fixes.
- Run JavaScript and stylesheet checks with `npm run lint:js` and `npm run lint:style`.
- Run application tests with `npm run test:php`; start the WordPress test environment with `npm run env:start` when required.
- Extract translations with `composer run i18n:extract` and compile them with `composer run i18n:compile`.
- Keep GitHub Actions workflows under `.github/workflows/` aligned with the configured local quality commands.
