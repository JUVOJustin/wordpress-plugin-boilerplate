---
description: Asset and block build conventions for the active WordPress plugin package.
---

- Place WordPress plugin admin assets in `resources/admin/` and frontend assets in `resources/frontend/`.
- Use the plugin package's `@wordpress/scripts` setup for asset compilation. Keep entry points in `webpack.config.js`.
- Run `npm run start` for watch mode and `npm run build` for production builds from the WordPress plugin package root.
- For a new block, run `npm run create-block` before creating or editing `block.json`, build entries, or registration code; the scaffold wires the foundation's automatic registration convention.
- Keep block manifests and built assets consistent with the generic checks in `tests/php/BlockRegistrationTest.php`.
