---
name: wp-plugin-bp
description: Use this skill whenever working on Demo Plugin or a downstream WordPress plugin based on this project. Use for plugin upgrades/sync tasks, adopting latest upstream project conventions, translations/i18n, PHPUnit application tests, QA checks, Action Scheduler, blocks, Abilities API, bundling, ACF, Sentry, README updates, wp-env, and AI workflow maintenance. If the user asks naturally about any of these, infer and run the matching task workflow.
compatibility: Some commands require PHP, Composer, Node.js, npm, Docker, wp-env, WP-CLI, or network access.
---

# WP Plugin BP Skill

Operate this WordPress plugin. Keep this file as the router and load command references only when the task calls for them.

## Task Routing

| Task | Purpose | Reference |
| --- | --- | --- |
| `upgrade [scope]` | Sync the plugin with the latest upstream project conventions | [references/upgrade.md](references/upgrade.md) |
| `translation [locales]` | Extract, update, translate, and compile plugin translations | [references/translation.md](references/translation.md) |
| `testing [target]` | Write or debug wp-env PHPUnit application tests | [references/testing.md](references/testing.md) |
| `qa [scope]` | Run configured quality checks and fix errors | [references/qa.md](references/qa.md) |
| `action-scheduler [target]` | Add or test Action Scheduler integration | [references/action-scheduler.md](references/action-scheduler.md) |
| `readme [target]` | Update the plugin README from current code and project conventions | [references/readme.md](references/readme.md) |
| `wp-skills [names]` | Upsert official WordPress agent skills | [references/wp-skills.md](references/wp-skills.md) |
| `blocks [target]` | Scaffold or maintain Gutenberg blocks | [references/blocks.md](references/blocks.md) |
| `abilities [target]` | Implement or update WordPress Abilities API code | [references/abilities.md](references/abilities.md) |
| `bundling [target]` | Work on wp-scripts entry points, assets, or enqueueing | [references/bundling.md](references/bundling.md) |
| `acf [target]` | Add or maintain ACF integration patterns | [references/acf.md](references/acf.md) |
| `sentry [target]` | Add or maintain Sentry integration patterns | [references/sentry.md](references/sentry.md) |

## Routing Rules

1. If the request is broad or has no clear task, show the task table grouped by purpose and ask what the user wants to do.
2. If the first word matches a task or alias, load that task's reference file and follow it. Everything after the task is the target or arguments.
3. If the first word does not match a task, infer the task from the user's intent, load the matching reference file, and proceed.
4. If two tasks are equally plausible and the next step would materially differ, ask one concise clarification question.

Do not continue with generic WordPress advice when a task applies. The task reference owns the workflow once selected.

## Automatic Task Inference

- Route to `upgrade` for: plugin upgrade, sync upstream, migrate to latest project conventions, adopt upstream features, update Loader, update AI commands or skills from upstream.
- Route to `translation` for: i18n, translations, translate, `.po`, `.pot`, `.mo`, locale codes like `de_DE`, text domain, `composer run i18n:*`.
- Route to `testing` for: PHPUnit, application tests, `WP_UnitTestCase`, factories, test bootstrap, wp-env test container, adding or debugging tests.
- Route to `qa` for: PHPCS, PHPStan, ESLint, stylelint, linting, static analysis, quality checks, build verification.
- Route to `action-scheduler` for: Action Scheduler, async jobs, background jobs, scheduled actions, queues, queue runner.
- Route to `readme` for: README, WordPress.org readme, plugin documentation summary, user-facing repository overview.
- Route to `wp-skills` for: WordPress agent skills, `wp-block-development`, `wp-phpstan`, `wp-rest-api`, `wp-project-triage`, `wp-interactivity-api`.
- Route to `blocks` for: Gutenberg blocks, `npm run create-block`, block manifests, block editor assets.
- Route to `abilities` for: WordPress Abilities API, ability categories, `add_ability`, WP 6.9 abilities.
- Route to `bundling` for: `@wordpress/scripts`, webpack entry points, build assets, enqueueing, asset localization.
- Route to `acf` for: Advanced Custom Fields, ACF JSON sync, field groups.
- Route to `sentry` for: Sentry SDK, error monitoring, early bootstrap capture.

## Skill Scripts

- `scripts/plugin-replace.php`: deterministic identity replacement and setup cleanup helper. Use this script for initialization and upgrade reference rewrites instead of hand-editing placeholders.

## References

Detailed docs are available as `references/doc-*.mdx`. In the source repo these may be symlinks into `docs/`; setup removes packaged `.agents/` from initialized plugins, adds `.agents/` to `.gitignore`, then asks whether to install the current skills with `npx skills add https://github.com/JUVOJustin/wordpress-plugin-boilerplate --skill=*`. Installed skills are managed with `npx skills update -p` instead of being committed downstream.

## Environment

If the host has no working environment (ddev,wp-studio etc) use wp-env.
- Start env: `npm run env:start`
- Run commands: `npm run env:cli composer run phpstan` or `npm run env:cli i18n:extract`

All PHP and wp-cli commands/tools can run inside wp-env. npm commands need to run on the host.
