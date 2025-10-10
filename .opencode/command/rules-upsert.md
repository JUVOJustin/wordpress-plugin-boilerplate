---
description: Sync wordpress rules with wordpress-dev-llm-rules repo
---

**Goal:** Sync and integrate upstream WordPress development rules from [JUVOJustin/wordpress-dev-llm-rules](https://github.com/JUVOJustin/wordpress-dev-llm-rules) into the current workspace.

## **1. Download & Cleanup**
* Download the archive:
  `https://github.com/JUVOJustin/wordpress-dev-llm-rules/archive/refs/heads/main.zip`
* Extract the `rules/` folder from the archive.
* **Always** delete the downloaded archive and temporary assets afterward.


## **2. Compare and Sync Rules**
* Compare existing rules in `.github/instructions/` with the extracted upstream rules.
* Rename each upstream rule to a Copilot-compatible format:
  `{filename}.instructions.md`

## **3. Add or Update Rules**
* **Add** new upstream rules that don’t exist locally.
* **Update** existing rules when both sources have the same file.

  * If differences are large, confirm with the user before overwriting.
  * Default to treating the upstream as the source of truth.

## **4. Apply Only Relevant Local Development Rules**
* Include only rules relevant to the project setup (e.g., `ddev`).
* Skip irrelevant ones (e.g., `wp-umbrella` if unused).

## **5. Add Metadata Header**
Add this header to every rule file:

```md
---
applyTo: '**'
---
```

## **6. Integrate Base Rules into AGENTS.md**
* Merge `base.md` into `AGENTS.md`.

  * If `base.md` content already exists, update it.
  * If differences are large, confirm before overwriting.
* Remove `base.md` after integration.

## **7. Reference Rules in AGENTS.md**
Add brief references in `AGENTS.md` describing when to read each rule, e.g.:

```
Read when executing php, npm, yarn, composer commands or working with ddev local environment: 
@.github/instructions/local-development/ddev.md
```

## **8. Confirm Rule Removals**
Before deleting rules that exist only locally (not upstream), confirm with the user.