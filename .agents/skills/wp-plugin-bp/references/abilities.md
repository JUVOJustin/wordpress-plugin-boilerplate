# Abilities Command

Implement or update WordPress Abilities API code.

## Workflow

1. Load `references/doc-abilities.mdx`.
2. Implement ability and category interfaces under `src/Abilities/`.
3. Register abilities through the Loader with `add_ability()`.
4. Do not register hooks in constructors.
5. Keep schema, permissions, and callbacks close to the owning ability class.
