---
description: Sync wordpress rules with wordpress-dev-llm-rules repo
---

Add the remote rules to this workspace

1. Get the `rules`folder of the https://github.com/JUVOJustin/wordpress-dev-llm-rules repository. Try to access them via web or clone them to folder inside of to the current workspace that you can access. The folder you cloned them into should be removed at the end.
2. Compare the existing rules with the new rules in `.github/instructions`. The upstream rules are not in a github copilot compatible format. Rename them to `{filename}.instructions.md`.
3. Add new rules only present in the remote repository
4. Update rules present in both repositories. If the difference is too big, ask the user to confirm. Generally the remote repository is the source of truth.
5. Only process rules about local development if they apply to the projects setup. Example: If project uses ddev for development copy rules about ddev but not necessarily the ones about wp-umbrella.
6. Add the following markdown header to rules:
```md
---
applyTo: '**'
---
```
7. Add links to all files into `.github/copilot-instructions.md` together with a brief description when to read each file. Add the reference like this: `.github/instructions/local-development/ddev.md Read when executing php,npm,yarn,composer commands or working with ddev local environment.`
8. Ask the user to confirm the removal of rules only present in the local repository