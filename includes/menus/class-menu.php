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
 */
abstract class Menu {
	use Traits\Mutator;
	use Traits\Meta;
	use Traits\Context;

	/**
	 * Menu items.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * HTML attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Add wrapper?
	 *
	 * @var bool
	 */
	protected $wrap = true;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
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
			 * Filters the menu class meta. The class meta stores properties related to the menu type rather than a specific menu instance. The dynamic part of the hook refers to the menu name (e.g. `user_account`). You can check the available menus in the `includes/menus` directory of HivePress.
			 *
			 * @hook hivepress/v1/menus/{menu_name}/meta
			 * @param {array} $meta Class meta values.
			 * @return {array} Class meta values.
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
			 * Filters the menu properties. The dynamic part of the hook refers to the menu name (e.g. `user_account`). You can check the available menus in the `includes/menus` directory of HivePress.
			 *
			 * @hook hivepress/v1/menus/{menu_name}
			 * @param {array} $props Menu properties.
			 * @param {object} $menu Menu object.
			 * @return {array} Menu properties.
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
			 * Filters menu items. At the time of this hook the menu context is already available. The dynamic part of the hook refers to the menu name (e.g. `user_account`). You can check the available menus in the `includes/menus` directory of HivePress.
			 *
			 * @hook hivepress/v1/menus/{menu_name}/items
			 * @param {array} $items Menu items.
			 * @param {object} $menu Menu object.
			 * @return {array} Menu items.
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

						if ( is_callable( $title ) && ! static::get_meta( 'chained' ) ) {
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
	 * Renders menu HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( $this->items ) {
			if ( $this->wrap ) {
				$output .= '<nav ' . hp\html_attributes( $this->attributes ) . '>';
			}

			// Render items.
			$output .= $this->render_items();

			if ( $this->wrap ) {
				$output .= '</nav>';
			}
		}

		return $output;
	}

	/**
	 * Renders menu items.
	 *
	 * @param mixed $current Current item name.
	 * @return string
	 */
	protected function render_items( $current = null ) {
		$output = '';

		// Filter items.
		$items = array_filter(
			$this->items,
			function( $item ) use ( $current ) {
				$parent = hp\get_array_value( $item, '_parent' );

				return ( is_null( $current ) && is_null( $parent ) ) || ( ! is_null( $current ) && $parent === $current );
			}
		);

		if ( $items ) {

			// Get current URL.
			$url = hivepress()->router->get_current_url();

			// Get current route.
			$route = hivepress()->router->get_current_route_name();

			// Render items.
			if ( ! $this->wrap && is_null( $current ) ) {
				$output .= '<ul ' . hp\html_attributes( $this->attributes ) . '>';
			} else {
				$output .= '<ul ' . ( $current ? 'class="sub-menu"' : '' ) . '>';
			}

			foreach ( hp\sort_array( $items ) as $name => $args ) {

				// Get current class.
				$class = 'hp-menu__item--' . hp\sanitize_slug( $name );

				if ( $args['url'] === $url || hp\get_array_value( $args, 'route', false ) === $route ) {
					$class .= ' hp-menu__item--current current-menu-item';
				}

				// Get child items.
				$child_items = $this->render_items( $name );

				if ( $child_items ) {
					$class .= ' menu-item-has-children';
				}

				// Render menu item.
				$output .= '<li class="menu-item hp-menu__item ' . esc_attr( $class ) . '">';
				$output .= '<a href="' . esc_url( $args['url'] ) . '">';

				// Render label.
				$output .= '<span>' . esc_html( $args['label'] ) . '</span>';

				// Render meta.
				if ( isset( $args['meta'] ) ) {
					$output .= '<small>' . esc_html( $args['meta'] ) . '</small>';
				}

				$output .= '</a>';

				// Render child items.
				$output .= $child_items;

				$output .= '</li>';
			}

			$output .= '</ul>';
		}

		return $output;
	}
}
