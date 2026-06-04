# README Command

Update the plugin README from the current codebase and project conventions.

## Workflow

1. Read the current `README.md` if it exists.
2. Inspect the plugin scope:
   - integrations
   - REST API endpoints
   - CLI commands
   - shortcodes
   - blocks
   - post types and taxonomies
3. Remove setup-only starter instructions from plugin READMEs.
4. Keep generally useful structure, commands, quality, and development notes.
5. Include plugin-specific functionality discovered from code.

## Output

When the user asks for content only, return only the updated README body. When editing the repo, update the file directly and summarize the change.
