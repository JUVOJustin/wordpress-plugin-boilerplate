---
description: (Re-)Setup this plugin
---

Always evaluate the shell output since some commands require user input or might break. 

It is extremely important to always follow this order:

1. Setup commands.
If the "commands-upsert" command is unknown to opencode prompt the user to download the commands from https://github.com/JUVOJustin/wordpress-plugin-boilerplate/tree/main/.opencode/command into the orojects `.opencode/command` folder. Offer to proceed with the download for the user. DO NOT modify the commands ever. If you downloaded the files you successfully finished this step.
`!opencode run "/commands-upsert"`

2. Setup rules
`!opencode run "/rules-upsert"`

3. Upsert Strauss
`!opencode run "/strauss-upsert"`

4. Upsert Q&A
`!opencode run "/qa-upsert"`

5. Update readme
`!opencode run "/readme-update"`