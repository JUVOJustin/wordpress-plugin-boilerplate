<?php
/**
 * WP_CLI Setup command integration
 *
 * @package Demo_Plugin
 */

namespace Demo_Plugin\Cli;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RegexIterator;
use WP_CLI;
use WP_CLI\ExitException;
use function WP_CLI\Utils\format_items;

/**
 * Class Setup
 *
 * This class is responsible for performing the initial setup of the plugin.
 */
class Setup {

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Plugin Namespace
	 *
	 * @var string
	 */
	protected string $namespace;

	/**
	 * Plugin Slug
	 *
	 * @var string
	 */
	protected string $slug;

	/**
	 * Plugin Path
	 *
	 * @var string|false
	 */
	protected $path;

	/**
	 * Init Class and set root path of plugin
	 */
	public function __construct() {
		$this->path = realpath( __DIR__ . '/../../' );
	}

	/**
	 * Initial plugin setup
	 *
	 * @param string[] $args Unnamed arguments passed from the command calling.
	 * @param string[] $assoc_args Named arguments passed from the command.
	 *
	 * @throws ExitException CLI ended with error.
	 * @when before_wp_load
	 */
	public function __invoke( array $args, array $assoc_args ): void { // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// if setup file still exists, assume setup has to be made.
		if ( ! file_exists( $this->path . '/setup.php' ) ) {
			WP_CLI::confirm( 'Are you sure you want to rerun the setup?' );
		}

		$this->name = $this->ask( "Enter the 'human' name of the plugin (e.g. My Awesome Plugin):" );
		if ( empty( $this->name ) ) {
			WP_CLI::error( 'You need to provide a name for the plugin.' );
		}

		// Namespace
		$namespace       = $this->to_pascal_snake_case( $this->name );
		$this->namespace = $this->ask( "Enter the namespace in Camel_Snake Case (e.g., 'My_Awesome_Plugin'). Leave empty for default '" . $namespace . "':", $namespace );

		// Slug
		$slug = str_replace( '_', '-', str_replace( ' ', '-', strtolower( $this->name ) ) );

		$this->slug = $this->ask( "Enter the slug you want to use for the plugin as kebab-case (e.g., 'awesome-plugin'). Leave empty for default '" . $slug . "':", $slug );

		WP_CLI::log( 'Using the following values:' );
		format_items(
			'table',
			array(
				array(
					'key'   => 'Plugin Name',
					'value' => $this->name,
				),
				array(
					'key'   => 'Namespace',
					'value' => $this->namespace,
				),
				array(
					'key'   => 'Slug',
					'value' => $this->slug,
				),

			),
			array( 'key', 'value' )
		);

		// Further operations like composer update, npm install, etc.
		$progress = \WP_CLI\Utils\make_progress_bar( 'Setup', 7 );

		// Replace in files
		if (
			! $this->replace_in_files(
				'demo-plugin',
				$this->slug,
				array(
					'.*\.php',
					'.*\.js',
					'.*\.json',
					'.*\.github\/.*\.(yml|md)',
					'.*\.neon',
				)
			)
			|| ! $this->replace_in_files(
				'demo_plugin',
				str_replace( '-', '_', $this->slug ),
				array(
					'.*\.php',
					'.*eslint.*\.js',
					'.*\.github\/.*\.(yml|md)',
				)
			)
			|| ! $this->replace_in_files(
				'Demo_Plugin',
				$this->namespace,
				array(
					'.*\.php',
					'.*\.json',
					'.*\.github\/.*\.(yml|md)',
				)
			)
			|| ! $this->replace_in_files(
				'DEMO_PLUGIN',
				strtoupper( $this->namespace ),
				array(
					'.*\.php',
					'.*\.json',
					'.*\.github\/.*\.(yml|md)',
				)
			)
			|| ! $this->replace_in_files( 'Demo Plugin', $this->name, array( '.*\.php', '.*README\.txt', '.*\.github\/.*\.(yml|md)' ) )
		) {
			WP_CLI::error( 'Error replacing in files.' );
		}
		$progress->tick();

		// rename main files
		$this->rename_files();
		$progress->tick();

		// Remove setup from autoloader
		$this->remove_setup_from_autoload();
		$progress->tick();

		// Fix paths
		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		exec( 'composer dump-autoload && composer update 2>&1', $output, $code );
		if ( 0 !== $code ) {
			WP_CLI::error( 'Error running composer update' );
		}
		$progress->tick();

		exec( 'npm install 2>&1', $output, $code );
		if ( 0 !== $code ) {
			WP_CLI::error( 'Error running npm install' );
		}
		$progress->tick();

		exec( 'npm run production 2>&1', $output, $code );
		if ( 0 !== $code ) {
			WP_CLI::error( 'Error running npm run production' );
		}
		$progress->tick();
		// phpcs:enable

		// Cleanup setup folder
		if ( file_exists( $this->path . '/setup.php' ) ) {
			$removed = unlink( $this->path . '/setup.php' ); // phpcs:disable WordPress.WP.AlternativeFunctions
			if ( ! $removed ) {
				WP_CLI::error( 'Error removing setup file' );
			}
		}

		// All done
		$progress->finish();
		WP_CLI::success( 'Setup completed' );
	}

	/**
	 * Rename files
	 *
	 * @return void
	 * @throws ExitException CLI ended with error.
	 */
	private function rename_files(): void {
		// phpcs:disable WordPress.WP.AlternativeFunctions.rename_rename
		if (
			! rename( $this->path . '/src/Demo_Plugin.php', $this->path . "/src/$this->namespace.php" ) // phpcs:disable WordPress.WP.AlternativeFunctions.rename_rename
			|| ! rename( $this->path . '/demo-plugin.php', $this->path . "/$this->slug.php" ) // phpcs:disable WordPress.WP.AlternativeFunctions.rename_rename
		) {
			WP_CLI::error( 'Error renaming files.' );
		}
		// phpcs:enable
	}

	/**
	 * Converts a given value into pascal snake case
	 *
	 * @param string $value The value to convert to pascal snake case.
	 *
	 * @return string
	 */
	private function to_pascal_snake_case( string $value ): string {
		// Split the string into words based on spaces or underscores
		$words = preg_split( '/[\s_]+/', $value );

		// Capitalize the first letter of each word and then join them with an underscore
		return implode( '_', array_map( 'ucfirst', $words ) );
	}

	/**
	 * Replace string in files using regex
	 *
	 * @param string   $find The string to replace.
	 * @param string   $replace The value to replace the $find string with.
	 * @param string[] $file_patterns array of regex patterns that determine which files to check.
	 *
	 * @return bool
	 * @throws ExitException CLI ended with error.
	 */
	private function replace_in_files( string $find, string $replace, array $file_patterns ): bool {

		$dir    = new RecursiveDirectoryIterator( $this->path, FilesystemIterator::SKIP_DOTS );
		$filter = new \RecursiveCallbackFilterIterator(
			$dir,
			function ( $current ) {
				$path = str_replace( $current->getFilename(), '', $current->getPathname() );
				// Directly check for 'vendor' or 'node_modules' in the path
				if ( strpos( $path, 'vendor' ) !== false || strpos( $path, 'node_modules' ) !== false ) {
					return false;
				}

				// Check for '.git' in the path
				if ( strpos( $path, '.git' ) !== false ) {
					// Ensure that it's not '.github' that's being matched
					if ( strpos( $path, '.github' ) === false ) {
						return false;
					}
				}

				return true;
			}
		);
		$ite    = new \RecursiveIteratorIterator( $filter );

		foreach ( $file_patterns as $file_pattern ) {

			$files = new RegexIterator( $ite, "/^{$file_pattern}$/", RegexIterator::GET_MATCH );
			foreach ( $files as $file ) {

				$file = $file[0];

				// phpcs:disable WordPress.WP.AlternativeFunctions
				$file_contents = file_get_contents( $file );
				if ( empty( $file_contents ) ) {
					\WP_CLI::log( "Skipping file '$file', since it is empty" );
					continue;
				}

				$file_contents = str_replace( $find, $replace, $file_contents );
				if ( ! file_put_contents( $file, $file_contents ) ) {
					\WP_CLI::error( "Error replacing in file: $file" );
				}
				// phpcs:enable
			}
		}

		return true;
	}

	/**
	 * Remove setup.php from file autoload
	 *
	 * @return void
	 */
	private function remove_setup_from_autoload(): void {

		// Path to your composer.json
		$composer_json_path = $this->path . '/composer.json';

		// Load the current composer.json into an array
		$composer_config = json_decode( file_get_contents( $composer_json_path ), true );                                      // phpcs:disable WordPress.WP.AlternativeFunctions

		// Remove the script from the autoload.files section
		if ( isset( $composer_config['autoload']['files'] ) ) {
			$key = array_search( 'setup.php', $composer_config['autoload']['files'], true );
			if ( false !== $key ) {
				unset( $composer_config['autoload']['files'][ $key ] );
			}

			// If the files array is empty, remove it
			if ( empty( $composer_config['autoload']['files'] ) ) {
				unset( $composer_config['autoload']['files'] );
			}
		}

		// Save the modified composer.json
		file_put_contents( $composer_json_path, json_encode( $composer_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); // phpcs:disable WordPress.WP.AlternativeFunctions
	}

	/**
	 * Ask an open question and return the answer
	 *
	 * @param string      $question The question prompted to the user.
	 * @param string|null $default_value Default value used when empty answer provided.
	 *
	 * @return string
	 */
	private function ask( string $question, ?string $default_value = null ): string {
		WP_CLI::log( WP_CLI::colorize( '%4' . $question . '%n' ) );
		$output = trim( fgets( STDIN ) ); // Get input from user

		return $output ? $output : $default_value;
	}
}
