---
description: Sync wordpress rules with wordpress-dev-llm-rules repo
---

Add remote rules to this workspace

1. Download https://github.com/JUVOJustin/wordpress-dev-llm-rules/archive/refs/heads/main.zip inside to the current workspace that you can access. ALWAYS remove the downloaded assets afterwards. The rules are stored inside a `rules` folder of the archive. 
2. Compare the existing rules in `.github/instructions` with the upstream rules downloaded. The upstream rules are not in a github copilot compatible format. Rename them to `{filename}.instructions.md`.
3. Add new rules from the upstream
4. Update rules present in both places. If the difference is too big, ask the user to confirm. Generally the upstream is the source of truth.
5. Only process rules about local development if they apply to the projects setup. Example: If project uses ddev for development copy rules about ddev but not necessarily the ones about wp-umbrella.
6. Add the following markdown header to rules:
```md
---
applyTo: '**'
---
```
7. Add references of rules to `AGENTS.md` together with a brief description when to read each file. Add the reference like this: `Read when executing php,npm,yarn,composer commands or working with ddev local environment: @.github/instructions/local-development/ddev.md`
8. Ask the user to confirm the removal of rules only present in the local project
