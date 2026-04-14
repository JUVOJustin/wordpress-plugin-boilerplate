---
name: application-testing
description: |
  Write, extend, or debug PHPUnit application tests for this WordPress plugin.
  Use this skill whenever tests are involved — adding a test for a new feature,
  testing hook or filter behavior, testing WordPress data operations with factories,
  or when asked to "add a test", "write tests", "test this function", or
  "verify this behavior with a test". This skill is opinionated about the
  project's wp-env-based setup and supersedes any generic PHPUnit guidance.
---

# Application Testing

Tests run exclusively inside the `wp-env` test container. The bootstrap and PHPUnit config are pre-wired. Do not modify `tests/bootstrap.php` or `phpunit.xml.dist`. The plugin is already loaded when tests run — no manual require needed.

For full reference including common patterns, plugin dependencies, and CI details, read `references/testing.mdx`.

## Running the test suite

Follow these steps in order:

1. **Start wp-env** — requires Docker. Spins up the WordPress development and test containers. Skip if the environment is already running.

   ```bash
   npm run env:start
   ```

2. **Run the full test suite** — executes PHPUnit inside the `tests-cli` container against the isolated test database.

   ```bash
   npm run test:php
   ```

## Creating a test file

Drop a `.php` file in `tests/php/`. PHPUnit discovers it automatically — no registration needed.

```php
<?php
/**
 * [What this class tests]
 */
class MyFeatureTest extends WP_UnitTestCase {

    public function test_[describes_the_expected_behaviour](): void {
        // arrange
        // act
        // assert
    }
}
```

**Conventions:**
- No namespace — standard WordPress testing convention
- One class per file; filename must match the class name (`MyFeatureTest.php`)
- Method names describe the expected behaviour: `test_filter_appends_suffix`, `test_meta_is_saved`

## Factories — creating WordPress data

Use `self::factory()` to create posts, users, terms, and comments. Never insert raw SQL. Database state is rolled back automatically between every test.

```php
$post_id = self::factory()->post->create( [ 'post_status' => 'publish' ] );
$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
```

## Hooks and filters

Register hooks inside the test method, not in `setUp` — they are cleaned up automatically after each test.

```php
add_filter( 'demo_plugin/my_filter', fn( $v ) => $v . '-modified' );
$result = apply_filters( 'demo_plugin/my_filter', 'original' );
$this->assertSame( 'original-modified', $result );
```

## Plugin dependencies

If the code under test requires another plugin, install it in the test container by extending `afterStart` in `.wp-env.json`:

```json
{
    "lifecycleScripts": {
        "afterStart": "<existing_commands> && wp-env run tests-cli wp plugin install <plugin-slug> --activate"
    }
}
```

Restart with `npm run env:start` after editing `.wp-env.json`.

## References
- `references/testing.mdx` - Common test patterns, managing plugin dependencies using composer in tests, CI setup
- `references/action-scheduler.mdx` - Knowledge about Action Scheduler and how to make it work in tests if it's a dependency
