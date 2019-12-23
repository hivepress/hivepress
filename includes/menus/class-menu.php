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
		$args = hp\merge_arrays(
			[
				'meta' => [
					'name' => hp\get_class_name( static::class ),
				],
			],
			$args
		);

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
					if ( isset( $route['title'] ) ) {
						$args['label'] = $route['title'];
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
