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
- AI-oriented project instructions in `AGENTS.md` and a cross-tool APM package in `.apm/skills/wp-plugin-bp/`

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
|- docs/                    Documentation site entry points; some alias canonical APM guides
|- tests/php/               PHPUnit application tests
|- apm.yml                  Versioned APM package manifest
|- .apm/skills/wp-plugin-bp/ Unified plugin AI skill with task references
|- .github/workflows/       CI/CD workflows
`- README.txt               WordPress.org plugin readme template
```

## Local Development

### Create a Plugin From the Boilerplate

```bash
composer create-project juvo/wordpress-plugin-boilerplate
```

The setup script asks for the plugin name, namespace, and slug, runs the packaged `wp-plugin-bp` replacement script, and removes the package-authoring `.apm/` tree and `apm.yml` from the initialized plugin.
After replacement, setup can install the versioned APM package. APM creates a consumer manifest and lockfile and deploys the skill to the detected agent runtimes.

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
- Treat `docs/` as user-facing documentation; edit canonical linked guides in `.apm/skills/wp-plugin-bp/references/`

## Where To Look Next

| Goal | Start here |
| --- | --- |
| Understand the docs site structure | [`docs/index.mdx`](docs/index.mdx) |
| Learn local environment workflows | [`docs/wp-env.mdx`](docs/wp-env.mdx) |
| Write application tests | [`docs/testing.mdx`](docs/testing.mdx) |
| Work on bundling or block assets | [`docs/bundeling.mdx`](docs/bundeling.mdx), [`docs/create-blocks.mdx`](docs/create-blocks.mdx) |
| Configure translations | [`docs/i18n.mdx`](docs/i18n.mdx) |
| Review AI-specific repo rules | [`AGENTS.md`](AGENTS.md), [`docs/work-with-ai.mdx`](docs/work-with-ai.mdx), [`.apm/skills/wp-plugin-bp/SKILL.md`](.apm/skills/wp-plugin-bp/SKILL.md) |

## AI And Maintenance Notes

- `AGENTS.md` contains the high-level repository rules and doc map
- `apm.yml` gives the AI package its own release version, independent of the example plugin's `1.0.0` version
- `.apm/skills/wp-plugin-bp/` contains the unified skill, canonical detailed guides, task references, and scripts
- APM installs optional official WordPress skills from `WordPress/agent-skills` when the `wp-skills` workflow is requested
- natural requests such as "sync with upstream project conventions" should route through the `wp-plugin-bp` skill and infer the upgrade workflow
- when repo structure or workflows change, update `README.md`, the relevant canonical guide or `docs/` page, and `.apm/skills/wp-plugin-bp/`

## Upstream Reference

This plugin was created using the [wordpress-plugin-boilerplate](https://github.com/JUVOJustin/wordpress-plugin-boilerplate). Keep that upstream reference so future updates and comparisons stay straightforward.
