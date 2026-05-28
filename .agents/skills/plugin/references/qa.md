# QA Command

Run configured quality assurance tools and fix errors.

## Rules

- Use commands already defined in `composer.json` and `package.json`.
- Do not install or run unconfigured tools.
- Fix errors automatically.
- Do not automatically fix warnings unless the user asks.

## PHP

1. Run `composer run phpcs`.
2. If PHPCS reports fixable issues, run `composer run phpcbf`, then rerun PHPCS.
3. Run `composer run phpstan`.

## JavaScript and Styles

1. Run the linting and formatting scripts defined in `package.json`.
2. If fix scripts exist, use them before manual edits.
3. Run `npm run build` when assets or package config changed.
