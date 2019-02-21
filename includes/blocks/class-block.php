<?php
/**
 * Abstract block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract block class.
 *
 * @class Block
 */
abstract class Block {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Block attributes.
	 *
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		foreach ( $args as $arg_name => $arg_value ) {
			call_user_func_array( [ $this, 'set_' . $arg_name ], [ $arg_value ] );
		}
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

			return call_user_func_array( [ $this, $method ], array_merge( [ $arg ], $args ) );
		}
	}

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final private function set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->$name = $value;
		}
	}

	/**
	 * Gets property.
	 *
	 * @param string $name Property name.
	 */
	final private function get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->$name;
		}
	}

	/**
	 * Gets block attribute.
	 *
	 * @param mixed $name Attribute name.
	 */
	final public function get_attribute( $name ) {
		return hp_get_array_value( $this->get_attributes(), $name );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
