<?php
/**
 * Abstract menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract menu class.
 *
 * @class Menu
 */
abstract class Menu {
	use Traits\Mutator;
	use Traits\Meta;

	/**
	 * Menu items.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * Menu context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Menu attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Menu meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name'    => hp\get_class_name( static::class ),
				'chained' => false,
			],
			$meta
		);

		// Filter meta.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters menu meta.
			 *
			 * @filter /menus/{$name}/meta
			 * @description Filters menu meta.
			 * @param string $name Menu name.
			 * @param array $meta Menu meta.
			 */
			$meta = apply_filters( 'hivepress/v1/menus/' . hp\get_class_name( $class ) . '/meta', $meta );
		}

		// Set meta.
		static::set_meta( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters menu arguments.
			 *
			 * @filter /menus/{$name}
			 * @description Filters menu arguments.
			 * @param string $name Menu name.
			 * @param array $args Menu arguments.
			 * @param object $object Menu object.
			 */
			$args = apply_filters( 'hivepress/v1/menus/' . hp\get_class_name( $class ), $args, $this );
		}

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps menu properties.
	 */
	protected function boot() {

		// Set class.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [
					'hp-menu',
					'hp-menu--' . hp\sanitize_slug( static::get_meta( 'name' ) ),
				],
			]
		);
	}

	/**
	 * Sets menu items.
	 *
	 * @param array $items Menu items.
	 */
	final protected function set_items( $items ) {
		$this->items = [];

		foreach ( hp\sort_array( $items ) as $name => $args ) {
			if ( isset( $args['route'] ) ) {

				// Get route.
				$route = hivepress()->router->get_route( $args['route'] );

				if ( $route ) {

					// Set label.
					if ( ! isset( $args['label'] ) ) {
						$title = hp\get_array_value( $route, 'title' );

						if ( is_callable( $title ) ) {
							$title = call_user_func( $title );
						}

						$args['label'] = $title;
					}

					// Set URL.
					if ( ! isset( $args['url'] ) ) {
						$args['url'] = hivepress()->router->get_url( $args['route'] );
					}

					// Set current flag.
					if ( hivepress()->router->get_current_url() === $args['url'] ) {
						$args['current'] = true;
					}
				}
			}

			$this->items[ $name ] = $args;
		}
	}

	/**
	 * Gets menu items.
	 *
	 * @return array
	 */
	final public function get_items() {
		return $this->items;
	}

	/**
	 * Gets context values.
	 *
	 * @param string $name Context name.
	 * @return mixed
	 */
	final public function get_context( $name = null ) {
		return empty( $name ) ? $this->context : hp\get_array_value( $this->context, $name );
	}

	/**
	 * Renders menu HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( $this->items ) {
			$output = '<nav ' . hp\html_attributes( $this->attributes ) . '><ul>';

			foreach ( $this->items as $name => $args ) {
				$output .= '<li class="hp-menu__item ' . ( hp\get_array_value( $args, 'current' ) ? 'hp-menu__item--current current-menu-item' : '' ) . '">';
				$output .= '<a href="' . esc_url( $args['url'] ) . '">' . esc_html( $args['label'] ) . '</a>';
				$output .= '</li>';
			}

			$output .= '</ul></nav>';
		}

		return $output;
	}
}
