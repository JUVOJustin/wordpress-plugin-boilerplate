# WordPress Agent Skills

This directory contains specialized agent skills for WordPress development from the [Automattic/agent-skills](https://github.com/Automattic/agent-skills) repository.

## Purpose

Agent skills provide LLM agents with specialized knowledge and procedures for specific WordPress development tasks such as:
- Building with the Interactivity API
- Project structure detection and triage
- Block development
- And more...

## Structure

Each skill is stored in its own subdirectory:
```
.opencode/skill/
├── wp-interactivity-api/
├── wp-project-triage/
├── wp-block-development/
└── ...
```

Each skill folder contains:
- `SKILL.md` — Main skill documentation (required)
- `references/` — Optional reference documentation
- `scripts/` — Optional helper scripts

## Installation & Updates

Skills are synced from upstream using the `/agent-skills-sync` command:

```bash
# Sync default skills (wp-interactivity-api, wp-project-triage, wp-block-development)
/agent-skills-sync

# Sync specific skills
/agent-skills-sync wp-interactivity-api wp-performance

# Sync multiple custom skills
/agent-skills-sync wp-block-themes wp-wpcli-and-ops
```

## Available Skills

Visit the [Automattic/agent-skills](https://github.com/Automattic/agent-skills) repository to see all available skills.

Common skills include:
- `wp-interactivity-api` — WordPress Interactivity API development
- `wp-project-triage` — Repository structure detection
- `wp-block-development` — Gutenberg block development
- `wp-block-themes` — Block theme development
- `wp-performance` — Performance optimization
- `wp-plugin-development` — Plugin development best practices
- `wp-wpcli-and-ops` — WP-CLI and operations

## Upstream Source

- **Repository:** https://github.com/Automattic/agent-skills
- **Skills location:** `skills/<skill-name>/`
- **License:** Check upstream repository for licensing information

## Important Notes

### Packagist-Friendly
- Skills are **fully vendored** into this repository (not symlinked).
- They are included in Composer/Packagist installs automatically.
- No additional setup required after `composer install`.

### Update Policy
- **Upstream is the source of truth** — Local changes will be overwritten on sync.
- Do NOT manually edit skills in this directory.
- To contribute changes, submit PRs to [Automattic/agent-skills](https://github.com/Automattic/agent-skills).

### Usage by LLMs
- Skills are referenced in `AGENTS.md` using the format: `@.opencode/skill/<skill-name>/SKILL.md`
- LLMs can read these skills to gain specialized knowledge for specific tasks.

## Maintenance

To update all synced skills to their latest upstream versions:
```bash
/agent-skills-sync
```

This will:
1. Download the latest versions from Automattic/agent-skills
2. Update all previously synced skills
3. Preserve the list of skills (won't add new default skills if you've customized)
