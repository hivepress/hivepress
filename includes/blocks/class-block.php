<?php
/**
 * Abstract block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract block class.
 *
 * @class Block
 */
abstract class Block {
	use Traits\Mutator;

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Block settings.
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Block values.
	 *
	 * @var array
	 */
	protected $values = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
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
	 * @param array $args Block arguments.
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
	 * Gets block title.
	 *
	 * @return string
	 */
	final public static function get_title() {
		return static::$title;
	}

	/**
	 * Sets block settings.
	 *
	 * @param array $settings Block settings.
	 */
	final protected static function set_settings( $settings ) {
		static::$settings = [];

		foreach ( $settings as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			if ( class_exists( $field_class ) ) {

				// Create field.
				static::$settings[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
			}
		}
	}

	/**
	 * Gets block settings.
	 *
	 * @return array
	 */
	final public static function get_settings() {
		return static::$settings;
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 */
	final public function __call( $name, $args ) {
		$prefixes = array_filter(
			[
				'set',
				'get',
			],
			function( $prefix ) use ( $name ) {
				return strpos( $name, $prefix . '_' ) === 0;
			}
		);

		if ( ! empty( $prefixes ) ) {
			$method = reset( $prefixes );
			$arg    = substr( $name, strlen( $method ) + 1 );

			return call_user_func_array( [ $this, $method . '_property' ], array_merge( [ $arg ], $args ) );
		}
	}

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final protected function set_property( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->$name = $value;
		} else {
			$this->values[ $name ] = $value;
		}
	}

	/**
	 * Gets property.
	 *
	 * @param string $name Property name.
	 */
	final protected function get_property( $name ) {
		return hp\get_array_value( $this->values, $name );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {
		// todo.
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
