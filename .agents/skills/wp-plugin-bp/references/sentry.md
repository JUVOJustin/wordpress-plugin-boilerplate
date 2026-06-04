# Sentry Command

Add or maintain Sentry integration.

## Workflow

1. Load `references/doc-sentry.mdx`.
2. Bootstrap Sentry early enough to capture plugin runtime errors.
3. Keep DSN and environment values configurable.
4. Avoid hardcoding secrets.
5. Register integration hooks through the Loader where WordPress hooks are needed.
