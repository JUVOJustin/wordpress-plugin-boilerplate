<?php

namespace Demo_Plugin\Cli;

use WP_CLI;
use WP_CLI\ExitException;
use function WP_CLI\Utils\format_items;

class Setup {

	protected string $name;
	protected string $namespace;
	protected string $string;
	protected string $path = DEMO_PLUGIN_PATH;

	/**
	 * @throws ExitException
	 */
	public function __invoke( $args, $assoc_args ) {

		if ( file_exists( $this->path . 'setup.php' ) ) {

			if ( ! isset( $assoc_args['name'] ) ) {
				WP_CLI::error( 'You have to provide a plugin name' );
			}

			$this->name = $assoc_args['name'];

			// Namespace
			if ( isset( $assoc_args['namespace'] ) ) {
				$this->namespace = $assoc_args['namespace'];
			} else {
				$this->namespace = $this->toPascalSnakeCase( $this->name );
			}

			// Slug
			if ( isset( $assoc_args['slug'] ) ) {
				$this->slug = $assoc_args['slug'];
			} else {
				$this->slug = str_replace( '_', '-', str_replace( ' ', '-', strtolower( $this->name ) ) );
			}

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

			$this->replace_in_files();
			$this->rename_files();

			// Further operations like composer update, npm install, etc.
			system( 'composer update' );
			system( 'npm install' );
			system( 'npm run production' );

			// Remove setup from autoloader
			$this->removeSetupFromAutoload();

			// Cleanup setup folder
			if ( ! unlink( $this->path . "setup.php" ) ) {
				WP_CLI::error( 'Error removing setup file' );
			}
			WP_CLI::success( 'Setup completed' );

		}
	}

	/**
	 * Replaces slug, namespace and name in files
	 *
	 * @return void
	 * @throws ExitException
	 */
	private function replace_in_files() {

		// Define PHP paths since glob in php is not recursive
		$phpPaths = [ '*.php', '**/*.php', 'tests/**/*.php' ];

		if (
			! $this->replaceInFiles( 'demo-plugin', $this->slug, array_merge( $phpPaths, [ '*.js', '*.json' ] ) )
			|| ! $this->replaceInFiles( 'demo_plugin', str_replace( '-', '_', $this->slug ), $phpPaths )
			|| ! $this->replaceInFiles( 'Demo_Plugin', $this->namespace, array_merge( $phpPaths, [ '*.json' ] ) )
			|| ! $this->replaceInFiles( 'DEMO_PLUGIN', strtoupper( $this->namespace ), $phpPaths )
			|| ! $this->replaceInFiles( 'Demo Plugin', $this->name, [ 'demo-plugin.php', 'README.txt' ] )
		) {
			WP_CLI::error( 'Error replacing in files.' );
		}
		WP_CLI::success( 'Replacements done.' );
	}

	/**
	 * Rename files
	 *
	 * @return void
	 * @throws ExitException
	 */
	private function rename_files() {
		if (
			! rename( $this->path . 'src/Demo_Plugin.php', $this->path . "src/$this->namespace.php" )
			|| ! rename( $this->path . 'demo-plugin.php', $this->path . "$this->slug.php" )
		) {
			WP_CLI::error( 'Error renaming files.' );
		}
		WP_CLI::success( 'Renaming files done.' );
	}

	/**
	 * @param $string
	 *
	 * @return string
	 */
	private function toPascalSnakeCase( $string ): string {
		// Split the string into words based on spaces or underscores
		$words = preg_split( '/[\s_]+/', $string );

		// Capitalize the first letter of each word and then join them with an underscore
		return implode( '_', array_map( 'ucfirst', $words ) );
	}

	/**
	 * @param string $find
	 * @param string $replace
	 * @param array $filePattern
	 *
	 * @return bool
	 */
	private function replaceInFiles( string $find, string $replace, array $filePattern ): bool {
		foreach ( $filePattern as $pattern ) {
			foreach ( glob( $pattern, GLOB_BRACE ) as $filename ) {
				// Exclude setup.php
				if (
					basename( $filename ) === 'setup.php'
					|| basename( $filename ) === 'Setup.php'
				) {
					continue;
				}
				$fileContents = file_get_contents( $filename );
				$fileContents = str_replace( $find, $replace, $fileContents );
				if ( ! file_put_contents( $filename, $fileContents ) ) {
					echo "Error replacing in file: $filename\n";

					return false;
				}
			}
		}

		return true;
	}

	private function removeSetupFromAutoload() {

		// Path to your composer.json
		$composerJsonPath = $this->path . 'composer.json';

		// Load the current composer.json into an array
		$composerConfig = json_decode(file_get_contents($composerJsonPath), true);

		// Remove the script from the autoload.files section
		if (isset($composerConfig['autoload']['files'])) {
			$key = array_search('setup.php', $composerConfig['autoload']['files']);
			if ($key !== false) {
				unset($composerConfig['autoload']['files'][$key]);
			}

			// If the files array is empty, remove it
			if (empty($composerConfig['autoload']['files'])) {
				unset($composerConfig['autoload']['files']);
			}
		}

		// Save the modified composer.json
		file_put_contents($composerJsonPath, json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

		// Regenerate the autoloader
		system('composer dump-autoload');
	}

	/**
	 * Ask an open question and return the answer
	 *
	 * @param $question
	 *
	 * @return string
	 */
	function ask( $question ): string {
		echo $question . "\n";

		return trim( fgets( STDIN ) ); // Get input from user
	}
}