# WordPress Skills Command

Upsert official WordPress agent skills into `.agents/skills/`.

## Workflow

1. List available skills `npx skills add https://github.com/WordPress/agent-skills --list`
2. Determine requested skills from command arguments.
3. If nospecific skills are requested from user, use:
   - `wp-interactivity-api`
   - `wp-project-triage`
   - `wp-block-development`
   - `wp-phpstan`
   - `wp-rest-api`
4. Always include `wp-project-triage` when syncing selected WordPress skills because other skills may depend on it.
5. Intall/Update skills `npx skills add https://github.com/WordPress/agent-skills --skill wp-project-triage --skill wp-phpstan`. 
6. Report missing requested skill names after syncing valid ones.
