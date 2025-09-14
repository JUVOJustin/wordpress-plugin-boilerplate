---
description: Add boilerplate commands to this workspace
---

ALL command files need to be valid markdown with proper `.md` file extension

1. Download https://github.com/JUVOJustin/wordpress-plugin-boilerplate/archive/refs/heads/main.zip inside of to the current workspace. ALWAYS remove the downloaded assets at end. The commands are stored inside `.opencode/command` folder of the archive.
2. Compare the existing commands in `.opencode/command` with the upstream commands downloaded. 
3. Add ALL new commands only present in the upstream
4. Update commands present in both places. If the difference is too big, ask the user to confirm. Generally the upstream is the source of truth.
5. Ask the user to confirm the removal of commands only present in the local project
