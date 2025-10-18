---
description: Run quality assurance tools
---

# Run quality assurance tools
* DO NOT run or install any unconfigured tool
* ALWAYS fix Errors
* DO NOT automatically fix warnings. After fixing the errors ask the user if warnings should be fixed too

## PHP: Run phpcs and phpstan defined in the composer.json.
* ALWAYS only run commands defined in composer.json to run the tools. DO NOT use the binaries directly
* If phpcs reports issues first run phpcbf to apply autofixing

Use @.github/instructions/quality-assurance/phpstan.instructions.md for guidance how to solve phpstan errors.

## JS/Styling Run linting and formatting tools defined in package.json.
* ALWAYS only run commands defined in package.json to run the tools. DO NOT use the tools directly
* If linting reports issues first run the ":fix" commands to apply autofixing