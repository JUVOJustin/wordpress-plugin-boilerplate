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

Tests run exclusively inside the `wp-env` test container via:

```bash
npm run env:start   # once, if the environment isn't running yet
npm run test:php    # run the full suite
```

The bootstrap and PHPUnit config are pre-wired. Do not modify `tests/bootstrap.php` or `phpunit.xml.dist`. The plugin is already loaded when tests run — no manual require needed.

For full reference including common patterns, plugin dependencies, and CI details, read `references/testing.mdx`.

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
        "afterStart": "wp-env run cli wp package install wp-cli/i18n-command:v2.7.0 && wp-env run cli wp plugin activate demo-plugin && wp-env run tests-cli wp plugin install <plugin-slug> --activate"
    }
}
```

Restart with `npm run env:start` after editing `.wp-env.json`. See `references/testing.mdx` for details and a concrete example.
