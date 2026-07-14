<?php
/**
 * WP_CLI Setup command integration.
 *
 * Collects user input and delegates replacement logic to the setup script
 * packaged with the wp-plugin-bp APM skill.
 *
 * @package Demo_Plugin
 */

namespace Demo_Plugin\Cli;

use WP_CLI;
use WP_CLI\ExitException;
use function WP_CLI\Utils\format_items;

/**
 * Perform initial plugin setup from the boilerplate template.
 */
class Setup {

	private const SETUP_APM_PACKAGE = 'JUVOJustin/wordpress-plugin-boilerplate';

	private const SETUP_SKILL_SCRIPT = '.apm/skills/wp-plugin-bp/scripts/plugin-replace.php';

	/**
	 * Plugin name provided by the user.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Plugin namespace in Pascal_Snake_Case.
	 *
	 * @var string
	 */
	protected string $namespace;

	/**
	 * Plugin text domain slug in kebab-case.
	 *
	 * @var string
	 */
	protected string $slug;

	/**
	 * Absolute plugin root path.
	 *
	 * @var string|false
	 */
	protected $path;

	/**
	 * Resolve plugin root path for setup operations.
	 */
	public function __construct() {
		$this->path = realpath( __DIR__ . '/../../' );
	}

	/**
	 * Prompt setup values and apply all boilerplate replacements.
	 *
	 * @param string[] $args Unused positional arguments.
	 * @param string[] $assoc_args Unused associative arguments.
	 *
	 * @throws ExitException CLI ended with error.
	 * @when before_wp_load
	 */
	public function __invoke( array $args, array $assoc_args ): void { // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( false === $this->path ) {
			WP_CLI::error( 'Unable to resolve plugin path for setup.' );
		}

		$plugin_path = (string) $this->path;
		$script_path = $this->replacement_script_path( $plugin_path );
		$package_ref = $this->apm_package_reference( $plugin_path );

		// If setup file still exists, assume setup has to be made.
		if ( ! file_exists( $plugin_path . '/setup.php' ) ) {
			WP_CLI::confirm( 'Are you sure you want to rerun the setup?' );
		}

		$this->name = $this->ask( "Enter the 'human' name of the plugin (e.g. My Awesome Plugin):" );
		if ( '' === trim( $this->name ) ) {
			WP_CLI::error( 'You need to provide a name for the plugin.' );
		}

		$default_namespace = $this->to_pascal_snake_case( $this->name );
		$this->namespace   = $this->ask( "Enter the namespace in Camel_Snake Case (e.g., 'My_Awesome_Plugin'). Leave empty for default '{$default_namespace}':", $default_namespace );

		$default_slug = $this->to_slug( $this->name );
		$this->slug   = $this->ask( "Enter the text domain slug you want to use as kebab-case (e.g., 'awesome-plugin'). Leave empty for default '{$default_slug}':", $default_slug );

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
					'key'   => 'Text Domain',
					'value' => $this->slug,
				),
			),
			array( 'key', 'value' )
		);

		$progress = \WP_CLI\Utils\make_progress_bar( 'Setup', 4 );

		$this->run_replacement_script( $script_path, $plugin_path, $this->name, $this->namespace, $this->slug );
		$progress->tick();

		$this->run_shell_command( 'composer dump-autoload && composer update', 'Error running composer update' );
		$progress->tick();

		$this->run_shell_command( 'npm install', 'Error running npm install' );
		$progress->tick();

		$this->run_shell_command( 'npm run build', 'Error running npm run build' );
		$progress->tick();

		$progress->finish();

		WP_CLI::success( 'Setup completed' );
		$this->maybe_install_apm_package( $plugin_path, $package_ref );
	}

	/**
	 * Return the packaged replacement script path.
	 *
	 * @param string $plugin_path Absolute plugin root path.
	 *
	 * @return string
	 */
	private function replacement_script_path( string $plugin_path ): string {
		$script_path = $plugin_path . '/' . self::SETUP_SKILL_SCRIPT;
		if ( ! file_exists( $script_path ) ) {
			WP_CLI::error( "Missing replacement script: {$script_path}" );
		}

		return $script_path;
	}

	/**
	 * Build the versioned APM package reference before setup removes its manifest.
	 *
	 * @param string $plugin_path Absolute plugin root path.
	 *
	 * @return string
	 */
	private function apm_package_reference( string $plugin_path ): string {
		$manifest_path = $plugin_path . '/apm.yml';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- This is a local package manifest, not a remote URL.
		$manifest = file_get_contents( $manifest_path );
		$matches  = array();

		if ( false === $manifest || 1 !== preg_match( '/^version:\s*["\']?([0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?)["\']?\s*$/m', $manifest, $matches ) ) {
			WP_CLI::error( "Missing or invalid APM package version in: {$manifest_path}" );
		}

		$version = $matches[1] ?? '';
		if ( '' === $version ) {
			WP_CLI::error( "Missing APM package version in: {$manifest_path}" );
		}

		return self::SETUP_APM_PACKAGE . '#v' . $version;
	}

	/**
	 * Optionally install the versioned APM package into the initialized plugin.
	 *
	 * Setup removes the package-authoring manifest and `.apm` source. APM then
	 * creates a consumer manifest, lockfile, and native skill deployment.
	 *
	 * @param string $plugin_path Absolute plugin root path.
	 * @param string $package_ref Versioned APM package reference.
	 *
	 * @return void
	 */
	private function maybe_install_apm_package( string $plugin_path, string $package_ref ): void {
		if ( ! $this->ask_confirmation( 'Install the APM package for AI-assisted development? [y/N]' ) ) {
			WP_CLI::log( "APM package was not installed. Run 'apm install {$package_ref}' later if needed." );

			return;
		}

		if ( ! $this->command_is_available( 'apm' ) ) {
			WP_CLI::warning( "APM is not installed. Install it from https://microsoft.github.io/apm/getting-started/installation/, then run 'apm install {$package_ref}'." );

			return;
		}

		$command = implode(
			' ',
			array(
				'cd',
				escapeshellarg( $plugin_path ),
				'&&',
				'apm',
				'install',
				escapeshellarg( $package_ref ),
			)
		);

		$this->run_shell_command( $command, 'Error installing APM package' );
		WP_CLI::success( 'APM package installed' );
	}

	/**
	 * Check whether an executable can run without mutating the plugin.
	 *
	 * @param string $command Executable name.
	 *
	 * @return bool
	 */
	private function command_is_available( string $command ): bool {
		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		exec( escapeshellcmd( $command ) . ' --version 2>&1', $output, $code );
		// phpcs:enable

		return 0 === $code;
	}

	/**
	 * Invoke the shared replacement script with the user-provided identity values.
	 *
	 * Passing --cleanup-setup lets the helper remove setup-only Composer entries
	 * and files before any subsequent Composer commands regenerate the autoloader.
	 *
	 * @param string $script_path Absolute path to replacement script.
	 * @param string $plugin_path Absolute plugin root path.
	 * @param string $plugin_name Human readable plugin name.
	 * @param string $plugin_namespace Plugin namespace.
	 * @param string $plugin_text_domain Plugin text domain.
	 *
	 * @return void
	 */
	private function run_replacement_script( string $script_path, string $plugin_path, string $plugin_name, string $plugin_namespace, string $plugin_text_domain ): void {
		$php = $this->php_binary();

		$command = implode(
			' ',
			array(
				escapeshellarg( $php ),
				escapeshellarg( $script_path ),
				'--path',
				escapeshellarg( $plugin_path ),
				'--plugin-name',
				escapeshellarg( $plugin_name ),
				'--plugin-namespace',
				escapeshellarg( $plugin_namespace ),
				'--plugin-text-domain',
				escapeshellarg( $plugin_text_domain ),
				'--cleanup-setup',
			)
		);

		$this->run_shell_command( $command, 'Error running replacement script' );
	}

	/**
	 * Resolve the PHP binary path used to invoke sub-scripts.
	 *
	 * @return string
	 */
	private function php_binary(): string {
		return defined( 'PHP_BINARY' ) ? PHP_BINARY : 'php';
	}

	/**
	 * Execute a shell command and stop setup when it fails.
	 *
	 * @param string $command Shell command to execute.
	 * @param string $error_message Message shown on failure.
	 *
	 * @return void
	 * @throws ExitException CLI ended with error.
	 */
	private function run_shell_command( string $command, string $error_message ): void {
		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		exec( $command . ' 2>&1', $output, $code );
		// phpcs:enable

		if ( 0 === $code ) {
			return;
		}

		if ( ! empty( $output ) ) {
			WP_CLI::log( implode( PHP_EOL, $output ) );
		}

		WP_CLI::error( $error_message );
	}

	/**
	 * Ask an open question and return the answer.
	 *
	 * @param string      $question Prompt shown in CLI.
	 * @param string|null $default_value Default value used for empty input.
	 *
	 * @return string
	 */
	private function ask( string $question, ?string $default_value = null ): string {
		WP_CLI::log( WP_CLI::colorize( '%4' . $question . '%n' ) );
		$output = trim( (string) fgets( STDIN ) );

		return '' !== $output ? $output : (string) $default_value;
	}

	/**
	 * Ask a yes/no question without aborting setup when the answer is no.
	 *
	 * @param string $question Prompt shown in CLI.
	 *
	 * @return bool
	 */
	private function ask_confirmation( string $question ): bool {
		$answer = strtolower( $this->ask( $question, 'n' ) );

		return in_array( $answer, array( 'y', 'yes' ), true );
	}

	/**
	 * Convert a value into Pascal_Snake_Case.
	 *
	 * @param string $value Input string.
	 *
	 * @return string
	 */
	private function to_pascal_snake_case( string $value ): string {
		$words = preg_split( '/[^a-zA-Z0-9]+/', $value );
		if ( false === $words ) {
			return '';
		}

		$words = array_filter(
			$words,
			static fn ( string $word ): bool => '' !== $word
		);

		return implode( '_', array_map( 'ucfirst', $words ) );
	}

	/**
	 * Convert a value into a kebab-case text domain.
	 *
	 * @param string $value Input string.
	 *
	 * @return string
	 */
	private function to_slug( string $value ): string {
		return str_replace( '_', '-', str_replace( ' ', '-', strtolower( $value ) ) );
	}
}
