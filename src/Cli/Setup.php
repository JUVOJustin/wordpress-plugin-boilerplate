<?php
/**
 * WP_CLI Setup command integration.
 *
 * Collects user input and delegates all replacement logic to the shared
 * boilerplate-replace.php script in .agents/skills/boilerplate-update/scripts/.
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
		$script_path = $plugin_path . '/.agents/skills/boilerplate-update/scripts/boilerplate-replace.php';
		if ( ! file_exists( $script_path ) ) {
			WP_CLI::error( "Missing replacement script: {$script_path}" );
		}

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

		$this->remove_setup_wp_cli_dependency( $plugin_path );

		$this->run_shell_command( 'composer dump-autoload && composer update', 'Error running composer update' );
		$progress->tick();

		$this->run_shell_command( 'npm install', 'Error running npm install' );
		$progress->tick();

		$this->run_shell_command( 'npm run build', 'Error running npm run build' );
		$progress->tick();

		$progress->finish();
		WP_CLI::success( 'Setup completed' );
	}

	/**
	 * Invoke the shared replacement script with the user-provided identity values.
	 *
	 * Passing --cleanup-setup removes setup autoload entries and setup files as
	 * part of this call, before any subsequent composer commands regenerate the
	 * autoloader, so the vendor autoload map is built from a clean composer.json.
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
	 * Remove the setup-only WP-CLI Composer dependency after setup has run.
	 *
	 * The setup command itself still depends on the local `wp` binary provided by
	 * Composer, but the generated plugin should not keep that dependency.
	 *
	 * @param string $plugin_path Absolute plugin root path.
	 *
	 * @return void
	 */
	private function remove_setup_wp_cli_dependency( string $plugin_path ): void {
		$composer_json_path = $plugin_path . '/composer.json';

		if ( ! file_exists( $composer_json_path ) ) {
			WP_CLI::error( 'Unable to find composer.json after setup replacement.' );
		}

		$composer_json = file_get_contents( $composer_json_path );
		if ( false === $composer_json ) {
			WP_CLI::error( 'Unable to read composer.json after setup replacement.' );
		}

		$composer_config = json_decode( $composer_json, true );
		if ( ! is_array( $composer_config ) ) {
			WP_CLI::error( 'composer.json contains invalid JSON after setup replacement.' );
		}

		if ( ! isset( $composer_config['require-dev']['wp-cli/wp-cli'] ) ) {
			return;
		}

		unset( $composer_config['require-dev']['wp-cli/wp-cli'] );

		if ( empty( $composer_config['require-dev'] ) ) {
			unset( $composer_config['require-dev'] );
		}

		$file_written = file_put_contents(
			$composer_json_path,
			json_encode( $composer_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL
		);

		if ( false === $file_written ) {
			WP_CLI::error( 'Unable to write composer.json after removing setup WP-CLI dependency.' );
		}
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
	 * Convert a value into Pascal_Snake_Case.
	 *
	 * @param string $value Input string.
	 *
	 * @return string
	 */
	private function to_pascal_snake_case( string $value ): string {
		$words = preg_split( '/[\s_]+/', $value );

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
