# Develop and Ship Blocks

The project has builtin block support. This means it has an opinionated way to use blocks, but also automates a lot of the tedious tasks like registration and asset management.

## Create your first block

Creating a block is as simple as running `npm run create-block`. The script will prompt you for the block name and other
details, and then generate the necessary files in the `src/Blocks/` directory.

If you take a look at the `package.json` file, you will see that the `create-block` script used but with the correct namespace and textdomain already applied.

Thats it! The block will be automatically registered and the assets enqueued.

## How Blocks are registered
Blocks are automatically registered using the `wp_register_block_types_from_metadata_collection` function. It is a fancy way to automatically register blocks and enqueue their scripts and styles.
To make this work the `npm run start` and `npm run build` scripts make use of the `--blocks-manifest` flag that generates a `blocks-manifest.json` file in the `build/` directory.

Luckily this is all done for you automatically! No need for any manual registration or code.

## Using webpack-entrypoints styles in the editor
There may be situations where you want to use styles defined in the `webpack-entrypoints` files in the block editor.
For example if you want the styling of your block to be the same on frontend and backend but also requires e.g. variables from the plugins global styles.
Another usecase is when using [server-side rendering for block previews](https://github.com/WordPress/gutenberg/tree/trunk/packages/server-side-render).

To achieve this you can add the follwing snippet in the `register_blocks` method of the plugins main class.

```php
/**
 * Enqueue entrypoint in the editor.
 * Should be used for shared styles and scripts
 */
add_action('enqueue_block_assets', function () {
    $this->enqueue_entrypoint( 'demo-plugin-frontend' );
});
```