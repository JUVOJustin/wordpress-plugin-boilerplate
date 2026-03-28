# WordPress Plugin Boilerplate

[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-blue)](https://phpstan.org/)
[![PHPCS](https://img.shields.io/badge/PHPCS-WordPress-green)](https://github.com/WordPress/WordPress-Coding-Standards)
[![Test/Analyse](https://github.com/JUVOJustin/wordpress-plugin-boilerplate/actions/workflows/test-analyse.yml/badge.svg)](https://github.com/JUVOJustin/wordpress-plugin-boilerplate/actions/workflows/test-analyse.yml)

This repository is a modern, object-oriented WordPress plugin boilerplate. It is intended as a source repo, starter template, and reference implementation for developers and AI coding agents building production-ready plugins.

If you want the user-facing documentation site entry point, start with [`docs/index.mdx`](docs/index.mdx).

## What This Repository Includes

- Composer-based PHP structure with namespacing
- Centralized WordPress hook registration through the loader
- `@wordpress/scripts` for bundling, linting, and formatting
- `@wordpress/env` for reproducible local WordPress development
- PHPUnit application testing in the dedicated `tests-cli` container
- GitHub Actions for analysis, testing, and release automation
- AI-oriented project instructions in `AGENTS.md`, `.opencode/command/`, and `.agents/skills/`

## Typical Use

Use this repo when you want to:

- create a new plugin with sane defaults
- inspect the preferred project structure before extending a plugin
- sync an existing plugin with upstream boilerplate changes
- give an AI agent enough repo context to make safe edits

## Repository Map

```text
.
|- demo-plugin.php          Main bootstrap file only
|- src/                     Plugin logic grouped by feature/domain
|- resources/               Admin and frontend assets
|- docs/                    User-facing documentation site content
|- tests/php/               PHPUnit application tests
|- .agents/skills/          Reusable AI skills for this project
|- .opencode/command/       Custom OpenCode slash commands
|- .github/workflows/       CI/CD workflows
`- README.txt               WordPress.org plugin readme template
```

## Local Development

### Create a Plugin From the Boilerplate

```bash
composer create-project juvo/wordpress-plugin-boilerplate
```

The setup script then asks for the plugin name, namespace, and slug and rewrites the boilerplate identity.

### Common Commands

| Command | Purpose |
| --- | --- |
| `npm run env:start` | Start the local WordPress environment |
| `npm run env:stop` | Stop the environment |
| `npm run build` | Build production assets |
| `npm run start` | Watch and rebuild assets during development |
| `npm run test:php` | Run PHPUnit application tests in `wp-env` |
| `npm run lint:js` | Lint JavaScript |
| `npm run lint:style` | Lint styles |
| `composer run phpstan` | Run PHP static analysis |
| `composer run phpcs` | Run WordPress coding standards checks |
| `composer run i18n:extract` | Extract translatable strings |
| `composer run i18n:compile` | Compile translation files |

## Architecture Notes

- Keep business logic in `src/`; do not place it in `demo-plugin.php`
- Register hooks, filters, shortcodes, CLI commands, and abilities through the loader
- Put assets in `resources/admin/` and `resources/frontend/`
- Add PHPUnit application tests in `tests/php/`
- Treat `docs/` as user-facing documentation and keep it in sync with repo behavior

## Where To Look Next

| Goal | Start here |
| --- | --- |
| Understand the docs site structure | [`docs/index.mdx`](docs/index.mdx) |
| Learn local environment workflows | [`docs/wp-env.mdx`](docs/wp-env.mdx) |
| Write application tests | [`docs/testing.mdx`](docs/testing.mdx) |
| Work on bundling or block assets | [`docs/bundeling.mdx`](docs/bundeling.mdx), [`docs/create-blocks.mdx`](docs/create-blocks.mdx) |
| Configure translations | [`docs/i18n.mdx`](docs/i18n.mdx) |
| Review AI-specific repo rules | [`AGENTS.md`](AGENTS.md), [`docs/work-with-ai.mdx`](docs/work-with-ai.mdx) |

## AI And Maintenance Notes

- `AGENTS.md` contains the high-level repository rules and doc map
- `.agents/skills/` contains reusable task-specific guidance
- `.opencode/command/` contains custom commands such as `/readme-update`
- when repo structure or workflows change, update both `README.md` and the relevant files in `docs/`

## Upstream Reference

This plugin was created using the [wordpress-plugin-boilerplate](https://github.com/JUVOJustin/wordpress-plugin-boilerplate). Keep that upstream reference so future updates and comparisons stay straightforward.
