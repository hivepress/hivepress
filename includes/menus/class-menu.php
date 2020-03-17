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

		// Get items.
		$items = $this->items;

		// Filter items.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters menu items.
			 *
			 * @filter /menus/{$name}/items
			 * @description Filters menu items.
			 * @param string $name Menu name.
			 * @param array $items Menu items.
			 * @param object $object Menu object.
			 */
			$items = apply_filters( 'hivepress/v1/menus/' . hp\get_class_name( $class ) . '/items', $items, $this );
		}

		// Set items.
		$this->set_items( $items );

		// Set attributes.
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
			if ( isset( $args['route'] ) && ( ! isset( $args['label'] ) || ! isset( $args['url'] ) ) ) {

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
						if ( static::get_meta( 'chained' ) ) {
							$args['url'] = hivepress()->router->get_url( $args['route'], hivepress()->request->get_params(), true );
						} else {
							$args['url'] = hivepress()->router->get_url( $args['route'] );
						}
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
			$output .= '<nav ' . hp\html_attributes( $this->attributes ) . '>';
			$output .= '<ul>';

			// Get current route.
			$route = hp\get_array_value( hivepress()->router->get_current_route(), 'name', false );

			foreach ( $this->items as $name => $args ) {

				// Get current class.
				$class = '';

				if ( hivepress()->router->get_current_url() === $args['url'] || hp\get_array_value( $args, 'route' ) === $route ) {
					$class = 'hp-menu__item--current current-menu-item';
				}

				// Render menu item.
				$output .= '<li class="hp-menu__item ' . esc_attr( $class ) . '">';
				$output .= '<a href="' . esc_url( $args['url'] ) . '">' . esc_html( $args['label'] ) . '</a>';
				$output .= '</li>';
			}

			$output .= '</ul>';
			$output .= '</nav>';
		}

		return $output;
	}
}
