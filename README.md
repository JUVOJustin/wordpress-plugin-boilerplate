# What is this boilerplate
This boilerplate is a fork of [WordPress Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate) but with some additional features and improvements. It is a modern, organized, and object-oriented foundation for building high-quality WordPress plugins.

## Features of this boilerplate
- Namespaces support using composer
- Easy Shortcode Registration through the loader
- PHPStan with ready-made Github actions
- Bud.js for simple bundling and build of assets
- Ready made Github actions, for building and bundling

# Setup
## Step 1: Create Your Project
Run the following command to create your project. This will download the boilerplate and automatically run the `setup.php` script for initial configuration:

```
composer create-project juvo/wordpress-plugin-boilerplate path/to/your-new-plugin
```

## Step 2: Configure Your Plugin (Automatic Prompt)
Upon project creation, you'll be guided through a series of prompts to configure your plugin:

- **Plugin Name**: Enter the name of your plugin.
- **Namespace (optional)**: Suggests a default namespace based on your plugin name but allows customization.
- **Plugin Slug (optional)**: Choose a slug for your plugin; a default based on your plugin name is suggested.

Your inputs will automatically tailor the boilerplate to match your plugin's identity.

## Step 3: Finalization (Optional)
After configuration, the setup will finalize by updating files, renaming relevant items, and performing cleanup actions, including:
- Replacing placeholders with your specified details.
- Renaming files to match your plugin's namespace and slug.
- Running `composer update` and `npm install` to install dependencies.
- Cleaning up by removing the `setup.php` file.

At this point the plugin is set up and good to go. Now it is your time to change to adjust plugin and readme headers according to your needs.

## Using SatisPress for Premium Dependencies
For integrating premium plugins like ACF Pro, our boilerplate uses [SatisPress](https://github.com/cedaro/satispress). To add or update dependencies:

1. Navigate to `tests/setup` in your plugin directory.
2. Update `composer.json` with your desired packages.
3. Run `npm run composer:test` for easy dependency management within the subdirectory.

**Note**: You'll be prompted for credentials during setup. Ensure `auth.json` is not committed to your repository. GitHub Actions should be configured with a `SATISPRESS_KEY` secret for automation.

### Wrapping Up
That's it! Your plugin is now ready for development. Dive into creating your next remarkable WordPress plugin with ease and efficiency.