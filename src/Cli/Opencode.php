<?php
/**
 * WP_CLI Opencode command integration
 *
 * This class provides commands for managing OpenCode AI agent skills.
 * It allows fetching and updating skills from the Automattic/agent-skills repository.
 *
 * @package Demo_Plugin
 */

namespace Demo_Plugin\Cli;

use WP_CLI;
use WP_CLI\ExitException;

/**
 * Class Opencode
 *
 * Manages OpenCode AI agent skills from the Automattic/agent-skills repository.
 * Skills are stored in .opencode/skill/<skill-name>/ directory.
 */
class Opencode {

	/**
	 * The GitHub repository containing the skills
	 */
	const SKILLS_REPO = 'https://github.com/Automattic/agent-skills';

	/**
	 * Default skills to install when no skills are specified
	 *
	 * @var string[]
	 */
	const DEFAULT_SKILLS = array(
		'wp-interactivity-api',
		'wp-project-triage',
		'wp-block-development',
	);

	/**
	 * Plugin root path
	 *
	 * @var string|false
	 */
	protected $plugin_path;

	/**
	 * Skills directory path
	 *
	 * @var string
	 */
	protected string $skills_dir;

	/**
	 * Temporary directory for cloning
	 *
	 * @var string
	 */
	protected string $temp_dir;

	/**
	 * Initialize the class and set paths
	 */
	public function __construct() {
		$this->plugin_path = realpath( __DIR__ . '/../../' );
		$this->skills_dir  = $this->plugin_path . '/.opencode/skill';
		$this->temp_dir    = sys_get_temp_dir() . '/opencode-skills-' . time();
	}

	/**
	 * Setup or update OpenCode AI agent skills from Automattic/agent-skills repository
	 *
	 * ## OPTIONS
	 *
	 * [--skills=<skills>]
	 * : Comma-separated list of skills to install/update.
	 * If not provided, default skills will be installed.
	 * Default skills: wp-interactivity-api, wp-project-triage, wp-block-development
	 *
	 * ## EXAMPLES
	 *
	 *     # Install default skills
	 *     wp opencode skill:setup
	 *
	 *     # Install specific skills
	 *     wp opencode skill:setup --skills=wp-interactivity-api,wp-block-development
	 *
	 * @param string[] $args Unnamed arguments passed from the command.
	 * @param string[] $assoc_args Named arguments passed from the command.
	 *
	 * @throws ExitException CLI ended with error.
	 * @when before_wp_load
	 */
	public function skill_setup( array $args, array $assoc_args ): void {
		// Determine which skills to install
		$skills_to_install = $this->get_skills_to_install( $assoc_args );

		if ( empty( $skills_to_install ) ) {
			WP_CLI::error( 'No skills specified for installation.' );
		}

		WP_CLI::log( 'Skills to install/update: ' . implode( ', ', $skills_to_install ) );

		// Create skills directory if it doesn't exist
		if ( ! $this->ensure_skills_directory_exists() ) {
			WP_CLI::error( 'Failed to create skills directory.' );
		}

		// Clone the repository to temp directory
		if ( ! $this->clone_repository() ) {
			WP_CLI::error( 'Failed to clone the skills repository.' );
		}

		// Validate and install each skill
		$success_count = 0;
		$failed_skills = array();

		foreach ( $skills_to_install as $skill ) {
			if ( ! $this->validate_skill_exists( $skill ) ) {
				$failed_skills[] = $skill;
				WP_CLI::warning( "Skill '$skill' not found in upstream repository. Skipping." );
				continue;
			}

			if ( $this->install_skill( $skill ) ) {
				$success_count++;
				WP_CLI::success( "Skill '$skill' installed/updated successfully." );
			} else {
				$failed_skills[] = $skill;
				WP_CLI::warning( "Failed to install/update skill '$skill'." );
			}
		}

		// Cleanup temp directory
		$this->cleanup_temp_directory();

		// Summary
		WP_CLI::log( '' );
		WP_CLI::success( "Successfully installed/updated $success_count skill(s)." );

		if ( ! empty( $failed_skills ) ) {
			WP_CLI::warning( 'Failed skills: ' . implode( ', ', $failed_skills ) );
		}
	}

	/**
	 * Get the list of skills to install from command arguments
	 *
	 * @param string[] $assoc_args Named arguments from the command.
	 *
	 * @return string[] Array of skill names to install.
	 */
	private function get_skills_to_install( array $assoc_args ): array {
		// Check if skills argument is provided
		if ( isset( $assoc_args['skills'] ) && ! empty( $assoc_args['skills'] ) ) {
			$skills = array_map( 'trim', explode( ',', $assoc_args['skills'] ) );
			return array_filter( $skills ); // Remove empty values
		}

		// Return default skills if no argument provided
		return self::DEFAULT_SKILLS;
	}

	/**
	 * Ensure the skills directory exists
	 *
	 * @return bool True if directory exists or was created successfully.
	 */
	private function ensure_skills_directory_exists(): bool {
		if ( is_dir( $this->skills_dir ) ) {
			return true;
		}

		// Create directory with recursive flag
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
		return mkdir( $this->skills_dir, 0755, true );
		// phpcs:enable
	}

	/**
	 * Clone the skills repository to a temporary directory
	 *
	 * @return bool True if clone was successful.
	 */
	private function clone_repository(): bool {
		// Check if git is available
		exec( 'git --version 2>&1', $output, $return_code );
		if ( 0 !== $return_code ) {
			WP_CLI::error( 'Git is not available. Please install git to use this command.' );
			return false;
		}

		WP_CLI::log( 'Cloning skills repository...' );

		// Clone with depth 1 to save bandwidth and time
		$command = sprintf(
			'git clone --depth 1 --filter=blob:none --sparse %s %s 2>&1',
			escapeshellarg( self::SKILLS_REPO ),
			escapeshellarg( $this->temp_dir )
		);

		exec( $command, $output, $return_code );

		if ( 0 !== $return_code ) {
			WP_CLI::debug( 'Clone output: ' . implode( "\n", $output ) );
			return false;
		}

		// Initialize sparse checkout to only get the skills directory
		$sparse_init = sprintf(
			'cd %s && git sparse-checkout init --cone 2>&1',
			escapeshellarg( $this->temp_dir )
		);

		exec( $sparse_init, $output, $return_code );

		if ( 0 !== $return_code ) {
			WP_CLI::debug( 'Sparse init output: ' . implode( "\n", $output ) );
			return false;
		}

		// Set sparse checkout to only include skills directory
		$sparse_set = sprintf(
			'cd %s && git sparse-checkout set skills 2>&1',
			escapeshellarg( $this->temp_dir )
		);

		exec( $sparse_set, $output, $return_code );

		return 0 === $return_code;
	}

	/**
	 * Validate that a skill exists in the cloned repository
	 *
	 * @param string $skill The skill name to validate.
	 *
	 * @return bool True if skill exists.
	 */
	private function validate_skill_exists( string $skill ): bool {
		$skill_path = $this->temp_dir . '/skills/' . $skill;
		return is_dir( $skill_path );
	}

	/**
	 * Install or update a skill from the temporary repository to the skills directory
	 *
	 * @param string $skill The skill name to install.
	 *
	 * @return bool True if installation was successful.
	 */
	private function install_skill( string $skill ): bool {
		$source_path      = $this->temp_dir . '/skills/' . $skill;
		$destination_path = $this->skills_dir . '/' . $skill;

		// Early exit: source doesn't exist
		if ( ! is_dir( $source_path ) ) {
			return false;
		}

		// If destination exists, remove it first (for clean update)
		if ( is_dir( $destination_path ) ) {
			if ( ! $this->remove_directory_recursive( $destination_path ) ) {
				WP_CLI::debug( "Failed to remove existing skill directory: $destination_path" );
				return false;
			}
		}

		// Copy the skill directory
		return $this->copy_directory_recursive( $source_path, $destination_path );
	}

	/**
	 * Recursively copy a directory
	 *
	 * @param string $source Source directory path.
	 * @param string $destination Destination directory path.
	 *
	 * @return bool True if copy was successful.
	 */
	private function copy_directory_recursive( string $source, string $destination ): bool {
		// Early exit: source doesn't exist
		if ( ! is_dir( $source ) ) {
			return false;
		}

		// Create destination directory
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
		if ( ! mkdir( $destination, 0755, true ) ) {
			return false;
		}
		// phpcs:enable

		$dir = opendir( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_opendir

		// Early exit: cannot open directory
		if ( false === $dir ) {
			return false;
		}

		while ( false !== ( $file = readdir( $dir ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readdir
			// Skip . and ..
			if ( '.' === $file || '..' === $file ) {
				continue;
			}

			$source_file      = $source . '/' . $file;
			$destination_file = $destination . '/' . $file;

			if ( is_dir( $source_file ) ) {
				// Recursively copy subdirectory
				if ( ! $this->copy_directory_recursive( $source_file, $destination_file ) ) {
					closedir( $dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_closedir
					return false;
				}
			} else {
				// Copy file
				// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_copy
				if ( ! copy( $source_file, $destination_file ) ) {
					closedir( $dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_closedir
					return false;
				}
				// phpcs:enable
			}
		}

		closedir( $dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_closedir
		return true;
	}

	/**
	 * Recursively remove a directory
	 *
	 * @param string $directory Directory path to remove.
	 *
	 * @return bool True if removal was successful.
	 */
	private function remove_directory_recursive( string $directory ): bool {
		// Early exit: directory doesn't exist
		if ( ! is_dir( $directory ) ) {
			return true;
		}

		$files = array_diff( scandir( $directory ), array( '.', '..' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_scandir

		foreach ( $files as $file ) {
			$path = $directory . '/' . $file;

			if ( is_dir( $path ) ) {
				if ( ! $this->remove_directory_recursive( $path ) ) {
					return false;
				}
			} else {
				// phpcs:disable WordPress.WP.AlternativeFunctions.unlink_unlink
				if ( ! unlink( $path ) ) {
					return false;
				}
				// phpcs:enable
			}
		}

		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
		return rmdir( $directory );
		// phpcs:enable
	}

	/**
	 * Clean up the temporary directory
	 *
	 * @return void
	 */
	private function cleanup_temp_directory(): void {
		if ( is_dir( $this->temp_dir ) ) {
			$this->remove_directory_recursive( $this->temp_dir );
		}
	}
}
