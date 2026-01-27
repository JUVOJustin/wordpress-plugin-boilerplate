<?php
/**
 * Ability Interface
 *
 * Defines the contract for WordPress Abilities API implementations.
 * Aligns with the native WP_Ability class structure in WordPress 6.9+.
 *
 * @package Demo_Plugin
 * @see WP_Ability
 */

namespace Demo_Plugin\Abilities;

use WP_Error;

/**
 * Interface Ability_Interface
 *
 * Contract for ability classes that integrate with WordPress Abilities API.
 */
interface Ability_Interface {

	/**
	 * Get the unique ability name
	 *
	 * @return string Ability name in format namespace/ability-name.
	 */
	public static function get_name(): string;

	/**
	 * Get the ability label for display
	 *
	 * @return string Human-readable ability name.
	 */
	public static function get_label(): string;

	/**
	 * Get the ability description
	 *
	 * @return string Description of what the ability does.
	 */
	public static function get_description(): string;

	/**
	 * Get the ability category class
	 *
	 * Returns a class implementing Ability_Category_Interface.
	 * The Loader will automatically register the category.
	 *
	 * @return class-string<Ability_Category_Interface> Category class name.
	 */
	public static function get_category(): string;

	/**
	 * Get the input schema for validation
	 *
	 * @return array<string, mixed> JSON Schema for input validation.
	 */
	public static function get_input_schema(): array;

	/**
	 * Get the output schema for documentation
	 *
	 * @return array<string, mixed> JSON Schema for output structure.
	 */
	public static function get_output_schema(): array;

	/**
	 * Get ability annotations
	 *
	 * Defines behavioral characteristics of the ability.
	 *
	 * @return array{readonly?: bool|null, destructive?: bool|null, idempotent?: bool|null} Ability annotations.
	 */
	public static function get_annotations(): array;

	/**
	 * Determine if ability should be exposed in REST API
	 *
	 * @return bool True to expose in REST API, false otherwise.
	 */
	public static function show_rest(): bool;

	/**
	 * Check if the current user can execute this ability
	 *
	 * @param mixed $input Optional. The input data for the ability.
	 * @return bool|WP_Error True if permitted, false if denied, WP_Error on failure.
	 */
	public static function check_permissions( mixed $input = null ): bool|WP_Error;

	/**
	 * Execute the ability
	 *
	 * @param mixed $input Optional. The input data for the ability.
	 * @return mixed|WP_Error Result data or error.
	 */
	public static function execute( mixed $input = null ): mixed;
}
