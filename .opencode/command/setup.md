---
description: (Re-)Setup this plugin
---

Always evaluate the shell output since some commands require user input or might break.
Never commit or push any changes to version control.

It is extremely important to always follow this order:

1. Setup commands
    `!opencode run "/commands-upsert"`

    If the "commands-upsert" command is unknown to opencode: 
    Ask to download it from https://raw.githubusercontent.com/JUVOJustin/wordpress-plugin-boilerplate/refs/heads/main/.opencode/command/commands-upsert.md and save it in `.opencode/command/commands-upsert.md`. Keep the file content exactly as is. Execute the command and continue with the next step.

2. Setup rules
    `!opencode run "/rules-upsert"`

3. Upsert Strauss
    `!opencode run "/strauss-upsert"`

4. Upsert Q&A
    `!opencode run "/qa-upsert"`

5. Update readme
    `!opencode run "/readme-update"`
