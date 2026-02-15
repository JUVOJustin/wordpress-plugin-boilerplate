---
description: Upsert WordPress agent skills [skillnames], default to wp-interactivity-api, wp-project-triage, wp-block-development, wp-phpstan, wp-rest-api
subtask: false
---

**Goal:** Upsert skills provided by WordPress https://github.com/WordPress/agent-skills into the current workspace.

## **1. Download & Cleanup**
* Download the archive:
  `https://github.com/WordPress/agent-skills/archive/refs/heads/trunk.zip`
* Extract the `skills/` folder from the downloaded archive.
* **Always** delete the downloaded archive and temporary assets afterwards.

## **2. Add or Update Rules**

Based on the input provided, determine which skills to sync: "$ARGUMENTS"

1. **No input (default)**:
    - wp-interactivity-api 
    - wp-project-triage 
    - wp-block-development
    - wp-phpstan
    - wp-rest-api

2. Defined input:
    - Always upsert wp-project-triage as it is a dependency for other skills.
    - Upsert the skills "$ARGUMENTS" if they exist in the archive. Let the user know about missing skills after syncing the valid ones.

* Compare existing skills in @.agents/skills/ with the extracted skills.
* **Add** new skills that don’t exist in the workspace and should be added.
* **Update** existing skills
* Default to treating the upstream as the source of truth.
