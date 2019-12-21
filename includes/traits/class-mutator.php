<?php
/**
 * Mutator.
 *
 * @package HivePress\Traits
 */

namespace HivePress\Traits;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Mutator trait.
 *
 * @trait Mutator
 */
trait Mutator {

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 * @param string $prefix Method prefix.
	 */
	final protected function set_property( $name, $value, $prefix = '' ) {
		$method = $prefix . 'set_' . $name;

		if ( method_exists( $this, $method ) && ! ( new \ReflectionMethod( $this, $method ) )->isStatic() ) {
			call_user_func_array( [ $this, $method ], [ $value ] );
		} elseif ( property_exists( $this, $name ) && ! ( new \ReflectionProperty( $this, $name ) )->isStatic() ) {
			$this->$name = $value;
		}
	}

	/**
	 * Sets static property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 * @param string $prefix Method prefix.
	 */
	final protected static function set_static_property( $name, $value, $prefix = '' ) {
		$method = $prefix . 'set_' . $name;

		if ( method_exists( static::class, $method ) && ( new \ReflectionMethod( static::class, $method ) )->isStatic() ) {
			call_user_func_array( [ static::class, $method ], [ $value ] );
		} elseif ( property_exists( static::class, $name ) && ( new \ReflectionProperty( static::class, $name ) )->isStatic() ) {
			static::$$name = $value;
		}
	}
}
