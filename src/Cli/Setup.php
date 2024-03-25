<?php

namespace Demo_Plugin\Cli;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use WP_CLI;
use WP_CLI\ExitException;
use function WP_CLI\Utils\format_items;

class Setup {

	protected string $name;
	protected string $namespace;
	protected string $slug;
	protected string $path;

	/**
	 */
	public function __construct() {
		$this->path = realpath( __DIR__ . '/../../' );
	}

	/**
	 * Initial plugin setup
	 *
	 * @param string[] $args
	 * @param string[] $assoc_args
	 *
	 * @throws ExitException
	 * @when before_wp_load
	 */
	public function __invoke( array $args, array $assoc_args ): void {

		// if setup file still exists, assume setup has to be made
		if ( ! file_exists( $this->path . '/setup.php' ) ) {
			WP_CLI::confirm( "Are you sure you want to rerun the setup?" );
		}

		$this->name = $this->ask( "Enter the 'human' name of the plugin (e.g. My Awesome Plugin):" );
		if ( empty( $this->name ) ) {
			WP_CLI::error( 'You need to provide a name for the plugin.' );
		}

		// Namespace
		$namespace       = $this->toPascalSnakeCase( $this->name );
		$this->namespace = $this->ask( "Enter the namespace in Camel_Snake Case (e.g., 'My_Awesome_Plugin'). Leave empty for default '" . $namespace . "':", $namespace );

		// Slug
		$slug = str_replace( '_', '-', str_replace( ' ', '-', strtolower( $this->name ) ) );;
		$this->slug = $this->ask( "Enter the slug you want to use for the plugin as kebab-case (e.g., 'awesome-plugin'). Leave empty for default '" . $slug . "':", $slug );

		WP_CLI::log( "Using the following values:" );
		format_items( 'table', [
			[
				'key'   => 'Plugin Name',
				'value' => $this->name,
			],
			[
				'key'   => 'Namespace',
				'value' => $this->namespace,
			],
			[
				'key'   => 'Slug',
				'value' => $this->slug,
			],

		], array( 'key', 'value' ) );

		// Further operations like composer update, npm install, etc.
		$progress = \WP_CLI\Utils\make_progress_bar( 'Setup', 7 );

		// Replace in files
		if (
			! $this->replaceInFiles( 'demo-plugin', $this->slug, [ '.*\.php', '.*\.js', '.*\.json' ] )
			|| ! $this->replaceInFiles( 'demo_plugin', str_replace( '-', '_', $this->slug ), [ '.*\.php' ] )
			|| ! $this->replaceInFiles( 'Demo_Plugin', $this->namespace, [ '.*\.php', '.*\.json' ] )
			|| ! $this->replaceInFiles( 'DEMO_PLUGIN', strtoupper( $this->namespace ), [ '.*\.php', '.*\.json' ] )
			|| ! $this->replaceInFiles( 'Demo Plugin', $this->name, [ '.*\.php', '.*README\.txt' ] )
		) {
			WP_CLI::error( 'Error replacing in files.' );
		}
		$progress->tick();

		$this->rename_files();
		$progress->tick();

		// Remove setup from autoloader
		$this->removeSetupFromAutoload();
		$progress->tick();

		// Fix paths
		exec( "composer dump-autoload && composer update 2>&1", $output, $code );
		if ( $code !== 0 ) {
			WP_CLI::error( 'Error running composer update' );
		}
		$progress->tick();

		exec( "npm install 2>&1", $output, $code );
		if ( $code !== 0 ) {
			WP_CLI::error( 'Error running npm install' );
		}
		$progress->tick();

		exec( "npm run production 2>&1", $output, $code );
		if ( $code !== 0 ) {
			WP_CLI::error( 'Error running npm run production' );
		}
		$progress->tick();

		// Cleanup setup folder
		if ( file_exists( $this->path . "/setup.php" ) && ! unlink( $this->path . "/setup.php" ) ) {
			WP_CLI::error( 'Error removing setup file' );
		}

		// All done
		$progress->finish();
		WP_CLI::success( 'Setup completed' );

	}

	/**
	 * Rename files
	 *
	 * @return void
	 * @throws ExitException
	 */
	private function rename_files(): void {
		if (
			! rename( $this->path . '/src/Demo_Plugin.php', $this->path . "/src/$this->namespace.php" )
			|| ! rename( $this->path . '/demo-plugin.php', $this->path . "/$this->slug.php" )
		) {
			WP_CLI::error( 'Error renaming files.' );
		}
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	private function toPascalSnakeCase( string $string ): string {
		// Split the string into words based on spaces or underscores
		$words = preg_split( '/[\s_]+/', $string );

		// Capitalize the first letter of each word and then join them with an underscore
		return implode( '_', array_map( 'ucfirst', $words ) );
	}

	/**
	 * Replace string in files using glob
	 *
	 * @param string $find
	 * @param string $replace
	 * @param string[] $filePatterns array of glob patterns
	 *
	 * @return bool
	 */
	private function replaceInFiles( string $find, string $replace, array $filePatterns ): bool {

		$dir = new RecursiveDirectoryIterator( $this->path );
		$ite = new RecursiveIteratorIterator( $dir );

		foreach ( $filePatterns as $filePattern ) {

			$files = new RegexIterator( $ite, "/^(?!.*(\/vendor\/|\/node_modules\/))$filePattern$/", RegexIterator::GET_MATCH );
			foreach ( $files as $file ) {

				$file = $file[0];

				$fileContents = file_get_contents( $file );
				$fileContents = str_replace( $find, $replace, $fileContents );
				if ( ! file_put_contents( $file, $fileContents ) ) {
					echo "Error replacing in file: $file\n";

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Remove setup.php from file autoload
	 *
	 * @return void
	 */
	private function removeSetupFromAutoload(): void {

		// Path to your composer.json
		$composerJsonPath = $this->path . '/composer.json';

		// Load the current composer.json into an array
		$composerConfig = json_decode( file_get_contents( $composerJsonPath ), true );

		// Remove the script from the autoload.files section
		if ( isset( $composerConfig['autoload']['files'] ) ) {
			$key = array_search( 'setup.php', $composerConfig['autoload']['files'] );
			if ( $key !== false ) {
				unset( $composerConfig['autoload']['files'][ $key ] );
			}

			// If the files array is empty, remove it
			if ( empty( $composerConfig['autoload']['files'] ) ) {
				unset( $composerConfig['autoload']['files'] );
			}
		}

		// Save the modified composer.json
		file_put_contents( $composerJsonPath, json_encode( $composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * Ask an open question and return the answer
	 *
	 * @param string $question
	 * @param string|null $default
	 *
	 * @return string
	 */
	private function ask( string $question, ?string $default = null ): string {
		WP_CLI::log( WP_CLI::colorize( '%4' . $question . '%n' ) );
		$output = trim( fgets( STDIN ) ); // Get input from user

		return $output ? $output : $default;
	}
}