---
description: Sync WordPress agent skills from Automattic/agent-skills repository
---

**Goal:** Install and update Automattic WordPress "agent skills" from [Automattic/agent-skills](https://github.com/Automattic/agent-skills) into this repository under `.opencode/skill/<skill-name>/`.

## Arguments

`$ARGUMENTS` — (Optional) Space-separated list of skill names to sync. If omitted, syncs the default skills.

**Default skills:**
- `wp-interactivity-api`
- `wp-project-triage`
- `wp-block-development`

**Usage examples:**
```
/agent-skills-sync
/agent-skills-sync wp-interactivity-api
/agent-skills-sync wp-interactivity-api wp-project-triage
/agent-skills-sync wp-block-development wp-performance
```

## Source Repository

**Upstream:** `https://github.com/Automattic/agent-skills`
- Skills are located at: `skills/<skill-name>/`
- Each skill contains:
  - `SKILL.md` — Main skill documentation (required)
  - `references/` — Optional reference documentation
  - `scripts/` — Optional helper scripts

## Destination

All skills are synced to: `.opencode/skill/<skill-name>/`

Example destination structure:
```
.opencode/skill/
├── wp-interactivity-api/
│   ├── SKILL.md
│   └── references/
│       ├── directives-quickref.md
│       └── debugging.md
├── wp-project-triage/
│   ├── SKILL.md
│   ├── references/
│   │   └── triage.schema.json
│   └── scripts/
│       └── detect_wp_project.mjs
└── wp-block-development/
    ├── SKILL.md
    ├── references/
    │   └── ...
    └── scripts/
        └── ...
```

## Procedure

### 1. Parse Arguments

- If `$ARGUMENTS` is provided and not empty, parse it as space-separated skill names.
- If `$ARGUMENTS` is empty or not provided, use default skills: `wp-interactivity-api`, `wp-project-triage`, `wp-block-development`.

### 2. Download Upstream Repository

Download the upstream repository archive:
- URL: `https://github.com/Automattic/agent-skills/archive/refs/heads/main.zip`
- Extract to a temporary location (e.g., `/tmp/agent-skills-temp/`)
- **ALWAYS** delete the downloaded archive and temporary directory after syncing.

### 3. Validate Skills Exist

For each skill in the list:
- Check if the skill exists in `agent-skills-main/skills/<skill-name>/`
- If a skill does NOT exist in the upstream repository:
  - Log a warning: `⚠️  Skill '<skill-name>' not found in Automattic/agent-skills and will be skipped.`
  - Continue with remaining skills
- If no valid skills remain, exit with an error message.

**Available skills** (as of 2024, check upstream for updates):
- `wordpress-router`
- `wp-abilities-api`
- `wp-block-development`
- `wp-block-themes`
- `wp-interactivity-api`
- `wp-performance`
- `wp-playground`
- `wp-plugin-development`
- `wp-project-triage`
- `wp-wpcli-and-ops`

### 4. Sync Each Valid Skill

For each valid skill:

#### 4.1 Create Destination Directory
- Ensure `.opencode/skill/<skill-name>/` exists.
- If it doesn't exist, create it.

#### 4.2 Copy Full Skill Folder
- Copy the **entire** skill folder from upstream to local:
  - Source: `agent-skills-main/skills/<skill-name>/`
  - Destination: `.opencode/skill/<skill-name>/`
- This includes:
  - `SKILL.md` (required)
  - `references/` directory (if exists)
  - `scripts/` directory (if exists)
  - Any other files/folders present in the upstream skill

#### 4.3 Handle Existing Skills

**If the skill already exists locally:**
- Compare existing files with upstream files.
- **Update behavior:**
  - Replace all files with upstream versions (treat upstream as source of truth).
  - Remove any local files that no longer exist upstream.
  - Add any new upstream files.
- **If major differences detected:**
  - Show a summary of changes (files added/modified/removed).
  - Ask user to confirm before proceeding with update.
  - Default answer: Yes (proceed with update).

**If the skill is new:**
- Simply copy the entire folder.
- Log: `✓ Added skill '<skill-name>'`

### 5. Update AGENTS.md

After syncing skills, add or update a section in `AGENTS.md`:

#### 5.1 Add WordPress Skills Section

If not already present, add this section at the end of `AGENTS.md`:

```markdown
### WordPress Agent Skills

This project includes specialized agent skills for WordPress development from [Automattic/agent-skills](https://github.com/Automattic/agent-skills).

**Available skills:**
- **wp-interactivity-api** — Use when building or debugging WordPress Interactivity API features
  - Read skill: @.opencode/skill/wp-interactivity-api/SKILL.md
- **wp-project-triage** — Use for deterministic inspection of WordPress repositories
  - Read skill: @.opencode/skill/wp-project-triage/SKILL.md
- **wp-block-development** — Use when developing WordPress blocks
  - Read skill: @.opencode/skill/wp-block-development/SKILL.md

**To update skills:** Run `/agent-skills-sync` command.
```

#### 5.2 Update Existing Section

If the section already exists:
- Update the list of skills to match the synced skills.
- Keep the format consistent with existing content.
- Ensure all skill references use `@.opencode/skill/<skill-name>/SKILL.md` format.

### 6. Summary Report

After completion, display a summary:

```
✓ Agent Skills Sync Complete

Synced skills:
  ✓ wp-interactivity-api
  ✓ wp-project-triage
  ✓ wp-block-development

Updated AGENTS.md with skill references.

These skills are now available in .opencode/skill/ and will be included in Packagist installs.
```

If any skills were skipped:
```
⚠️  Skipped skills (not found in upstream):
  • invalid-skill-name
  • another-invalid-skill
```

## Important Notes

### Packagist-Friendly Vendoring
- Skills are **fully copied** into this repository (not symlinked or git-submoduled).
- This ensures they are available in Composer/Packagist installs.
- Updates require re-running this command.

### Update Strategy
- **Upstream is the source of truth** — Local changes will be overwritten.
- Skills should not be manually edited in `.opencode/skill/`.
- To contribute changes to skills, submit PRs to the upstream [Automattic/agent-skills](https://github.com/Automattic/agent-skills) repository.

### Skill Reference Format
- Always reference skills using `@.opencode/skill/<skill-name>/SKILL.md` format in AGENTS.md.
- This allows LLMs to easily locate and read skill documentation.

## Cleanup

**ALWAYS** perform cleanup:
- Delete the downloaded ZIP file.
- Delete the temporary extraction directory.
- Do NOT commit temporary files.
