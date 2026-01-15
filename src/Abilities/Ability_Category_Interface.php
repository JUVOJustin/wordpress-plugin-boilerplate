<?php
/**
 * Ability Category Interface
 *
 * Defines the contract for WordPress Ability Category implementations.
 * Categories help organize abilities and make them easier to discover and filter.
 *
 * @package Demo_Plugin
 * @see wp_register_ability_category
 */

namespace Demo_Plugin\Abilities;

/**
 * Interface Ability_Category_Interface
 *
 * Contract for ability category classes that integrate with WordPress Abilities API.
 */
interface Ability_Category_Interface {

	/**
	 * Get the unique category slug
	 *
	 * Must contain only lowercase alphanumeric characters and hyphens.
	 *
	 * @return string Category slug (e.g., 'data-retrieval', 'site-management').
	 */
	public static function get_slug(): string;

	/**
	 * Get the category label for display
	 *
	 * @return string Human-readable category name.
	 */
	public static function get_label(): string;

	/**
	 * Get the category description
	 *
	 * @return string Description of the category's purpose.
	 */
	public static function get_description(): string;

	/**
	 * Get optional category metadata
	 *
	 * @return array<string, mixed> Additional metadata for the category.
	 */
	public static function get_meta(): array;
}
