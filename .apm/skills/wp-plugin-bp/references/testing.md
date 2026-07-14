# Testing Command

Write, extend, or debug PHPUnit application tests for this plugin.

## Required Pattern

1. Use the configured wp-env test environment. Do not create a second test bootstrap.
2. Put test files in `tests/php/`.
3. Use no namespace in PHPUnit test files.
4. Extend `WP_UnitTestCase`.
5. Use `self::factory()` for WordPress data.
6. Register hooks inside the test method unless a shared fixture genuinely needs `setUp`.
7. Run tests with `npm run test:php`.

## References

- Load `references/doc-testing.mdx` for detailed PHPUnit conventions.
- Load `references/doc-action-scheduler.mdx` when tests involve Action Scheduler.
