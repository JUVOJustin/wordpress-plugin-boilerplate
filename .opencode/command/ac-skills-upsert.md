---
description: Add/update automattic skills
Input: $ARGUMENTS
---

**Goal:** Upsert skills provided by automattic https://github.com/Automattic/agent-skills into the current workspace.

## **1. Download & Cleanup**
* Download the archive:
  `https://github.com/Automattic/agent-skills/archive/refs/heads/trunk.zip`
* Extract the `skills/` folder from the downloaded archive.
* **Always** delete the downloaded archive and temporary assets afterwards.

## **2. Add or Update Rules**

Based on the input provided, determine which skills to sync:

1. **No arguments (default)**:
    - wp-interactivity-api 
    - wp-project-triage 
    - wp-block-development

2. Defined arguments:
    - Upsert the skills $ARGUMENTS if they exist in the archive. Let the user know about missing skills after syncing the valid ones.

* Compare existing skills in @.opencode/skill with the extracted skills.
* **Add** new skills that don’t exist in the workspace and should be added.
* **Update** existing skills
* Default to treating the upstream as the source of truth.