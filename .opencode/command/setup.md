---
description: (Re-)Setup this plugin
---

It is extremely important to always follow this order:

1. Setup commands
`!opencode run "/commands-upsert"`

2. Setup rules
`!opencode run "/rules-upsert"`

3. Upsert Strauss
`!opencode run "/strauss-upsert"`

4. Upsert Q&A
`!opencode run "/qa-upsert"`

5. Update readme
`!opencode run "/readme-update"`