---
description: Run phpcs and phpstan defined in the composer.json.
---

Run phpcs and phpstan defined in the composer.json.

* DO NOT run or install any unconfigured tool
* ALWAYS run commands defined in composer.json to run the tools. DO NOT use the binaries directly
* If phpcs reports issues first run phpcbf to apply autofixing
* ALWAYS fix Errors
* DO NOT automatically fix warnings. After fixing the errors ask the user if warnings should be fixed too