#!/usr/bin/env php
<?php
/**
 * Self-contained script to replace boilerplate placeholders in WordPress plugin repositories.
 *
 * Modes:
 *   --plugin-name + --plugin-namespace + --plugin-text-domain   Apply replacements
 *   --cleanup-setup (optional)  Also remove setup-only Composer entries and files
 *
 * Source options (optional – defaults to the boilerplate defaults):
 *   --source-plugin-name        Human-readable name currently in the files (default: "Demo Plugin")
 *   --source-plugin-namespace   Namespace currently in the files (default: "Demo_Plugin")
 *   --source-plugin-text-domain Text domain currently in the files (default: "demo-plugin")
 */

const DEFAULT_SOURCE_PLUGIN_NAME = 'Demo Plugin';
const DEFAULT_SOURCE_PLUGIN_NAMESPACE = 'Demo_Plugin';
const DEFAULT_SOURCE_PLUGIN_TEXT_DOMAIN = 'demo-plugin';

exit(main());

/**
 * Parse CLI options, dispatch to the correct mode, and return an exit code.
 *
 * @return int
 */
function main(): int
{
	try {
		$options = parse_options();

		if (isset($options['help'])) {
			print_help();

			return 0;
		}

		$plugin_path = resolve_plugin_path(get_option_string($options, 'path', getcwd() ?: '.'));

		// Replace mode: all three identity options are required.
		$plugin_name        = get_required_option($options, 'plugin-name');
		$plugin_namespace   = get_required_option($options, 'plugin-namespace');
		$plugin_text_domain = get_required_option($options, 'plugin-text-domain');

		// Source options default to the boilerplate defaults so the script works
		// on a fresh clone without any extra arguments.
		$source_plugin_name        = get_option_string($options, 'source-plugin-name', DEFAULT_SOURCE_PLUGIN_NAME);
		$source_plugin_namespace   = get_option_string($options, 'source-plugin-namespace', DEFAULT_SOURCE_PLUGIN_NAMESPACE);
		$source_plugin_text_domain = get_option_string($options, 'source-plugin-text-domain', DEFAULT_SOURCE_PLUGIN_TEXT_DOMAIN);

		apply_replacements($plugin_path, $plugin_name, $plugin_namespace, $plugin_text_domain, $source_plugin_name, $source_plugin_namespace, $source_plugin_text_domain);

		if (isset($options['cleanup-setup'])) {
			cleanup_setup_artifacts($plugin_path);
		}

		fwrite(STDOUT, "Boilerplate placeholders replaced successfully in: {$plugin_path}" . PHP_EOL);

		return 0;
	} catch (RuntimeException $exception) {
		fwrite(STDERR, 'Error: ' . $exception->getMessage() . PHP_EOL);

		return 1;
	}
}

// ---------------------------------------------------------------------------
// Replacement logic
// ---------------------------------------------------------------------------

/**
 * Apply all boilerplate placeholder replacements that define plugin identity.
 *
 * @param string $plugin_path Absolute plugin root path.
 * @param string $plugin_name Human readable plugin name.
 * @param string $plugin_namespace Root namespace in Pascal_Snake_Case.
 * @param string $plugin_text_domain Text domain in kebab-case.
 * @param string $source_plugin_name Human-readable name currently in the files.
 * @param string $source_plugin_namespace Namespace currently in the files.
 * @param string $source_plugin_text_domain Text domain slug currently in the files.
 *
 * @return void
 */
function apply_replacements(string $plugin_path, string $plugin_name, string $plugin_namespace, string $plugin_text_domain, string $source_plugin_name = DEFAULT_SOURCE_PLUGIN_NAME, string $source_plugin_namespace = DEFAULT_SOURCE_PLUGIN_NAMESPACE, string $source_plugin_text_domain = DEFAULT_SOURCE_PLUGIN_TEXT_DOMAIN): void
{
	$plugin_slug = to_slug($plugin_text_domain);
	if ('' === $plugin_slug) {
		throw new RuntimeException('Plugin text domain cannot be empty.');
	}

	if ('' === trim($plugin_name)) {
		throw new RuntimeException('Plugin name cannot be empty.');
	}

	if ('' === trim($plugin_namespace)) {
		throw new RuntimeException('Plugin namespace cannot be empty.');
	}

	$source_slug      = to_slug($source_plugin_text_domain);
	$source_namespace = trim($source_plugin_namespace);

	if ('' === $source_slug) {
		throw new RuntimeException('Source plugin text domain cannot be empty.');
	}

	if ('' === $source_namespace) {
		throw new RuntimeException('Source plugin namespace cannot be empty.');
	}

	if ('' === trim($source_plugin_name)) {
		throw new RuntimeException('Source plugin name cannot be empty.');
	}

	$md_patterns = array(
		'.*README\.md',
		'.*docs\/.*\.(md|mdx)',
		'.*AGENTS\.md',
		'.*\.agents\/skills\/.*\.md',
		'.*\.opencode\/command\/.*\.md',
	);

	// kebab-case slug (demo-plugin -> my-plugin)
	replace_in_files(
		$plugin_path,
		$source_slug,
		$plugin_slug,
		array_merge(
			array(
				'.*\.php',
				'.*\.js',
				'.*\.json',
				'.*\.github\/.*\.(yml|md)',
				'.*\.neon',
			),
			$md_patterns
		)
	);

	// snake_case slug (demo_plugin -> my_plugin)
	replace_in_files(
		$plugin_path,
		str_replace('-', '_', $source_slug),
		str_replace('-', '_', $plugin_slug),
		array_merge(
			array(
				'.*\.php',
				'.*eslint.*\.js',
				'.*\.github\/.*\.(yml|md)',
			),
			$md_patterns
		)
	);

	// Namespace (Demo_Plugin -> My_Plugin)
	replace_in_files(
		$plugin_path,
		$source_namespace,
		$plugin_namespace,
		array_merge(
			array(
				'.*\.php',
				'.*\.json',
				'.*\.github\/.*\.(yml|md)',
			),
			$md_patterns
		)
	);

	// Constant prefix (DEMO_PLUGIN -> MY_PLUGIN)
	replace_in_files(
		$plugin_path,
		strtoupper($source_namespace),
		strtoupper($plugin_namespace),
		array_merge(
			array(
				'.*\.php',
				'.*\.json',
				'.*\.github\/.*\.(yml|md)',
			),
			$md_patterns
		)
	);

	// Human name (Demo Plugin -> My Awesome Plugin)
	replace_in_files(
		$plugin_path,
		trim($source_plugin_name),
		$plugin_name,
		array_merge(
			array(
				'.*\.php',
				'.*README\.txt',
				'.*\.github\/.*\.(yml|md)',
			),
			$md_patterns
		)
	);

	rename_template_files($plugin_path, $plugin_namespace, $plugin_slug, $source_namespace, $source_slug);
	remove_boilerplate_docs($plugin_path);
}

// ---------------------------------------------------------------------------
// File operations
// ---------------------------------------------------------------------------

/**
 * Create a recursive file iterator that skips vendor, node_modules, and .git.
 *
 * @param string $plugin_path Absolute plugin root.
 *
 * @return RecursiveIteratorIterator<RecursiveCallbackFilterIterator>
 */
function create_filtered_file_iterator(string $plugin_path): RecursiveIteratorIterator
{
	$dir = new RecursiveDirectoryIterator($plugin_path, FilesystemIterator::SKIP_DOTS);

	$filter = new RecursiveCallbackFilterIterator(
		$dir,
		function ($current) {
			$pathname = str_replace('\\', '/', $current->getPathname());

			if (false !== strpos($pathname, '/vendor/') || str_ends_with($pathname, '/vendor')) {
				return false;
			}

			if (false !== strpos($pathname, '/node_modules/') || str_ends_with($pathname, '/node_modules')) {
				return false;
			}

			// Skip .git but allow .github
			if (preg_match('#/\.git(/|$)#', $pathname) && false === strpos($pathname, '.github')) {
				return false;
			}

			return true;
		}
	);

	return new RecursiveIteratorIterator($filter);
}

/**
 * Replace a single placeholder across all matching files in the plugin tree.
 *
 * @param string   $plugin_path Absolute plugin root.
 * @param string   $find Placeholder to replace.
 * @param string   $replace Replacement value.
 * @param string[] $file_patterns Regex path patterns that define eligible files.
 *
 * @return void
 */
function replace_in_files(string $plugin_path, string $find, string $replace, array $file_patterns): void
{
	$iterator = create_filtered_file_iterator($plugin_path);

	foreach ($iterator as $file_info) {
		if (! $file_info->isFile()) {
			continue;
		}

		$file_path = str_replace('\\', '/', $file_info->getPathname());
		if (! matches_file_patterns($file_path, $file_patterns)) {
			continue;
		}

		$file_contents = file_get_contents($file_path);
		if (false === $file_contents || '' === $file_contents) {
			continue;
		}

		$new_contents = str_replace($find, $replace, $file_contents);
		if ($new_contents === $file_contents) {
			continue;
		}

		if (false === file_put_contents($file_path, $new_contents)) {
			throw new RuntimeException("Error replacing placeholder in file: {$file_path}");
		}
	}
}

/**
 * Check whether a path matches at least one allowed regex pattern.
 *
 * @param string   $path Absolute file path.
 * @param string[] $file_patterns Regex file patterns.
 *
 * @return bool
 */
function matches_file_patterns(string $path, array $file_patterns): bool
{
	foreach ($file_patterns as $file_pattern) {
		if (1 === preg_match("/^{$file_pattern}$/", $path)) {
			return true;
		}
	}

	return false;
}

/**
 * Rename the boilerplate template files to the final plugin names.
 *
 * @param string $plugin_path Plugin root path.
 * @param string $plugin_namespace Root plugin namespace.
 * @param string $plugin_slug Plugin slug.
 * @param string $source_namespace Source namespace currently used in file names.
 * @param string $source_slug Source slug currently used in file names.
 *
 * @return void
 */
function rename_template_files(string $plugin_path, string $plugin_namespace, string $plugin_slug, string $source_namespace = DEFAULT_SOURCE_PLUGIN_NAMESPACE, string $source_slug = DEFAULT_SOURCE_PLUGIN_TEXT_DOMAIN): void
{
	rename_if_exists($plugin_path . "/src/{$source_namespace}.php", $plugin_path . "/src/{$plugin_namespace}.php");
	rename_if_exists($plugin_path . "/{$source_slug}.php", $plugin_path . "/{$plugin_slug}.php");
}

/**
 * Rename a file when the source still exists and destination does not.
 *
 * @param string $source Source file path.
 * @param string $destination Destination file path.
 *
 * @return void
 */
function rename_if_exists(string $source, string $destination): void
{
	if ($source === $destination || ! file_exists($source)) {
		return;
	}

	if (file_exists($destination)) {
		throw new RuntimeException("Cannot rename '{$source}' to '{$destination}' because destination already exists.");
	}

	if (! rename($source, $destination)) {
		throw new RuntimeException("Error renaming '{$source}' to '{$destination}'.");
	}
}

/**
 * Strip content between BOILERPLATE-DOCS-START and BOILERPLATE-DOCS-END markers
 * from all eligible files in the plugin tree.
 *
 * Supports PHP comments (`// <BOILERPLATE-DOCS-START>`) and HTML/Markdown
 * comments (`<!-- BOILERPLATE-DOCS-START -->`).
 *
 * @param string $plugin_path Plugin root path.
 *
 * @return void
 */
function remove_boilerplate_docs(string $plugin_path): void
{
	$php_pattern  = '/^\s*\/\/\s*<BOILERPLATE-DOCS-START>.*?^\s*\/\/\s*<BOILERPLATE-DOCS-END>\s*\n?/ms';
	$html_pattern = '/^\s*<!--\s*BOILERPLATE-DOCS-START\s*-->.*?^\s*<!--\s*BOILERPLATE-DOCS-END\s*-->\s*\n?/ms';

	$iterator = create_filtered_file_iterator($plugin_path);

	foreach ($iterator as $file_info) {
		if (! $file_info->isFile()) {
			continue;
		}

		$file_path = $file_info->getPathname();
		$extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

		if (! in_array($extension, array('php', 'md', 'mdx', 'yml', 'yaml', 'txt'), true)) {
			continue;
		}

		$content = file_get_contents($file_path);
		if (false === $content) {
			continue;
		}

		$new_content = preg_replace($php_pattern, '', $content);
		$new_content = preg_replace($html_pattern, '', (string) $new_content);

		if ($new_content !== $content) {
			file_put_contents($file_path, $new_content);
		}
	}
}

// ---------------------------------------------------------------------------
// Setup cleanup
// ---------------------------------------------------------------------------

/**
 * Remove setup-only Composer entries and files that should not remain after
 * the initial setup.
 *
 * @param string $plugin_path Plugin root path.
 *
 * @return void
 */
function cleanup_setup_artifacts(string $plugin_path): void
{
	cleanup_setup_composer_config($plugin_path);
	remove_file_if_exists($plugin_path . '/setup.php');
	remove_file_if_exists($plugin_path . '/src/Cli/Setup.php');
	remove_file_if_exists($plugin_path . '/context7.json');
}

/**
 * Update composer.json to remove setup-only autoload entries, hooks, and
 * dependencies.
 *
 * @param string $plugin_path Plugin root path.
 *
 * @return void
 */
function cleanup_setup_composer_config(string $plugin_path): void
{
	$composer_json_path = $plugin_path . '/composer.json';
	if (! file_exists($composer_json_path)) {
		return;
	}

	$composer_config = json_decode((string) file_get_contents($composer_json_path), true);
	if (! is_array($composer_config)) {
		throw new RuntimeException('composer.json contains invalid JSON.');
	}

	if (isset($composer_config['autoload']['files']) && is_array($composer_config['autoload']['files'])) {
		$key = array_search('setup.php', $composer_config['autoload']['files'], true);
		if (false !== $key) {
			unset($composer_config['autoload']['files'][$key]);
			$composer_config['autoload']['files'] = array_values($composer_config['autoload']['files']);
		}

		if (empty($composer_config['autoload']['files'])) {
			unset($composer_config['autoload']['files']);
		}
	}

	if (isset($composer_config['scripts']['post-create-project-cmd']) && is_array($composer_config['scripts']['post-create-project-cmd'])) {
		$composer_config['scripts']['post-create-project-cmd'] = array_values(
			array_filter(
				$composer_config['scripts']['post-create-project-cmd'],
				function ($command): bool {
					return ! is_string($command) || false === strpos($command, 'wp setup');
				}
			)
		);

		if (empty($composer_config['scripts']['post-create-project-cmd'])) {
			unset($composer_config['scripts']['post-create-project-cmd']);
		}
	}

	if (isset($composer_config['require-dev']['wp-cli/wp-cli'])) {
		unset($composer_config['require-dev']['wp-cli/wp-cli']);
	}

	if (empty($composer_config['require-dev'])) {
		unset($composer_config['require-dev']);
	}

	if (false === file_put_contents($composer_json_path, json_encode($composer_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL)) {
		throw new RuntimeException('Unable to write composer.json.');
	}
}

/**
 * Remove a file if it exists.
 *
 * @param string $file Path to file.
 *
 * @return void
 */
function remove_file_if_exists(string $file): void
{
	if (! file_exists($file)) {
		return;
	}

	if (! unlink($file)) {
		throw new RuntimeException("Error removing file: {$file}");
	}
}

// ---------------------------------------------------------------------------
// String helpers
// ---------------------------------------------------------------------------

/**
 * Convert a value into Pascal_Snake_Case for namespaces.
 *
 * @param string $value Input string.
 *
 * @return string
 */
function to_pascal_snake_case(string $value): string
{
	$words = preg_split('/[^a-zA-Z0-9]+/', $value);
	if (false === $words) {
		return '';
	}

	$words = array_filter($words, 'strlen');

	return implode(
		'_',
		array_map(
			function (string $word): string {
				return ucfirst(strtolower($word));
			},
			$words
		)
	);
}

/**
 * Convert a value into a kebab-case text domain slug.
 *
 * @param string $value Input string.
 *
 * @return string
 */
function to_slug(string $value): string
{
	$slug = strtolower(trim($value));
	$slug = preg_replace('/[\s_]+/', '-', $slug);
	$slug = preg_replace('/[^a-z0-9\-]+/', '', (string) $slug);
	$slug = preg_replace('/\-+/', '-', (string) $slug);

	return trim((string) $slug, '-');
}

// ---------------------------------------------------------------------------
// CLI option helpers
// ---------------------------------------------------------------------------

/**
 * Parse supported command line options.
 *
 * @return array<string, mixed>
 */
function parse_options(): array
{
	$options = getopt(
		'',
		array(
			'path:',
			'plugin-name:',
			'plugin-namespace:',
			'plugin-text-domain:',
			'source-plugin-name:',
			'source-plugin-namespace:',
			'source-plugin-text-domain:',
			'cleanup-setup',
			'help',
		)
	);

	if (false === $options) {
		throw new RuntimeException('Unable to parse CLI options.');
	}

	return $options;
}

/**
 * Resolve and validate the target plugin path.
 *
 * @param string $path Path provided by the caller.
 *
 * @return string
 */
function resolve_plugin_path(string $path): string
{
	$resolved_path = realpath($path);
	if (false === $resolved_path) {
		throw new RuntimeException("Invalid plugin path: {$path}");
	}

	return $resolved_path;
}

/**
 * Read an optional string option.
 *
 * @param array<string, mixed> $options Parsed option map.
 * @param string               $key Option key without leading dashes.
 * @param string               $default Default value when option is missing.
 *
 * @return string
 */
function get_option_string(array $options, string $key, string $default): string
{
	if (! isset($options[$key])) {
		return $default;
	}

	$value = $options[$key];
	if (! is_string($value) || '' === trim($value)) {
		return $default;
	}

	return trim($value);
}

/**
 * Read a required option and validate non-empty input.
 *
 * @param array<string, mixed> $options Parsed option map.
 * @param string               $key Required option key.
 *
 * @return string
 */
function get_required_option(array $options, string $key): string
{
	$value = get_option_string($options, $key, '');
	if ('' !== $value) {
		return $value;
	}

	throw new RuntimeException("Missing required option --{$key}.");
}

/**
 * Print usage information.
 *
 * @return void
 */
function print_help(): void
{
	$help = sprintf(
		<<<'HELP'
Boilerplate replacement helper

Usage:
  php boilerplate-replace.php --plugin-name <name> --plugin-namespace <ns> --plugin-text-domain <td> [--path <plugin-path>] [--cleanup-setup]
  php boilerplate-replace.php --plugin-name <name> --plugin-namespace <ns> --plugin-text-domain <td> --source-plugin-name <name> --source-plugin-namespace <ns> --source-plugin-text-domain <td> [--path <plugin-path>]

Options:
  --path                        Target plugin path (default: current directory)
  --plugin-name                 Human-readable plugin name (e.g. "My Awesome Plugin")
  --plugin-namespace            Root namespace (e.g. "My_Awesome_Plugin")
  --plugin-text-domain          Text domain slug (e.g. "my-awesome-plugin")
  --source-plugin-name          Human-readable name currently in the files (default: "%s")
  --source-plugin-namespace     Namespace currently in the files (default: "%s")
  --source-plugin-text-domain   Text domain slug currently in the files (default: "%s")
  --cleanup-setup               Also remove setup-only Composer entries and files after replacement
  --help                        Show this help

Examples:
  # Initial setup from the boilerplate defaults:
  php boilerplate-replace.php --plugin-name "My Plugin" --plugin-namespace "My_Plugin" --plugin-text-domain "my-plugin" --path /path/to/plugin

  # Rename an already-customised plugin (e.g. "My Plugin" -> "Better Plugin"):
  php boilerplate-replace.php \
    --source-plugin-name "My Plugin" --source-plugin-namespace "My_Plugin" --source-plugin-text-domain "my-plugin" \
    --plugin-name "Better Plugin" --plugin-namespace "Better_Plugin" --plugin-text-domain "better-plugin" \
    --path /path/to/plugin
HELP,
		DEFAULT_SOURCE_PLUGIN_NAME,
		DEFAULT_SOURCE_PLUGIN_NAMESPACE,
		DEFAULT_SOURCE_PLUGIN_TEXT_DOMAIN
	);

	fwrite(STDOUT, $help . PHP_EOL);
}
