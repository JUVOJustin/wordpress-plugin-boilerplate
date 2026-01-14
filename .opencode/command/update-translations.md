---
description: Update plugin translations [lang1 lang2 ...]
subtask: false
---

**Goal:** Update or create translations of the plugin based on the latest available strings.

1. Run `npm run i18n:extract`. This generates the `.pot` file in the `languages/` directory and updates existing `.po` files.
2. Based on the input provided, determine what to translate: "$ARGUMENTS"
    * **No input (default)**: Check existing `.po` files and add missing or outdated translations.
    * Defined input: Create `.po` files for the languages specified in the input if they do not exist. Add missing or outdated translations to all `.po` files.
    * Use one subtask/subagent per language
3. Run `npm run i18n:compile` to compile the `.po` files into `.mo`, `.json` and `.php` files for use by WordPress.