---
name: translations
description: Update or create WordPress plugin translations. Use when working with i18n, translations, .po/.pot files, or when user asks to translate plugin strings into specific languages.
---

# WordPress Plugin Translations

Manage internationalization (i18n) for WordPress plugins by extracting, translating, and compiling translation files.

## Workflow

1. **Validate setup**: Read `languages/` to check existing translation files
2. **Extract strings**: Run `composer run i18n:extract` to generate `.pot` and update `.po` files
3. **Translate**: Based on arguments:
   - **No arguments**: Update existing `.po` files with missing translations
   - **With languages** (e.g., `de_DE fr_FR`): Create `.po` files for specified languages if missing, then translate
4. **Compile**: Run `composer run i18n:compile` to generate `.mo`, `.json`, and `.php` files

## Translation Guidelines

- Use one subtask/subagent per `.po` file for parallel processing
- Ensure textdomain matches the one defined in main plugin file
- Requires `wp-cli` and a working WordPress installation

## Reference

For detailed i18n documentation, see [i18n.md](references/i18n.md).
