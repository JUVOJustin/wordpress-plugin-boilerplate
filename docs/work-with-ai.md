# Working with AI

This boilerplate is optimized for AI-assisted development using [opencode](https://opencode.ai). It provides a complete set of commands, skills, and instructions that guide AI agents to work effectively with your plugin.
These resources are kept up to date and it makes sense to update them with the latest version of the boilerplate. You can add your own resources anytime.

## How It Works

The AI integration consists of three layers that work together:

### 1. AGENTS.md — The Rulebook

The `AGENTS.md` file in the root directory is the primary instruction set for AI agents. It contains:
- High-level architecture guidance
- Coding standards and conventions
- Quick reference for key primitives
- Pointers to detailed documentation

**This file is your single source of truth** for how AI agents should interact with your codebase.

### 2. Commands — Quick Actions

Commands are one-off tasks you can trigger via slash commands (e.g., `/translations-upsert`). They live in `.opencode/command/` and provide:
- Fast, repeatable operations
- Consistent execution patterns
- Automatic context awareness

### 3. Skills — Complex Workflows

Skills are sophisticated, multi-step workflows for complex tasks. They live in `.opencode/skill/` and handle:
- Multi-file operations
- Decision trees and conditional logic
- Integration with external tools

## Available Commands

| Command | Description |
|---------|-------------|
| `/translations-upsert [lang1 lang2...]` | Update or create translations. Without arguments: updates all existing `.po` files. With arguments: creates/updates specific languages. |
| `/wp-skills-upsert [skillnames]` | Download and install official WordPress agent skills. Default: `wp-interactivity-api`, `wp-project-triage`, `wp-block-development`, `wp-phpstan`. |
| `/commands-upsert` | Sync commands and skills from the upstream boilerplate. |
| `/readme-update` | Update README.md with current plugin information, blocks, CLI commands, etc. |
| `/qa-run` | Run quality assurance tools (PHPCS, PHPStan, ESLint, stylelint). |

## Built-in Skills

### plugin-translations-update

Handles the complete translation workflow:
1. Builds JS assets to extract strings
2. Generates `.pot` template file
3. Updates or creates `.po` files
4. Compiles to `.mo`, `.json`, and `.php` formats

See [i18n.md](i18n.md) for detailed translation documentation.

### boilerplate-update

Syncs your plugin with the latest boilerplate features:
- Build tools and dependencies
- CI/CD workflows
- QA configurations
- New architectural patterns (Abilities API, etc.)

See [boilerplate-update skill](.opencode/skill/boilerplate-update/SKILL.md) for the full workflow.

## WordPress Agent Skills (Recommended)

The WordPress project provides official skills for WordPress development. Install them with `/wp-skills-upsert`:

| Skill | Purpose |
|-------|---------|
| `wp-block-development` | Create, modify, and debug Gutenberg blocks |
| `wp-interactivity-api` | Work with the Interactivity API for dynamic blocks |
| `wp-phpstan` | PHPStan analysis with WordPress-specific rules |
| `wp-project-triage` | Project analysis and task management |

These skills are maintained in `https://github.com/WordPress/agent-skills` and follow WordPress core best practices.

## Customizing for Other AI Tools

While optimized for opencode, the structure works with any AI assistant:

To adapt for another tool:
- For claude code: copy `AGENTS.md` to `CLAUDE.md`.
- Commands are basically pure prompts. You can copy their body to any tool. You might need to adjust the `front matter`.
- Copy `.opencode/skill` into your tools skills folder like `.claude/skills`.

## Tips for Effective AI Collaboration

- **Be specific**: Instead of "fix the bug", say "fix the PHP notice in src/Admin/Settings.php line 42"
- **Reference conventions**: Mention "follow the Loader pattern" or "use the enqueue_entrypoint method"
- **Use commands**: They're faster and more consistent than describing the same task repeatedly
- **Update docs**: When you add new patterns, update `AGENTS.md` so future AI interactions benefit
