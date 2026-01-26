---
description: Update plugin translations [lang1 lang2 ...]
subtask: false
---

**Goal:** Update or create translations of the plugin based on the latest available strings.

1. Read @languages/ to validate which languages are already set up. This folder needs to contain all translation files.
2. Run `composer run i18n:extract`. This generates a `.pot` file updates existing `.po` files.
3. Based on the input provided, determine what to translate: "$ARGUMENTS"
    * **No input (default)**: Read existing `.po` files and edit them to add missing or outdated translations.
    * Defined input: Create `.po` files for the languages specified in the input if they do not exist. Edit all `.po` files and add missing or outdated translations.

   **Use one subtask/subagent per `.po` file**
4. Run `composer run i18n:compile` to compile the `.po` files into `.mo`, `.json` and `.php` files. This step makes translations available to WordPress.