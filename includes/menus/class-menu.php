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
		$attributes = [];

		// Set class.
		$attributes['class'] = [ 'hp-menu' ];

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );
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

			foreach ( static::$items as $item ) {
				$output .= '<li><a href="#">Todo</a></li>';
			}

			$output .= '</ul></nav>';
		}

		return $output;
	}
}
