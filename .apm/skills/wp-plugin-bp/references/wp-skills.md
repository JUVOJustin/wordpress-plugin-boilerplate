# WordPress Skills Command

Install or update official WordPress agent skills through APM.

## Workflow

1. Inspect the available skill directories at `https://github.com/WordPress/agent-skills/tree/trunk/skills`.
2. Determine requested skills from command arguments.
3. If no specific skills are requested by the user, use:
   - `wp-interactivity-api`
   - `wp-project-triage`
   - `wp-block-development`
   - `wp-phpstan`
   - `wp-rest-api`
4. Always include `wp-project-triage` when syncing selected WordPress skills because other skills may depend on it.
5. Install each requested skill as a virtual APM package pinned to the repository's `trunk` branch, for example: `apm install 'WordPress/agent-skills/skills/wp-project-triage#trunk' 'WordPress/agent-skills/skills/wp-phpstan#trunk'`.
6. Use `apm update` to refresh already-declared WordPress skill dependencies.
7. Report missing requested skill names after syncing valid ones.
