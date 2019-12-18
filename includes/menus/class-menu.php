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

	/**
	 * Chained flag.
	 *
	 * @var bool
	 */
	protected static $chained = false;

	/**
	 * Menu items.
	 *
	 * @var array
	 */
	protected static $items = [];

	/**
	 * Menu attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Menu arguments.
	 */
	public static function init( $args = [] ) {

		/**
		 * Filters menu arguments.
		 *
		 * @filter /menus/{$name}
		 * @description Filters menu arguments.
		 * @param string $name Menu name.
		 * @param array $args Menu arguments.
		 */
		$args = apply_filters( 'hivepress/v1/menus/' . static::get_name(), $args );

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->bootstrap();
	}

	/**
	 * Bootstraps menu properties.
	 */
	protected function bootstrap() {

		// Set class.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [
					'hp-menu',
					'hp-menu--' . hp\sanitize_slug( static::get_name() ),
				],
			]
		);
	}

	/**
	 * Gets menu name.
	 *
	 * @return string
	 */
	final public static function get_name() {
		return hp\get_class_name( static::class );
	}

	/**
	 * Checks chained flag.
	 *
	 * @return bool
	 */
	final public static function is_chained() {
		return static::$chained;
	}

	/**
	 * Sets menu items.
	 *
	 * @param array $items Menu items.
	 */
	final protected static function set_items( $items ) {
		foreach ( hp\sort_array( $items ) as $name => $item ) {
			if ( isset( $item['route'] ) ) {

				// Get route.
				$route = hivepress()->router->get_route( $item['route'] );

				if ( $route ) {

					// Set label.
					if ( isset( $route['title'] ) ) {
						$item['label'] = $route['title'];
					}

					// Set URL.
					if ( ! isset( $item['url'] ) ) {
						$item['url'] = hivepress()->router->get_url( $item['route'] );
					}

					// Set current flag.
					if ( get_query_var( 'hp_route' ) === $item['route'] ) {
						$item['current'] = true;
					}
				}
			}

			static::$items[ $name ] = $item;
		}
	}

	/**
	 * Gets menu items.
	 *
	 * @return array
	 */
	final public static function get_items() {
		return static::$items;
	}

	/**
	 * Renders menu HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( ! empty( static::$items ) ) {
			$output = '<nav ' . hp\html_attributes( $this->attributes ) . '><ul>';

			foreach ( static::$items as $name => $item ) {
				$output .= '<li class="hp-menu__item ' . ( hp\get_array_value( $item, 'current', false ) ? 'hp-menu__item--current current-menu-item' : '' ) . '">';
				$output .= '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
				$output .= '</li>';
			}

			$output .= '</ul></nav>';
		}

		return $output;
	}
}
