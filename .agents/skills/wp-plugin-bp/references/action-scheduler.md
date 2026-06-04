# Action Scheduler Command

Add, update, or test Action Scheduler integration.

## Workflow

1. Load `references/doc-action-scheduler.mdx`.
2. Install the dependency with Composer when needed.
3. Load Action Scheduler through the main plugin bootstrap as documented.
4. Register scheduled hooks through the plugin loader or established feature service patterns.
5. For tests, schedule actions and execute them through the real queue runner. Do not call async callbacks directly for integration coverage.

## Testing Rules

- Assert the scheduled action exists before execution.
- Execute through `ActionScheduler_QueueRunner`.
- Assert final state after execution.
- Unschedule leftover actions in `tear_down()`.
