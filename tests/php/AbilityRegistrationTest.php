<?php
/**
 * Ensures every ability defined in code is registered with the WordPress Abilities API.
 *
 * Unlike blocks, abilities are not registered automatically: each ability is a plain PHP
 * class implementing Ability_Interface that must be wired up by hand via
 * `$loader->add_ability()` in the plugin's main class. The easy mistake is writing the
 * ability class but forgetting to register it — it then silently does nothing.
 *
 * Because the interface is strict (a static `get_name()` and `get_category()`), every
 * ability can be discovered by reflection and checked against the live registry. This
 * guard discovers every Ability_Interface implementation in the codebase and asserts
 * WordPress knows about it and its category.
 *
 * It needs no edits when abilities are added, and is skipped when none exist (the fresh
 * boilerplate state) or when the Abilities API is unavailable (WordPress < 6.9).
 *
 * @package Demo_Plugin
 */

use Demo_Plugin\Abilities\Ability_Interface;

/**
 * Verifies the manual ability registration wiring end-to-end.
 */
class AbilityRegistrationTest extends WP_UnitTestCase {

	/**
	 * Every Ability_Interface implementation must be registered with WordPress.
	 */
	public function test_defined_abilities_are_registered(): void {
		$this->require_abilities_api();

		foreach ( $this->discover_abilities() as $ability ) {
			$this->assertNotNull(
				wp_get_ability( $ability::get_name() ),
				sprintf(
					'Ability "%s" implements Ability_Interface but is not registered. Did you call $this->loader->add_ability( %s::class ) in the plugin main class?',
					$ability::get_name(),
					$ability
				)
			);
		}
	}

	/**
	 * Each ability's declared category must also be registered.
	 */
	public function test_ability_categories_are_registered(): void {
		$this->require_abilities_api();

		foreach ( $this->discover_abilities() as $ability ) {
			$category = $ability::get_category();

			$this->assertNotNull(
				wp_get_ability_category( $category::get_slug() ),
				sprintf(
					'Ability "%s" declares category "%s" but that category is not registered.',
					$ability::get_name(),
					$category::get_slug()
				)
			);
		}
	}

	/**
	 * Discover every concrete Ability_Interface implementation in the codebase.
	 *
	 * The abilities directory and namespace are derived from the interface itself, so this
	 * keeps working after the boilerplate's namespace is renamed during setup. Skips the
	 * test when no abilities exist yet.
	 *
	 * @return array<int, class-string<Ability_Interface>>
	 */
	private function discover_abilities(): array {
		$interface = new ReflectionClass( Ability_Interface::class );
		$base_ns   = $interface->getNamespaceName();
		$dir       = dirname( (string) $interface->getFileName() );

		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );

		$abilities = array();
		foreach ( $iterator as $file ) {
			if ( 'php' !== $file->getExtension() ) {
				continue;
			}

			$relative = substr( $file->getPathname(), strlen( $dir ) + 1, - strlen( '.php' ) );
			$class    = $base_ns . '\\' . str_replace( '/', '\\', $relative );

			// class_exists autoloads and returns false for interfaces/traits and any
			// file whose class name does not follow PSR-4, excluding them naturally.
			if ( ! class_exists( $class ) || ! is_subclass_of( $class, Ability_Interface::class ) ) {
				continue;
			}

			if ( ( new ReflectionClass( $class ) )->isAbstract() ) {
				continue;
			}

			$abilities[] = $class;
		}

		if ( empty( $abilities ) ) {
			$this->markTestSkipped( sprintf( 'No Ability_Interface implementations found in %s.', $dir ) );
		}

		return $abilities;
	}

	/**
	 * Skip the test when the Abilities API (WordPress 6.9+) is not loaded.
	 */
	private function require_abilities_api(): void {
		if ( ! function_exists( 'wp_get_ability' ) ) {
			$this->markTestSkipped( 'The WordPress Abilities API (WP 6.9+) is not available in this environment.' );
		}
	}
}
