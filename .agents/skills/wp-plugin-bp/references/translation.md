# Translation Command

Update or create WordPress plugin translations.

## Workflow

1. Run `npm run build` so JavaScript strings are available for extraction.
2. Inspect `languages/` and the main plugin file to confirm the text domain.
3. Run `composer run i18n:extract` to update the `.pot` file and existing `.po` files.
4. If locales were provided, create or update only those `.po` files. If no locales were provided, update existing `.po` files.
5. Fill untranslated strings with appropriate translations.
6. Run `composer run i18n:compile` to generate `.mo`, `.json`, and `.php` files.

## Rules

- Use one focused subtask per `.po` file when parallel work is available.
- Do not change the text domain unless the user explicitly requested a plugin rename.
- Load `references/doc-i18n.mdx` for detailed extraction and compilation behavior.
