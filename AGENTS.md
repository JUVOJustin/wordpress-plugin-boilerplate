<!-- BOILERPLATE-DOCS-START -->
# AI Coding Agent Instructions for Demo Plugin

This repository is the source template used to create downstream WordPress plugins.

- Refer to the template as "Demo Plugin" until setup replaces its identity.
- Content inside `BOILERPLATE-DOCS` markers is producer-only and is removed during setup.
- Keep only always-on WordPress plugin scope and static foundation guardrails in `.apm/instructions/`; do not duplicate them in this producer-only section.
- Keep task routing, workflows, references, and deterministic helper scripts in `.apm/skills/wp-plugin-bp/`; do not repeat those procedures in instructions.
- Keep detailed implementation guides canonical in `docs/`. Skill references may link to them with package-relative links.
- Keep the APM package version in `apm.yml` independent from the example plugin runtime version.
- Preserve `.apm/` and `apm.yml` until setup has run `plugin-replace.php`; setup then removes the producer source before installing the versioned package.
- When the foundation changes, update the relevant documentation, APM instruction, skill workflow, and upgrade-check area only where that artifact owns the changed behavior.
<!-- BOILERPLATE-DOCS-END -->

<!-- apm:start -->
<!-- apm:end -->
