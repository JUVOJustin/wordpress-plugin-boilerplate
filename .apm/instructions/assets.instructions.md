---
description: Asset and block build conventions provided by the plugin foundation.
applyTo: "resources/**,webpack.config.js,package.json,**/block.json"
---

- Place admin assets in `resources/admin/` and frontend assets in `resources/frontend/`.
- Use `@wordpress/scripts` for asset compilation. Keep entry points in `webpack.config.js`.
- Use `npm run start` for watch mode and `npm run build` for production builds.
- Create blocks with `npm run create-block`; block registration is automatic.
- Keep block manifests and built assets consistent with the generic checks in `tests/php/BlockRegistrationTest.php`.
