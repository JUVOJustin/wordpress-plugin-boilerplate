<?php
/**
 * Register all actions, filters, shortcodes, cli commands and abilities for the plugin
 *
 * @package    Demo_Plugin
 */

namespace Demo_Plugin;

use Demo_Plugin\Abilities\Ability_Category_Interface;
use Demo_Plugin\Abilities\Ability_Interface;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 */
class Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array<int, array{'hook':string, 'component':object, 'callback':string, 'priority':int, 'accepted_args':int}> $actions The actions registered with WordPress to fire when the plugin loads.
	 */
	protected array $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array<int, array{'hook':string, 'component':object, 'callback':string, 'priority':int, 'accepted_args':int}> $filters The filters registered with WordPress to fire when the plugin loads.
	 */
	protected array $filters;

	/**
	 * The array of shortcodes registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array<int, array{'hook':string, 'component':object, 'callback':string, 'priority':int, 'accepted_args':int}> $shortcodes The shortcodes registered with WordPress to load when the plugin loads.
	 */
	protected array $shortcodes;

	/**
	 * The array of WP-CLI commands registered with WordPress.
	 *
	 * @var array<string, array{'instance':string, 'args':mixed[]}> $cli The array of WP-CLI commands registered with WordPress.
	 */
	protected array $cli;

	/**
	 * The array of abilities registered with WordPress.
	 *
	 * @var array<string, class-string<Ability_Interface>> $abilities Ability class names keyed by ability name.
	 */
	protected array $abilities;

	/**
	 * The array of ability categories to register with WordPress.
	 *
	 * @var array<string, class-string<Ability_Category_Interface>> $ability_categories Category class names keyed by slug.
	 */
	protected array $ability_categories;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->actions            = array();
		$this->filters            = array();
		$this->shortcodes         = array();
		$this->abilities          = array();
		$this->ability_categories = array();
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @param string $hook The name of the WordPress action that is being registered.
	 * @param object $component A reference to the instance of the object on which the action is defined.
	 * @param string $callback The name of the function definition on the $component.
	 * @param int    $priority Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @since    1.0.0
	 */
	public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @param string $hook The name of the WordPress action that is being registered.
	 * @param object $component A reference to the instance of the object on which the action is defined.
	 * @param string $callback The name of the function definition on the $component.
	 *
	 * @since    1.0.0
	 */
	public function add_shortcode( string $hook, object $component, string $callback ): void {
		$this->shortcodes = $this->add( $this->shortcodes, $hook, $component, $callback );
	}

	/**
	 * Add a new WP-CLI command to the collection to be registered with WordPress.
	 *
	 * @param string              $name The name of the cli command you want to register.
	 * @param object              $instance A reference to the instance of the object on which the callback is defined.
	 * @param array<string,mixed> $args An associative array with additional registration parameters.
	 *
	 * @return void
	 */
	public function add_cli( string $name, object $instance, array $args = array() ): void {
		$this->cli[ $name ] = array(
			'instance' => $instance,
			'args'     => $args,
		);
	}

	/**
	 * Add a new ability to the collection to be registered with WordPress Abilities API.
	 *
	 * Automatically collects category classes for registration.
	 *
	 * @param string $ability_class Fully qualified class name implementing Ability_Interface.
	 *
	 * @return void
	 */
	public function add_ability( string $ability_class ): void {
		if ( ! is_subclass_of( $ability_class, Ability_Interface::class ) ) {
			return;
		}

		$this->abilities[ $ability_class::get_name() ] = $ability_class;

		$category_class = $ability_class::get_category();
		$this->ability_categories[ $category_class::get_slug() ] = $category_class;
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @param array<int, array{'hook':string, 'component':object, 'callback':string, 'priority':int, 'accepted_args':int}> $hooks The collection of hooks that is being registered (that is, actions or filters).
	 * @param string                                                                                                       $hook The name of the WordPress filter that is being registered.
	 * @param object                                                                                                       $component A reference to the instance of the object on which the filter is defined.
	 * @param string                                                                                                       $callback The name of the function definition on the $component.
	 * @param int                                                                                                          $priority The priority at which the function should be fired.
	 * @param int                                                                                                          $accepted_args The number of arguments that should be passed to the $callback.
	 *
	 * @return   array<int, array{'hook':string, 'component':object, 'callback':string, 'priority':int, 'accepted_args':int}> The collection of actions and filters registered with WordPress.
	 * @since    1.0.0
	 * @access   private
	 */
	private function add( array $hooks, string $hook, object $component, string $callback, int $priority = - 1, int $accepted_args = - 1 ): array {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @param string $hook The name of the WordPress filter that is being registered.
	 * @param object $component A reference to the instance of the object on which the filter is defined.
	 * @param string $callback The name of the function definition on the $component.
	 * @param int    $priority Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @since    1.0.0
	 */
	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run(): void {

		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array(
					$hook['component'],
					$hook['callback'],
				),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array(
					$hook['component'],
					$hook['callback'],
				),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->shortcodes as $hook ) {
			add_shortcode(
				$hook['hook'],
				array(
					$hook['component'],
					$hook['callback'],
				)
			);
		}

		// Check if WP_CLI is available
		if ( ! empty( $this->cli ) && class_exists( 'WP_CLI' ) ) {
			foreach ( $this->cli as $name => $data ) {
				\WP_CLI::add_command( $name, $data['instance'], $data['args'] );
			}
		}

		// Register WordPress Abilities
		if ( ! empty( $this->abilities ) && function_exists( 'wp_register_ability' ) ) {
			add_action( 'wp_abilities_api_categories_init', array( $this, 'do_register_ability_categories' ) );
			add_action( 'wp_abilities_api_init', array( $this, 'do_register_abilities' ) );
		}
	}

	/**
	 * Callback to register all ability categories with WordPress.
	 *
	 * @return void
	 */
	public function do_register_ability_categories(): void {
		foreach ( $this->ability_categories as $category_class ) {
			wp_register_ability_category(
				$category_class::get_slug(),
				array(
					'label'       => $category_class::get_label(),
					'description' => $category_class::get_description(),
					'meta'        => $category_class::get_meta(),
				)
			);
		}
	}

	/**
	 * Callback to register all abilities with WordPress.
	 *
	 * @return void
	 */
	public function do_register_abilities(): void {
		foreach ( $this->abilities as $ability_class ) {
			$category_class = $ability_class::get_category();

			wp_register_ability(
				$ability_class::get_name(),
				array(
					'label'               => $ability_class::get_label(),
					'description'         => $ability_class::get_description(),
					'category'            => $category_class::get_slug(),
					'input_schema'        => $ability_class::get_input_schema(),
					'output_schema'       => $ability_class::get_output_schema(),
					'execute_callback'    => array( $ability_class, 'execute' ),
					'permission_callback' => array( $ability_class, 'check_permissions' ),
					'meta'                => array(
						'annotations'  => $ability_class::get_annotations(),
						'show_in_rest' => $ability_class::show_rest(),
					),
				)
			);
		}
	}
}
