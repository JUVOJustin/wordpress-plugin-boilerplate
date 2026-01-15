# WordPress Abilities API

Expose plugin functionality as structured capabilities for AI assistants and automation tools. Requires **WordPress 6.9+**.

## Quick Start

### 1. Create Category Class

Create `src/Abilities/Categories/Data_Retrieval.php`:

```php
<?php

namespace Demo_Plugin\Abilities\Categories;

use Demo_Plugin\Abilities\Ability_Category_Interface;

/**
 * Category for abilities that retrieve data without modifications.
 */
class Data_Retrieval implements Ability_Category_Interface {

    public static function get_slug(): string {
        return 'data-retrieval';
    }

    public static function get_label(): string {
        return __( 'Data Retrieval', 'demo-plugin' );
    }

    public static function get_description(): string {
        return __( 'Abilities that retrieve and return data from the WordPress site.', 'demo-plugin' );
    }

    public static function get_meta(): array {
        return array();
    }
}
```

### 2. Create Ability Class

Create `src/Abilities/My_Ability.php`:

```php
<?php

namespace Demo_Plugin\Abilities;

use Demo_Plugin\Abilities\Categories\Data_Retrieval;
use WP_Error;

class My_Ability implements Ability_Interface {

    public static function get_name(): string {
        return 'demo-plugin/my-ability';
    }

    public static function get_label(): string {
        return __( 'My Ability', 'demo-plugin' );
    }

    public static function get_description(): string {
        return __( 'Does something useful.', 'demo-plugin' );
    }

    public static function get_category(): string {
        return Data_Retrieval::class;
    }

    public static function get_input_schema(): array {
        return array(
            'type'       => 'object',
            'properties' => array(
                'post_id' => array( 'type' => 'integer', 'minimum' => 1 ),
            ),
            'required' => array( 'post_id' ),
        );
    }

    public static function get_output_schema(): array {
        return array(
            'type'       => 'object',
            'properties' => array(
                'success' => array( 'type' => 'boolean' ),
                'title'   => array( 'type' => 'string' ),
            ),
        );
    }

    public static function get_annotations(): array {
        return array(
            'readonly'    => true,
            'destructive' => false,
            'idempotent'  => true,
        );
    }

    public static function show_rest(): bool {
        return true;
    }

    public static function check_permissions( mixed $input = null ): bool|WP_Error {
        return current_user_can( 'read' );
    }

    public static function execute( mixed $input = null ): mixed {
        $post = get_post( $input['post_id'] );

        if ( ! $post ) {
            return new WP_Error( 'not_found', 'Post not found.', array( 'status' => 404 ) );
        }

        return array( 'success' => true, 'title' => $post->post_title );
    }
}
```

### 3. Register Ability

In `Demo_Plugin.php`:

```php
$this->loader->add_ability( Abilities\My_Ability::class );
```

Categories are automatically registered via the Loader when abilities reference them.

## Ability Interface Reference

| Method | Returns | Purpose |
|--------|---------|---------|
| `get_name()` | `string` | Unique ID: `namespace/ability-name` |
| `get_label()` | `string` | Display name |
| `get_description()` | `string` | What the ability does |
| `get_category()` | `string` | Category class name |
| `get_input_schema()` | `array` | JSON Schema for input |
| `get_output_schema()` | `array` | JSON Schema for output |
| `get_annotations()` | `array` | Behavioral hints (readonly, destructive, idempotent) |
| `show_rest()` | `bool` | Expose via REST API |
| `check_permissions($input)` | `bool\|WP_Error` | Permission check |
| `execute($input)` | `mixed` | Main logic |

## Category Interface Reference

| Method | Returns | Purpose |
|--------|---------|---------|
| `get_slug()` | `string` | Unique slug (lowercase, hyphens only) |
| `get_label()` | `string` | Display name |
| `get_description()` | `string` | Category purpose |
| `get_meta()` | `array` | Optional metadata |

## Usage

```php
$ability = wp_get_ability( 'demo-plugin/my-ability' );
$result  = $ability->execute( array( 'post_id' => 123 ) );

if ( is_wp_error( $result ) ) {
    echo $result->get_error_message();
}
```
